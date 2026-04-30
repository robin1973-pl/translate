<?php
$tempPath = __DIR__ . '/temp/';
$realTempPath = realpath($tempPath);
echo "tempPath: $tempPath\n";
echo "realTempPath: $realTempPath\n";

// Simulate a file path
$simulatedFile = $realTempPath . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'Graphic.xml';
echo "simulatedFile: $simulatedFile\n";

$localPath = substr($simulatedFile, strlen($tempPath));
echo "localPath (using tempPath): '$localPath'\n";

$localPath2 = substr($simulatedFile, strlen($realTempPath));
echo "localPath (using realTempPath): '$localPath2'\n";
