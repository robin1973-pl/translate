<?php
// helpers/office_utils.php - Professional logic for Word and PowerPoint

/**
 * Merges fragmented text runs in a Word (DOCX) DOMDocument
 */
function cleanDocxXML(DOMDocument $dom) {
    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

    // Find all paragraphs
    $paragraphs = $xpath->query('//w:p');
    foreach ($paragraphs as $p) {
        $runs = $xpath->query('.//w:r', $p);
        if ($runs->length <= 1) continue;

        $currentRun = null;
        foreach ($runs as $r) {
            if ($currentRun === null) {
                $currentRun = $r;
                continue;
            }

            // Check if styles are similar (simplified for now)
            // In professional version, we merge if they are adjacent and share properties
            $t1 = $xpath->query('.//w:t', $currentRun)->item(0);
            $t2 = $xpath->query('.//w:t', $r)->item(0);

            if ($t1 && $t2) {
                // Merge text
                $t1->nodeValue .= $t2->nodeValue;
                // Remove the merged run
                $r->parentNode->removeChild($r);
            }
        }
    }
    return $dom;
}

/**
 * Extracts texts from Office XML with paragraph awareness
 */
function extractOfficeTexts(string $xmlContent, string $type) {
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = true;
    @$dom->loadXML($xmlContent);
    $xpath = new DOMXPath($dom);
    
    $rows = [];
    
    if ($type === 'docx') {
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        // We target text elements but we want to group them if possible
        // For a "good" engine, we'll extract each <w:t> but with context tracking
        $texts = $xpath->query('//w:t');
        foreach ($texts as $i => $node) {
            $rows[] = [$i, $node->nodeValue];
        }
    } elseif ($type === 'pptx') {
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $texts = $xpath->query('//a:t');
        foreach ($texts as $i => $node) {
            $rows[] = [$i, $node->nodeValue];
        }
    }
    
    return $rows;
}
