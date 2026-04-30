<?php
require_once 'helpers/xml_cleaner.php';

function runTest($name, $xml, $expectedSubstrings, $unexpectedSubstrings = []) {
    echo "Testing: $name... ";
    $cleaned = cleanIDMLXML($xml);
    
    $passed = true;
    foreach ($expectedSubstrings as $expected) {
        if (strpos($cleaned, $expected) === false) {
            echo "\n  FAILURE: Expected substring '$expected' not found.";
            $passed = false;
        }
    }
    
    foreach ($unexpectedSubstrings as $unexpected) {
        if (strpos($cleaned, $unexpected) !== false) {
            echo "\n  FAILURE: Unexpected substring '$unexpected' found.";
            $passed = false;
        }
    }
    
    if ($passed) {
        echo "PASSED\n";
    } else {
        echo "\n--- OUTPUT ---\n$cleaned\n--------------\n";
    }
    return $passed;
}

// 1. Fragmented CSR with same style
runTest(
    "Fragmented CSR Merge",
    '<ParagraphStyleRange AppliedParagraphStyle="P1">
        <CharacterStyleRange AppliedCharacterStyle="C1" Tracking="0"><Content>Hello </Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1" Tracking="-10"><Content>World</Content></CharacterStyleRange>
    </ParagraphStyleRange>',
    ['<Content>Hello World</Content>'],
    ['Tracking="-10"'] // Tracking should be stripped from the second one and then merged
);

// 2. Fragmented CSR with different style
runTest(
    "Different Styles No Merge",
    '<ParagraphStyleRange AppliedParagraphStyle="P1">
        <CharacterStyleRange AppliedCharacterStyle="C1"><Content>Hello </Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C2"><Content>World</Content></CharacterStyleRange>
    </ParagraphStyleRange>',
    ['AppliedCharacterStyle="C1"', 'AppliedCharacterStyle="C2"', '<Content>Hello </Content>', '<Content>World</Content>']
);

// 3. Hyperlink removal
runTest(
    "Hyperlink Removal",
    '<CharacterStyleRange AppliedCharacterStyle="C1"><Content>Link</Content><HyperlinkTextDestination Destination="123"/><Content> text</Content></CharacterStyleRange>',
    ['<Content>Link text</Content>'],
    ['HyperlinkTextDestination']
);

// 4. Subscripts & Superscripts
runTest(
    "Unicode Sub/Superscripts",
    '<ParagraphStyleRange AppliedParagraphStyle="P1">
        <CharacterStyleRange AppliedCharacterStyle="C1"><Content>H</Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1" Position="Subscript"><Content>2</Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1"><Content>O and 40</Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1" Position="Superscript"><Content>0</Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1"><Content> m</Content></CharacterStyleRange>
        <CharacterStyleRange AppliedCharacterStyle="C1" Position="Superscript"><Content>2</Content></CharacterStyleRange>
    </ParagraphStyleRange>',
    ['H₂O', '40°', 'm²']
);

// 5. Missing spaces
runTest(
    "Missing Spaces Fix",
    '<CharacterStyleRange AppliedCharacterStyle="C1"><Content>End of sentence.</Content><Content>Start of next.</Content></CharacterStyleRange>',
    ['<Content>End of sentence. Start of next.</Content>']
);
