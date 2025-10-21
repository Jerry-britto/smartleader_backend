<?php 
include("header.php"); 

// Get all languages
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);

// Handle DELETE
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM add_free_videos WHERE id='$delete_id'");
    echo "<script>alert('✅ Deleted!');window.location.href='show_free_video_audio.php';</script>";
}

// Handle ADD VIDEO
if (isset($_POST['add_video'])) {
    $video_id = !empty($_POST['video_id_manual']) ? intval($_POST['video_id_manual']) : intval($_POST['video_id_dropdown']);
    
    $video_query = mysqli_query($conn, "SELECT * FROM add_video WHERE id = $video_id AND (video_link IS NOT NULL AND video_link != '')");
    $video_data = mysqli_fetch_assoc($video_query);
    
    if ($video_data) {
        $check = mysqli_query($conn, "SELECT id FROM add_free_videos WHERE original_video_id = $video_id AND (video_link IS NOT NULL AND video_link != '')");
        
        if (mysqli_num_rows($check) == 0) {
            $name = mysqli_real_escape_string($conn, $video_data['video_name']);
            $image = mysqli_real_escape_string($conn, $video_data['image']);
            $video_link = mysqli_real_escape_string($conn, $video_data['video_link']);
            $time = mysqli_real_escape_string($conn, $video_data['time'] ?? '');
            
            $insert_sql = "INSERT INTO add_free_videos 
                          (original_video_id, name, image, video_link, time, language_key, tag_id, isFree) 
                          VALUES 
                          ('$video_id', '$name', '$image', '$video_link', '$time', '{$video_data['language_key']}', '{$video_data['tag_id']}', 1)";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo "<script>alert('✅ Video added!');window.location.href='show_free_video_audio.php';</script>";
            } else {
                echo "<script>alert('❌ Error: ".mysqli_error($conn)."');</script>";
            }
        } else {
            echo "<script>alert('⚠️ Video already exists!');</script>";
        }
    } else {
        echo "<script>alert('❌ Video ID $video_id not found!');</script>";
    }
}

// Handle ADD AUDIO FILE from folder
if (isset($_POST['add_audio_file'])) {
    $audio_file = mysqli_real_escape_string($conn, $_POST['audio_file']);
    $audio_name = mysqli_real_escape_string($conn, $_POST['audio_name']);
    $audio_image = mysqli_real_escape_string($conn, $_POST['audio_image']);
    $language_id = intval($_POST['language_id']);
    
    if (!empty($audio_file) && !empty($audio_name)) {
        $check = mysqli_query($conn, "SELECT id FROM add_free_videos WHERE audio = '$audio_file'");
        
        if (mysqli_num_rows($check) == 0) {
            $insert_sql = "INSERT INTO add_free_videos 
                          (name, image, audio, language_key, isFree) 
                          VALUES 
                          ('$audio_name', '$audio_image', '$audio_file', '$language_id', 1)";
            
            if (mysqli_query($conn, $insert_sql)) {
                echo "<script>alert('✅ Audio file added!');window.location.href='show_free_video_audio.php';</script>";
            } else {
                echo "<script>alert('❌ Error: ".mysqli_error($conn)."');</script>";
            }
        } else {
            echo "<script>alert('⚠️ Audio file already exists!');</script>";
        }
    }
}

// Handle TOGGLE
if (isset($_POST['toggle_free'])) {
    $toggle_id = intval($_POST['toggle_id']);
    $current = intval($_POST['current_status']);
    $new = ($current == 1) ? 0 : 1;
    
    mysqli_query($conn, "UPDATE add_free_videos SET isFree = $new WHERE id = $toggle_id");
    echo "<script>alert('Updated!');window.location.href='show_free_video_audio.php';</script>";
}

// Statistics
$total_videos = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM add_free_videos WHERE isFree = 1 AND (video_link IS NOT NULL AND video_link != '')"));
$total_audio = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM add_free_videos WHERE isFree = 1 AND (audio IS NOT NULL AND audio != '')"));

$available_videos = mysqli_num_rows(mysqli_query($conn, "
    SELECT av.id FROM add_video av
    WHERE (av.video_link IS NOT NULL AND av.video_link != '')
    AND av.id NOT IN (
        SELECT original_video_id FROM add_free_videos 
        WHERE original_video_id IS NOT NULL 
        AND (video_link IS NOT NULL AND video_link != '')
    )
"));

// Function to find matching image for audio file
function findMatchingImage($audioFilename, $imagesDir) {
    $baseName = pathinfo($audioFilename, PATHINFO_FILENAME);
    
    // Try exact match first
    foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
        if (file_exists($imagesDir . $baseName . '.' . $ext)) {
            return $baseName . '.' . $ext;
        }
    }
    
    // Try fuzzy match (remove special chars and match)
    $cleanBase = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($baseName));
    
    $files = scandir($imagesDir);
    foreach ($files as $file) {
        $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $fileBase = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(pathinfo($file, PATHINFO_FILENAME)));
            
            // Check if image name contains audio name or vice versa
            if (strpos($fileBase, $cleanBase) !== false || strpos($cleanBase, $fileBase) !== false) {
                return $file;
            }
        }
    }
    
    return '';
}

