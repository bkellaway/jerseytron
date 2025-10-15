<?php
require 'config.php';

function removeBackground($inputPath, $outputPath) {
    if (!ENABLE_BG_REMOVAL || empty(REMOVEBG_API_KEY)) {
        return false;
    }
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, 'https://api.remove.bg/v1.0/removebg');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'image_file' => new CURLFile($inputPath),
        'size' => 'auto',
        'format' => 'png'
    ]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Api-Key: ' . REMOVEBG_API_KEY
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        file_put_contents($outputPath, $result);
        return true;
    }
    
    return false;
}
?>