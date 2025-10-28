<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Upload Configuration Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-box h2 {
            color: #333;
            margin-top: 0;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            font-weight: bold;
            color: #555;
        }
        .config-value {
            color: #007bff;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="info-box">
        <h2>📋 PHP Upload Configuration Check</h2>
        
        <?php
        function formatBytes($bytes) {
            $units = ['B', 'KB', 'MB', 'GB'];
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            return round($bytes, 2) . ' ' . $units[$i];
        }

        function convertToBytes($size) {
            $size = trim($size);
            $last = strtolower($size[strlen($size) - 1]);
            $size = (int) $size;

            switch ($last) {
                case 'g':
                    $size *= 1024;
                case 'm':
                    $size *= 1024;
                case 'k':
                    $size *= 1024;
            }
            return $size;
        }

        $uploadMaxFilesize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $memoryLimit = ini_get('memory_limit');
        $maxExecutionTime = ini_get('max_execution_time');
        $maxInputTime = ini_get('max_input_time');
        $maxFileUploads = ini_get('max_file_uploads');

        $uploadBytes = convertToBytes($uploadMaxFilesize);
        $postBytes = convertToBytes($postMaxSize);
        $memoryBytes = convertToBytes($memoryLimit);

        $recommended = $uploadBytes >= (200 * 1024 * 1024) && 
                       $postBytes >= (210 * 1024 * 1024) &&
                       $maxExecutionTime >= 300;
        ?>

        <div class="config-item">
            <span class="config-label">PHP Version:</span>
            <span class="config-value"><?php echo phpversion(); ?></span>
        </div>
        <div class="config-item">
            <span class="config-label">Loaded Configuration File:</span>
            <span class="config-value"><?php echo php_ini_loaded_file(); ?></span>
        </div>
        <div class="config-item">
            <span class="config-label">upload_max_filesize:</span>
            <span class="config-value"><?php echo $uploadMaxFilesize . ' (' . formatBytes($uploadBytes) . ')'; ?></span>
        </div>
        <div class="config-item">
            <span class="config-label">post_max_size:</span>
            <span class="config-value"><?php echo $postMaxSize . ' (' . formatBytes($postBytes) . ')'; ?></span>
        </div>
        <div class="config-item">
            <span class="config-label">memory_limit:</span>
            <span class="config-value"><?php echo $memoryLimit . ' (' . formatBytes($memoryBytes) . ')'; ?></span>
        </div>
        <div class="config-item">
            <span class="config-label">max_execution_time:</span>
            <span class="config-value"><?php echo $maxExecutionTime; ?> seconds</span>
        </div>
        <div class="config-item">
            <span class="config-label">max_input_time:</span>
            <span class="config-value"><?php echo $maxInputTime; ?> seconds</span>
        </div>
        <div class="config-item">
            <span class="config-label">max_file_uploads:</span>
            <span class="config-value"><?php echo $maxFileUploads; ?> files</span>
        </div>
    </div>

    <div class="info-box">
        <?php if ($recommended): ?>
            <div class="success">
                ✅ <strong>Configuration looks good!</strong><br>
                Your PHP settings are configured to handle large file uploads.
            </div>
        <?php else: ?>
            <div class="warning">
                ⚠️ <strong>Configuration needs adjustment!</strong><br><br>
                <strong>Recommended settings for large uploads:</strong><br>
                • upload_max_filesize = 200M or higher<br>
                • post_max_size = 210M or higher (must be larger than upload_max_filesize)<br>
                • max_execution_time = 300 or higher<br>
                • memory_limit = 256M or higher<br><br>
                <strong>How to fix:</strong><br>
                1. Edit <code><?php echo php_ini_loaded_file(); ?></code><br>
                2. Update the values mentioned above<br>
                3. Restart Apache in XAMPP Control Panel<br>
                4. Refresh this page to verify changes
            </div>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <h2>🔍 How to Update php.ini in XAMPP</h2>
        <ol>
            <li>Open XAMPP Control Panel</li>
            <li>Click "Config" button next to Apache</li>
            <li>Select "PHP (php.ini)"</li>
            <li>Find and update these lines:
                <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px;">
upload_max_filesize = 200M
post_max_size = 210M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
max_file_uploads = 50</pre>
            </li>
            <li>Save the file</li>
            <li>Stop Apache in XAMPP Control Panel</li>
            <li>Start Apache again</li>
            <li>Refresh this page to verify</li>
        </ol>
    </div>

    <div style="text-align: center;">
        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn">🔄 Refresh Check</a>
        <a href="/" class="btn" style="background: #6c757d;">🏠 Back to Application</a>
    </div>
</body>
</html>
