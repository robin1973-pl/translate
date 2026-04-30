<?php
require_once 'helpers/xml_cleaner.php';

$xml = '
<ParagraphStyleRange AppliedParagraphStyle="ParagraphStyle/TEXT">
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]">
        <Content>Maximum ambient temperature is Ta : 40</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]" Position="Superscript">
        <Content>0</Content>
    </CharacterStyleRange>
</ParagraphStyleRange>
<ParagraphStyleRange AppliedParagraphStyle="ParagraphStyle/TEXT">
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]">
        <Content>Powierzchnia: 100 m</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/$ID/[No character style]" Position="Superscript">
        <Content>2</Content>
    </CharacterStyleRange>
</ParagraphStyleRange>
';

$cleaned = cleanIDMLXML($xml);
echo "--- CLEANED XML ---\n";
echo $cleaned;
echo "\n--- END ---\n";

if (strpos($cleaned, '40°') !== false && strpos($cleaned, 'm²') !== false) {
    echo "SUCCESS: Superscripts consolidated successfully.\n";
} else {
    echo "FAILURE: Superscripts NOT consolidated.\n";
}
