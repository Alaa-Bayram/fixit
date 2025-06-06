<?php
function translateText($text, $langPair) {
    $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($text) . '&langpair=' . $langPair;
    
    $result = file_get_contents($url);
    $response = json_decode($result, true);
    
    return $response['responseData']['translatedText'];
}
?>
