<?php
// xml_cleaner.php – Funkcje do czyszczenia i scalania XML z InDesign IDML

/**
 * Scal sąsiadujące tagi <Content> w obrębie jednego CharacterStyleRange
 */
function mergeAdjacentContentTags(string $xml): string {
    return preg_replace_callback(
        '/(<CharacterStyleRange\b[^>]*>)(.*?)<\/CharacterStyleRange>/su',
        function ($m) {
            $inner = $m[2];
            // Usuń puste Content
            $inner = preg_replace('/<Content>\s*<\/Content>/su', '', $inner);
            // Scal sąsiadujące Content zachowując spacje, ale usuwając nowe linie (formatowanie XML)
            $inner = preg_replace_callback('/<\/Content>(\s*)<Content>/su', function($m) {
                $ws = $m[1];
                if (strpos($ws, "\n") !== false || strpos($ws, "\r") !== false) {
                    return ""; // Usuń nowe linie i taby (formatowanie)
                }
                return $ws; // Zachowaj spacje poziome
            }, $inner);
            // Dodatkowe zabezpieczenie - nie usuwaj wszystkich białych znaków z innych struktur
            $inner = preg_replace('/>\s+</', '> <', $inner);
            return $m[1] . $inner . '</CharacterStyleRange>';
        },
        $xml
    );
}

/**
 * Scala pocięte CharacterStyleRange w ramach jednego ParagraphStyleRange
 * Rozwiązuje problem InDesign gdzie jedno słowo jest rozbite na wiele tagów
 * 
 * Przykład przed:
 * <CharacterStyleRange>Efekt autorun: re</CharacterStyleRange>
 * <CharacterStyleRange Tracking="-10">gulacj</CharacterStyleRange>
 * <CharacterStyleRange>a prędkości, od </CharacterStyleRange>
 * 
 * Przykład po:
 * <CharacterStyleRange><Content>Efekt autorun: regulacja prędkości, od </Content></CharacterStyleRange>
 */
