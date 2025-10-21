<?php
include('../common/config.php');

// Handle Add Free Video
if (isset($_POST['add_free_video'])) {
    $video_id = $_POST['video_id'] ?? null;
    
    if ($video_id) {
        // Get video details from add_video
        $sql = "SELECT video_name, image, video_link, time, language_key FROM add_video WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $video_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $video = mysqli_fetch_assoc($result);
        
        if ($video) {
            // Check if already exists
            $check_sql = "SELECT id FROM add_free_video WHERE video_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, 'i', $video_id);
            mysqli_stmt_execute($check_stmt);
            $exists = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($exists) > 0) {
                $message = "⚠️ Video already in Free Videos list!";
                $message_type = "warning";
            } else {
                // Insert into add_free_video
                $insert_sql = "INSERT INTO add_free_video (video_id, video_name, image, video_link, time, language_key, is_active) 
                               VALUES (?, ?, ?, ?, ?, ?, 1)";
                $insert_stmt = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_stmt, 'issssi', 
                    $video_id, 
                    $video['video_name'], 
                    $video['image'], 
                    $video['video_link'], 
                    $video['time'], 
                    $video['language_key']
                );
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    $message = "✅ Video added to Free Media successfully!";
                    $message_type = "success";
                } else {
                    $message = "❌ Error adding video: " . mysqli_error($conn);
                    $message_type = "error";
                }
            }
        }
    }
}

// Handle Remove Free Video
if (isset($_POST['remove_free_video'])) {
    $free_video_id = $_POST['free_video_id'] ?? null;
    
    if ($free_video_id) {
        $delete_sql = "DELETE FROM add_free_video WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, 'i', $free_video_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $message = "✅ Video removed from Free Media!";
            $message_type = "success";
        } else {
            $message = "❌ Error removing video!";
            $message_type = "error";
        }
    }
}

// Handle Toggle Active Status
if (isset($_POST['toggle_active'])) {
    $free_video_id = $_POST['free_video_id'] ?? null;
    $current_status = $_POST['current_status'] ?? 0;
    
    if ($free_video_id) {
        $new_status = ($current_status == 1) ? 0 : 1;
        $update_sql = "UPDATE add_free_video SET is_active = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, 'ii', $new_status, $free_video_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $message = $new_status ? "✅ Video activated!" : "⏸️ Video deactivated!";
            $message_type = "success";
        }
    }
}

// Get all free videos
$free_videos_sql = "SELECT fv.*, av.video_name as original_name 
                    FROM add_free_video fv 
                    LEFT JOIN add_video av ON fv.video_id = av.id 
                    ORDER BY fv.sort_order ASC, fv.id DESC";
$free_videos = mysqli_query($conn, $free_videos_sql);

// Get available videos (not in free list)
$available_sql = "SELECT id, video_name, video_link 
                  FROM add_video 
                  WHERE id NOT IN (SELECT video_id FROM add_free_video WHERE video_id IS NOT NULL)
                  AND video_link IS NOT NULL AND video_link != ''
                  ORDER BY id DESC 
                  LIMIT 100";
$available_videos = mysqli_query($conn, $available_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Free Videos</title>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 10px; }
        .stats { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin: 20px 0;
            display: flex;
            justify-content: space-around;
        }
        .stat-item { text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { font-size: 14px; opacity: 0.9; }
        
        .message {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: 500;
        }
        .message.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .message.warning { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        
        .section {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section h2 { color: #333; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        tr:hover { background: #f8f9fa; }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.active { background: #d4edda; color: #155724; }
        .badge.inactive { background: #f8d7da; color: #721c24; }
        
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            margin: 2px;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        
        .btn-add { background: #28a745; color: white; }
        .btn-remove { background: #dc3545; color: white; }
        .btn-toggle { background: #ffc107; color: #333; }
        
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        select:focus { outline: none; border-color: #667eea; }
        
        .video-link {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #667eea;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Free Videos Management System</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?= mysqli_num_rows($free_videos) ?></div>
                <div class="stat-label">Total Free Videos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= mysqli_num_rows($available_videos) ?></div>
                <div class="stat-label">Available to Add</div>
            </div>
        </div>
        
        <!-- Add New Free Video -->
        <div class="section">
            <h2>➕ Add New Free Video</h2>
            <form method="POST">
                <select name="video_id" required>
                    <option value="">Select a video to make free...</option>
                    <?php while ($av = mysqli_fetch_assoc($available_videos)): ?>
                        <option value="<?= $av['id'] ?>">
                            ID: <?= $av['id'] ?> - <?= htmlspecialchars($av['video_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <br><br>
                <button type="submit" name="add_free_video" class="btn-add">
                    ➕ Add to Free Videos
                </button>
            </form>
        </div>
        
        <!-- Current Free Videos -->
        <div class="section">
            <h2>📋 Current Free Videos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Video Name</th>
                        <th>YouTube Link</th>
                        <th>Status</th>
                        <th>Sort Order</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($free_videos) == 0): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                            No free videos yet. Add some above!
                        </td></tr>
                    <?php endif; ?>
                    
                    <?php while ($fv = mysqli_fetch_assoc($free_videos)): ?>
                    <tr>
                        <td><strong><?= $fv['id'] ?></strong></td>
                        <td><?= htmlspecialchars($fv['video_name'] ?: $fv['original_name']) ?></td>
                        <td>
                            <div class="video-link" title="<?= $fv['video_link'] ?>">
                                <?= $fv['video_link'] ? '✅ ' . $fv['video_link'] : '❌ No link' ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $fv['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $fv['is_active'] ? '✅ Active' : '⏸️ Inactive' ?>
                            </span>
                        </td>
                        <td><?= $fv['sort_order'] ?></td>
                        <td style="font-size: 12px;"><?= date('M d, Y', strtotime($fv['created_at'])) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="free_video_id" value="<?= $fv['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $fv['is_active'] ?>">
                                <button type="submit" name="toggle_active" class="btn-toggle">
                                    <?= $fv['is_active'] ? '⏸️ Deactivate' : '✅ Activate' ?>
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Remove this video from free list?');">
                                <input type="hidden" name="free_video_id" value="<?= $fv['id'] ?>">
                                <button type="submit" name="remove_free_video" class="btn-remove">
                                    🗑️ Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
