<?php 
include("header.php"); 

// Handle bulk import
if (isset($_POST['import_audio'])) {
    $selected = $_POST['audio_files'] ?? [];
    $default_language = intval($_POST['default_language']);
    $default_tag = intval($_POST['default_tag']);
    $imported = 0;
    
    foreach ($selected as $filename) {
        $filename = mysqli_real_escape_string($conn, $filename);
        
        // Check if already exists
        $check = mysqli_query($conn, "SELECT id FROM add_video WHERE audio = '$filename'");
        if (mysqli_num_rows($check) == 0) {
            // Generate name from filename
            $name = str_replace(['_', '-', '.mp3', '.wav', '.m4a'], [' ', ' ', '', '', ''], $filename);
            $name = ucwords(trim($name));
            $name = mysqli_real_escape_string($conn, $name);
            
            // Insert
            $sql = "INSERT INTO add_video (video_name, audio, language_key, tag_id, sort_order) 
                    VALUES ('$name', '$filename', '$default_language', '$default_tag', 0)";
            
            if (mysqli_query($conn, $sql)) {
                $imported++;
            }
        }
    }
    
    echo "<script>alert('✅ Imported $imported audio files!');window.location.href='import_audio.php';</script>";
}

// Scan for audio files
$audio_files = [];
$images_dir = '../images/';

if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'flac'])) {
            // Check if already in database
            $escaped = mysqli_real_escape_string($conn, $file);
            $check = mysqli_query($conn, "SELECT id FROM add_video WHERE audio = '$escaped'");
            
            $audio_files[] = [
                'filename' => $file,
                'size' => filesize($images_dir . $file),
                'exists' => mysqli_num_rows($check) > 0
            ];
        }
    }
}

// Sort by filename
usort($audio_files, function($a, $b) {
    return strcmp($a['filename'], $b['filename']);
});

// Get languages and tags
$languages = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM list"), MYSQLI_ASSOC);
$tags = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM tags ORDER BY tag_name"), MYSQLI_ASSOC);

$new_count = count(array_filter($audio_files, fn($f) => !$f['exists']));
$existing_count = count(array_filter($audio_files, fn($f) => $f['exists']));
?>

<style>
.import-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    color: white;
    border-radius: 10px;
    margin: 20px;
}
.stats-box {
    background: white;
    padding: 20px;
    margin: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.stat-item {
    display: inline-block;
    margin-right: 40px;
}
.stat-number {
    font-size: 42px;
    font-weight: bold;
    color: #667eea;
}
.audio-file-item {
    padding: 15px;
    background: white;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid #667eea;
}
.audio-file-item.exists {
    background: #f0f0f0;
    border-left-color: #999;
    opacity: 0.6;
}
.select-all-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>🎵 Bulk Audio Import</h1>
                    <p style="color: #666;">Import audio files from images folder to database</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="show_free_video_audio.php">Free Media</a></li>
                        <li class="breadcrumb-item active">Import Audio</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="stats-box">
        <div class="stat-item">
            <div class="stat-number"><?php echo count($audio_files); ?></div>
            <div>Total Audio Files</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color: #28a745;"><?php echo $new_count; ?></div>
            <div>New Files (Not in DB)</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color: #999;"><?php echo $existing_count; ?></div>
            <div>Already Imported</div>
        </div>
    </div>

    <?php if ($new_count > 0): ?>
    <form method="POST" style="margin: 20px;">
        <div class="card">
            <div class="card-header" style="background: #667eea; color: white;">
                <h3 class="card-title">📥 Import Settings</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label><strong>Default Language *</strong></label>
                        <select name="default_language" required class="form-control">
                            <option value="">Select language...</option>
                            <?php foreach ($languages as $lang): ?>
                                <option value="<?php echo $lang['id']; ?>"><?php echo $lang['value']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label><strong>Default Tag</strong></label>
                        <select name="default_tag" class="form-control">
                            <option value="0">None</option>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>"><?php echo $tag['tag_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label><br>
                        <button type="button" class="select-all-btn" onclick="selectAllNew()">
                            ✅ Select All New Files
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audio Files List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🎵 Audio Files</h3>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php foreach ($audio_files as $file): ?>
                    <div class="audio-file-item <?php echo $file['exists'] ? 'exists' : ''; ?>">
                        <label style="margin: 0; width: 100%; cursor: pointer;">
                            <input type="checkbox" 
                                   name="audio_files[]" 
                                   value="<?php echo htmlspecialchars($file['filename']); ?>"
                                   <?php echo $file['exists'] ? 'disabled checked' : ''; ?>
                                   class="new-file-checkbox"
                                   style="margin-right: 10px;">
                            <strong><?php echo htmlspecialchars($file['filename']); ?></strong>
                            <span style="float: right; color: #666;">
                                <?php echo number_format($file['size'] / 1024 / 1024, 2); ?> MB
                                <?php if ($file['exists']): ?>
                                    <span style="color: #28a745; margin-left: 10px;">✓ Already Imported</span>
                                <?php endif; ?>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer">
                <button type="submit" name="import_audio" class="btn btn-success btn-lg">
                    <i class="fas fa-upload"></i> Import Selected Audio Files
                </button>
            </div>
        </div>
    </form>
    <?php else: ?>
    <div class="alert alert-success" style="margin: 20px; padding: 30px; text-align: center;">
        <h3>✅ All audio files have been imported!</h3>
        <p>Go to <a href="show_free_video_audio.php">Free Media Management</a> to add them as free content.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function selectAllNew() {
    document.querySelectorAll('.new-file-checkbox:not([disabled])').forEach(cb => cb.checked = true);
}
</script>

<?php include("footer.php"); ?>
