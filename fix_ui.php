<?php
$file = 'd:\translator\translate_ui.php';
$content = file_get_contents($file);
$pos = strpos($content, 'window.addEventListener(\'DOMContentLoaded\', highlightLongTranslations);');
if ($pos !== false) {
    $clean = substr($content, 0, $pos);
    $clean .= "window.addEventListener('DOMContentLoaded', highlightLongTranslations);\n</script>\n</body>\n</html>";
    file_put_contents($file, $clean);
    echo "Fixed!";
} else {
    echo "Marker not found!";
}
