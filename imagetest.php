<?php
/**
 * List all available models for your API key
 * This helps verify which models you have access to
 */

require 'config.php';

if (empty(GEMINI_API_KEY)) {
    die("ERROR: Please set your GEMINI_API_KEY in config.php\n");
}

echo "Listing available models for your API key...\n";
echo "API Key: " . substr(GEMINI_API_KEY, 0, 8) . "...\n\n";

$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'x-goog-api-key: ' . GEMINI_API_KEY
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("cURL Error: $error\n");
}

echo "HTTP Response Code: $httpCode\n\n";

if ($httpCode >= 400) {
    echo "API Error:\n";
    echo $response . "\n";
    exit(1);
}

$result = json_decode($response, true);

if (!$result) {
    echo "Failed to parse JSON response:\n";
    echo $response . "\n";
    exit(1);
}

if (isset($result['models'])) {
    echo "Available models:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($result['models'] as $model) {
        $name = $model['name'];
        $displayName = isset($model['displayName']) ? $model['displayName'] : 'N/A';
        $description = isset($model['description']) ? $model['description'] : 'N/A';
        $supportedMethods = isset($model['supportedGenerationMethods']) ? implode(', ', $model['supportedGenerationMethods']) : 'N/A';
        
        echo "Name: $name\n";
        echo "Display Name: $displayName\n";
        echo "Description: $description\n";
        echo "Supported Methods: $supportedMethods\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    // Check if image model is available
    $imageModelFound = false;
    foreach ($result['models'] as $model) {
        if (strpos($model['name'], 'gemini-2.5-flash-image') !== false) {
            echo "\n? Found image generation model: " . $model['name'] . "\n";
            $imageModelFound = true;
        }
    }
    
    if (!$imageModelFound) {
        echo "\n? No image generation models found. You may need:\n";
        echo "   - A different API key with image generation access\n";
        echo "   - To enable billing on your Google Cloud account\n";
        echo "   - To wait for model availability in your region\n";
    }
    
} else {
    echo "Unexpected response format:\n";
    print_r($result);
}
?>