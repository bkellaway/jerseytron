<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Include dependencies
require 'vendor/autoload.php';
require 'config.php';

// Basic input validation
if (!isset($_POST['prompt']) || empty(trim($_POST['prompt']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt cannot be empty.']);
    exit;
}

$prompt = $_POST['prompt'];
// A base prompt to guide the AI towards creating hockey jerseys
$full_prompt = "A high-resolution, photorealistic image of a custom sublimated hockey jersey, front view, on a mannequin. The design should be: " . $prompt;

// --- Gemini API Call ---

// The API endpoint for the Gemini 2.5 Flash Image model
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateImage';

// The payload for the API request
$payload = json_encode([
    'prompt' => $full_prompt,
    'count' => 1, // Number of images to generate
    'quality' => 'hd', // Set to 'hd' or 'standard'
    'response_mime_type' => 'image/png', // We want PNGs
    'response_b64_json' => true // We want the image as a base64 string
]);

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    // --- THIS LINE IS THE FIX ---
    'x-goog-api-key: ' . GEMINI_API_KEY // Use the constant, not the variable
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Recommended for production

// Execute the request
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Close cURL session
curl_close($ch);

// --- Handle Response ---

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . $error]);
    exit;
}

if ($httpcode >= 400) {
    http_response_code($httpcode);
    // Try to decode the API's error message
    $error_details = json_decode($response, true);
    $message = isset($error_details['error']['message']) ? $error_details['error']['message'] : $response;
    echo json_encode(['error' => 'API Error: ' . $message]);
    exit;
}

$result = json_decode($response, true);

if (!isset($result['images'][0]['b64Json'])) {
    http_response_code(500);
    echo json_encode(['error' => 'API did not return a valid image. Check your prompt or API key.']);
    exit;
}

try {
    $base64Image = $result['images'][0]['b64Json'];
    $imageData = base64_decode($base64Image);

    if ($imageData === false) {
        throw new Exception('Failed to decode base64 image string.');
    }

    // Save the image to a file
    $fileName = 'jersey_' . uniqid() . '.png';
    $filePath = 'generated_images/' . $fileName;
    
    if (file_put_contents($filePath, $imageData) === false) {
        throw new Exception('Failed to save image to server. Check directory permissions.');
    }

    // Return the URL of the saved image
    echo json_encode(['imageUrl' => $filePath]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
}
?>