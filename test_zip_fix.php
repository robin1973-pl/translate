<?php
// Silmulate the ZIP path logic
$config = ['temp_dir' => __DIR__ . '/temp/'];
@mkdir($config['temp_dir']);
@mkdir($config['temp_dir'] . 'Resources');
touch($config['temp_dir'] . 'Resources/Graphic.xml');

$realTempPath = realpath($config['temp_dir']);
$dir = new RecursiveDirectoryIterator($realTempPath, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dir);

echo "Base Path: $realTempPath\n";
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $realPath = $file->getRealPath();
        $relative = substr($realPath, strlen($realTempPath));
        $relative = ltrim($relative, DIRECTORY_SEPARATOR);
        $zipPath = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
        echo "File: $realPath\n";
        echo "Internal ZIP Path: $zipPath\n";
        
        if (strpos($zipPath, 'Resources/') === 0) {
            echo "SUCCESS: Folder structure preserved.\n";
        } else {
            echo "FAILURE: Folder structure LOST! (Path: $zipPath)\n";
        }
    }
}

// Cleanup
@unlink($config['temp_dir'] . 'Resources/Graphic.xml');
@rmdir($config['temp_dir'] . 'Resources');
