<?php
require_once 'helpers/xml_cleaner.php';

$xml = '
<ParagraphStyleRange AppliedParagraphStyle="ParagraphStyle/TEXT">
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]">
        <Content>Wzór chemiczny wody to H</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]" Position="Subscript">
        <Content>2</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]">
        <Content>O</Content>
    </CharacterStyleRange>
</ParagraphStyleRange>
';

$cleaned = cleanIDMLXML($xml);
echo "--- CLEANED XML ---\n";
echo $cleaned;
echo "\n--- END ---\n";

if (strpos($cleaned, 'H₂O') !== false) {
    echo "SUCCESS: Subscripts consolidated successfully (H₂O).\n";
} else {
    echo "FAILURE: Subscripts NOT consolidated.\n";
}
