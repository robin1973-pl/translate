<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$docxFile = '2. Cisza nie chroni.docx';
$pptxFile = 'Europe.pptx';

function test_extract($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $tempDir = 'test_extract_' . $ext . '/';
    if (!is_dir($tempDir)) mkdir($tempDir);

    $zip = new ZipArchive;
    if ($zip->open($filename) === TRUE) {
        $zip->extractTo($tempDir);
        $zip->close();
        echo "Successfully unzipped $filename to $tempDir<br>";
    } else {
        echo "Failed to unzip $filename<br>";
        return;
    }

    if ($ext === 'docx') {
        $xmlFile = $tempDir . 'word/document.xml';
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $texts = $xml->xpath('//w:t');
            echo "Found " . count($texts) . " text segments in $filename.<br>";
            foreach (array_slice($texts, 0, 5) as $t) {
                echo "- " . (string)$t . "<br>";
            }
        }
    } elseif ($ext === 'pptx') {
        $slideFiles = glob($tempDir . 'ppt/slides/slide*.xml');
        echo "Found " . count($slideFiles) . " slides in $filename.<br>";
        foreach (array_slice($slideFiles, 0, 1) as $file) {
            $xml = simplexml_load_file($file);
            $xml->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            $texts = $xml->xpath('//a:t');
            echo "Slide 1: Found " . count($texts) . " text segments.<br>";
            foreach (array_slice($texts, 0, 5) as $t) {
                echo "- " . (string)$t . "<br>";
            }
        }
    }
}

test_extract($docxFile);
echo "<hr>";
test_extract($pptxFile);
?>