function fixFragmentedIDML(string $xml): string {
    return preg_replace_callback(
        '/(<ParagraphStyleRange[^>]*>)(.*?)(<\/ParagraphStyleRange>)/su',
        function ($paragraphMatch) {
            $prefix = $paragraphMatch[1];
            $innerContent = $paragraphMatch[2];
            $suffix = $paragraphMatch[3];
            
            // 1. Wyciągnij wszystkie CharacterStyleRange
            // Szukamy tagów otwierających, zawartości i zamykających
            $csrRegex = '/(<CharacterStyleRange\s*([^>]*)>)(.*?)(<\/CharacterStyleRange>)/su';
            
            // Dopasuj wszystkie bloki
            if (!preg_match_all($csrRegex, $innerContent, $matches, PREG_SET_ORDER)) {
                return $paragraphMatch[0]; // Brak zmian jeśli nie ma CharacterStyleRange
            }

            $mergedBlocks = [];
            $currentBlock = null;

            foreach ($matches as $match) {
                // $match[1] - pełny tag otwierający
                // $match[2] - tylko atrybuty
                // $match[3] - zawartość w środku
                // $match[4] - tag zamykający
                
                $attrs = $match[2];
                $content = $match[3];

                // 2. Normalizacja atrybutów do porównania
                // Usuwamy techniczne "szumy", które powodują rozbijanie (Tracking, Kerning itp.)
                $normalizedAttrs = preg_replace('/\s+(Tracking|KerningValue|HorizontalScale|VerticalScale|Leading|AppliedLanguage)="[^"]*"/u', '', $attrs);
                $normalizedAttrs = trim($normalizedAttrs);

                // 3. Specjalna obsługa "pułapek" - Indeksy górne i dolne (Stopnie, H2O, metry)
                $isSuperscript = strpos($attrs, 'Position="Superscript"') !== false;
                $isSubscript = strpos($attrs, 'Position="Subscript"') !== false;
                
                $superscriptMap = [
                    '0' => '°',
                    'o' => '°',
                    '1' => '¹',
                    '2' => '²',
                    '3' => '³',
                    '4' => '⁴',
                    '5' => '⁵',
                    '6' => '⁶',
                    '7' => '⁷',
                    '8' => '⁸',
                    '9' => '⁹',
                    '+' => '⁺',
                    '-' => '⁻',
                    '=' => '⁼',
                    '(' => '⁽',
                    ')' => '⁾',
                    'n' => 'ⁿ',
                ];
                
                $subscriptMap = [
                    '0' => '₀', '1' => '₁', '2' => '₂', '3' => '₃', '4' => '₄',
                    '5' => '₅', '6' => '₆', '7' => '₇', '8' => '₈', '9' => '₉',
                    '+' => '₊', '-' => '₋', '=' => '₌', '(' => '₍', ')' => '₎',
                    'a' => 'ₐ', 'e' => 'ₑ', 'h' => 'ₕ', 'i' => 'ᵢ', 'j' => 'ⱼ',
                    'k' => 'ₖ', 'l' => 'ₗ', 'm' => 'ₘ', 'n' => 'ₙ', 'o' => 'ₒ',
                    'p' => 'ₚ', 'r' => 'ᵣ', 's' => 'ₛ', 't' => 'ₜ', 'u' => 'ᵤ',
                    'v' => 'ᵥ', 'x' => 'ₓ'
                ];

                if (($isSuperscript || $isSubscript) && $currentBlock !== null) {
                    // Wyciągnij czysty tekst z Content
                    if (preg_match('/<Content>(.*?)<\/Content>/su', $content, $contentMatch)) {
                        $char = trim($contentMatch[1]);
                        $unicodeChar = null;
                        
                        if ($isSuperscript && isset($superscriptMap[$char])) {
                            $unicodeChar = $superscriptMap[$char];
                        } else if ($isSubscript && isset($subscriptMap[$char])) {
                            $unicodeChar = $subscriptMap[$char];
                        }
                        
                        if ($unicodeChar !== null) {
                            // Znaleziono "pułapkę" - zamień na Unicode i doklej do poprzedniego bloku
                            // Usuwamy ewentualne białe znaki na końcu poprzedniego bloku, by Unicode przylegał bezpośrednio
                            $currentBlock['content'] = preg_replace('/<\/Content>\s*$/su', "{$unicodeChar}</Content>", $currentBlock['content']);
                            continue; // Nie twórz nowego bloku dla tego indeksu!
                        }
                    }
                }

                if ($currentBlock === null) {
                    // Pierwszy blok
                    $currentBlock = [
                        'attrs' => $attrs,
                        'norm' => $normalizedAttrs,
                        'content' => $content
                    ];
                } else if ($currentBlock['norm'] === $normalizedAttrs) {
                    // Ten sam styl - MERGE
                    $currentBlock['content'] .= $content;
                } else {
                    // Inny styl - zamknij poprzedni, zacznij nowy
                    $mergedBlocks[] = "<CharacterStyleRange {$currentBlock['attrs']}>{$currentBlock['content']}</CharacterStyleRange>";
                    $currentBlock = [
                        'attrs' => $attrs,
                        'norm' => $normalizedAttrs,
                        'content' => $content
                    ];
                }
            }

            // Dodaj ostatni blok
            if ($currentBlock) {
                $mergedBlocks[] = "<CharacterStyleRange {$currentBlock['attrs']}>{$currentBlock['content']}</CharacterStyleRange>";
            }

            // Odtwórz strukturę, zachowując ewentualne białe znaki poza Tagami (choć zwykle ich nie ma w ParagraphStyleRange w IDML)
            $newInner = implode("\n\t\t\t", $mergedBlocks); // Dodaj lekkie formatowanie dla czytelności XML

            return $prefix . $newInner . $suffix;
        },
        $xml
    );
}

/**
 * Napraw brakujące spacje między tagami <Content>
 */
function fixMissingSpaces(string $xmlContent): string {
    // 1) spacje między kolejnymi Content, jeśli kończą się interpunkcją lub są to osobne bloki
    $xmlContent = preg_replace(
        '/([.!?:,])<\/Content>\s*<Content>(?=\p{L}|\p{N})/u',
        '$1</Content> <Content>',
        $xmlContent
    );

    // 2) ewentualne podwójne spacje po >
    $xmlContent = preg_replace(
        '/(>)[ ]{2,}(<Content>)/u',
        '$1 $2',
        $xmlContent
    );

    return $xmlContent;
}

/**
 * Kompletne czyszczenie XML IDML - użyj tej funkcji jako głównej
 */
function cleanIDMLXML(string $xml): string {
    // Krok 0: Usuń tagi HyperlinkTextDestination, by nie blokowały scalenia sąsiadujących Content
    $xml = preg_replace('/<HyperlinkTextDestination[^>]*\/>/su', '', $xml);

    // Krok 1: Scal pocięte CharacterStyleRange (z zachowaniem stylów)
    $xml = fixFragmentedIDML($xml);
    
    // Krok 2: Napraw brakujące spacje (zanim scalimy tagi Content)
    $xml = fixMissingSpaces($xml);

    // Krok 3: Scal sąsiadujące Content wewnątrz CharacterStyleRange
    $xml = mergeAdjacentContentTags($xml);
    
    return $xml;
}