// Scan for audio files + images
$audio_files = [];
$all_images = [];
$images_dir = '../images/';

if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    
    // Collect all images
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $all_images[] = $file;
        }
    }
    
    // Collect audio files
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'ogg'])) {
            $escaped_file = mysqli_real_escape_string($conn, $file);
            $check = mysqli_query($conn, "SELECT id FROM add_free_videos WHERE audio = '$escaped_file'");
            if (mysqli_num_rows($check) == 0) {
                $audio_files[] = [
                    'filename' => $file,
                    'suggested_image' => findMatchingImage($file, $images_dir)
                ];
            }
        }
    }
}
?>

<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
.nav-tabs .nav-link {
    color: #666;
    font-weight: 600;
    border-radius: 8px 8px 0 0;
}
.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    border-color: #667eea;
}
.add-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 25px;
    margin: 20px 0;
    border-radius: 10px;
    color: white;
}
.add-section-audio {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    padding: 25px;
    margin: 20px 0;
    border-radius: 10px;
}
.manual-id-input {
    border: 2px solid white;
    background: rgba(255,255,255,0.2);
    color: white;
    font-weight: 600;
}
.manual-id-input::placeholder {
    color: rgba(255,255,255,0.7);
}
.or-divider {
    text-align: center;
    margin: 15px 0;
    font-weight: 600;
    color: white;
}
.stats-box {
    background: white;
    padding: 20px;
    margin: 20px 0;
    border-radius: 10px;
}
.stat-item {
    display: inline-block;
    margin-right: 30px;
}
.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #667eea;
}
.badge-free { background: #28a745; color: white; padding: 6px 12px; border-radius: 12px; }
.badge-hidden { background: #dc3545; color: white; padding: 6px 12px; border-radius: 12px; }
.select2-container .select2-selection--single {
    height: 38px !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Free Media Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Free Media</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-box">
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_videos; ?></div>
            <div>Free Videos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $total_audio; ?></div>
            <div>Free Audio</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $available_videos; ?></div>
            <div>Available Videos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo count($audio_files); ?></div>
            <div>Audio Files in Folder</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="mediaTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="video-tab" data-toggle="tab" href="#video" role="tab">
                        🎬 Free Videos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="audio-tab" data-toggle="tab" href="#audio" role="tab">
                        🎵 Free Audio
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="mediaTabContent">
                
                <!-- VIDEO TAB -->
                <div class="tab-pane fade show active" id="video" role="tabpanel">
                    <div class="add-section">
                        <h4>➕ Add Free Video</h4>
                        <p>Enter Video ID directly OR search and select from dropdown</p>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-3">
                                    <label style="color: white;">Enter Video ID *</label>
                                    <input type="number" name="video_id_manual" class="form-control manual-id-input" 
                                           placeholder="e.g. 123" min="1" id="videoIdManual">
                                    <small style="color: rgba(255,255,255,0.8);">Quick add by ID number</small>
                                </div>
                                <div class="col-md-1 d-flex align-items-center justify-content-center">
                                    <div class="or-divider">OR</div>
                                </div>
                                <div class="col-md-6">
                                    <label style="color: white;">Search & Select Video</label>
                                    <select name="video_id_dropdown" class="form-control select2-video" id="videoDropdown">
                                        <option value="">Search videos...</option>
                                        <?php
                                        $videos = mysqli_query($conn, "
                                            SELECT av.id, av.video_name, l.value as language
                                            FROM add_video av
                                            LEFT JOIN list l ON av.language_key = l.id
                                            WHERE (av.video_link IS NOT NULL AND av.video_link != '')
                                            AND av.id NOT IN (
                                                SELECT original_video_id FROM add_free_videos 
                                                WHERE original_video_id IS NOT NULL 
                                                AND (video_link IS NOT NULL AND video_link != '')
                                            )
                                            ORDER BY av.id DESC
                                        ");
                                        while ($v = mysqli_fetch_assoc($videos)) {
                                            echo '<option value="'.$v['id'].'">ID: '.$v['id'].' - '.htmlspecialchars($v['video_name']).' ('.($v['language'] ?? 'N/A').')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="add_video" class="btn btn-light btn-block">
                                        <i class="fas fa-plus"></i> Add Video
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Video Table -->
                    <table class="table table-bordered" id="videoTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $videos_result = mysqli_query($conn, "SELECT * FROM add_free_videos WHERE (video_link IS NOT NULL AND video_link != '') ORDER BY id DESC");
                            if (mysqli_num_rows($videos_result) == 0) {
                                echo '<tr><td colspan="5" style="text-align:center; padding:40px;">No free videos yet</td></tr>';
                            }
                            while ($row = mysqli_fetch_assoc($videos_result)) {
                                $isFree = $row['isFree'] ?? 1;
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="../images/<?php echo $row['image']; ?>" style="width: 60px; height: 45px; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="<?php echo $isFree ? 'badge-free' : 'badge-hidden'; ?>">
                                            <?php echo $isFree ? 'FREE' : 'HIDDEN'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="toggle_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $isFree; ?>">
                                            <button type="submit" name="toggle_free" class="btn btn-sm btn-warning">Toggle</button>
                                        </form>
                                        <a onclick="return confirm('Remove?')" href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- AUDIO TAB -->
                <div class="tab-pane fade" id="audio" role="tabpanel">
                    
                    <?php if (count($audio_files) > 0): ?>
                    <div class="add-section-audio">
                        <h4 style="color: #333;">🎵 Add Audio File from Folder</h4>
                        <p style="color: #555;">Auto-detects matching images + searchable dropdowns</p>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-3">
                                    <label style="color: #333;">Search Audio File *</label>
                                    <select name="audio_file" required class="form-control select2-audio" id="audioFileSelect">
                                        <option value="">Search audio...</option>
                                        <?php foreach ($audio_files as $file): ?>
                                            <option value="<?php echo $file['filename']; ?>" 
                                                    data-image="<?php echo $file['suggested_image']; ?>">
                                                <?php echo $file['filename']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label style="color: #333;">Display Name *</label>
                                    <input type="text" name="audio_name" required class="form-control" id="audioNameInput">
                                </div>
                                <div class="col-md-3">
                                    <label style="color: #333;">Search Image</label>
                                    <select name="audio_image" class="form-control select2-image" id="audioImageSelect">
                                        <option value="">Search images...</option>
                                        <?php foreach ($all_images as $img): ?>
                                            <option value="<?php echo $img; ?>"><?php echo $img; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label style="color: #333;">Language</label>
                                    <select name="language_id" class="form-control">
                                        <option value="0">None</option>
                                        <?php foreach ($languages as $lang): ?>
                                            <option value="<?php echo $lang['id']; ?>"><?php echo $lang['value']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" name="add_audio_file" class="btn btn-dark btn-block">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Audio Table -->
                    <table class="table table-bordered" id="audioTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Image</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $audio_result = mysqli_query($conn, "SELECT * FROM add_free_videos WHERE (audio IS NOT NULL AND audio != '') ORDER BY id DESC");
                            if (mysqli_num_rows($audio_result) == 0) {
                                echo '<tr><td colspan="6" style="text-align:center; padding:40px;">No free audio yet</td></tr>';
                            }
                            while ($row = mysqli_fetch_assoc($audio_result)) {
                                $isFree = $row['isFree'] ?? 1;
                            ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="../images/<?php echo $row['image']; ?>" style="width: 60px; height: 45px; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['audio']); ?></td>
                                    <td>
                                        <span class="<?php echo $isFree ? 'badge-free' : 'badge-hidden'; ?>">
                                            <?php echo $isFree ? 'FREE' : 'HIDDEN'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="toggle_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $isFree; ?>">
                                            <button type="submit" name="toggle_free" class="btn btn-sm btn-warning">Toggle</button>
                                        </form>
                                        <a onclick="return confirm('Remove?')" href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for searchable dropdowns
    $('.select2-video').select2({
        placeholder: 'Search videos...',
        allowClear: true
    });
    
    $('.select2-audio').select2({
        placeholder: 'Search audio files...',
        allowClear: true
    });
    
    $('.select2-image').select2({
        placeholder: 'Search images...',
        allowClear: true
    });
    
    // Clear manual input when dropdown is selected
    $('#videoDropdown').on('change', function() {
        if ($(this).val()) {
            $('#videoIdManual').val('');
        }
    });
    $('#videoIdManual').on('input', function() {
        if ($(this).val()) {
            $('#videoDropdown').val(null).trigger('change');
        }
    });
    
    // Auto-fill audio name and image
    $('#audioFileSelect').on('change', function() {
        var filename = $(this).val();
        var suggestedImage = $(this).find(':selected').data('image');
        
        if (filename) {
            // Auto-fill name
            var name = filename.replace(/\.[^/.]+$/, "").replace(/[_-]/g, " ");
            $('#audioNameInput').val(name);
            
            // Auto-select image if found
            if (suggestedImage) {
                $('#audioImageSelect').val(suggestedImage).trigger('change');
            }
        }
    });
    
    // Initialize DataTables
    if ($('#videoTable').length) {
        $('#videoTable').DataTable({"pageLength": 25, "order": [[0, "desc"]]});
    }
    if ($('#audioTable').length) {
        $('#audioTable').DataTable({"pageLength": 25, "order": [[0, "desc"]]});
    }
});
</script>

<?php include("footer.php"); ?>
