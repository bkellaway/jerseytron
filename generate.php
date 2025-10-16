<?php
// Debug version of generate.php to help diagnose the issue
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1); // Don't output errors to browser, we'll handle them

// Include dependencies
require 'config.php';

// Log function to help debug
function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . ' - DEBUG: ' . $message . "\n", 3, 'debug.log');
}

debug_log("=== Starting generate.php debug ===");

try {
    // Basic input validation
    if (!isset($_POST['prompt']) || empty(trim($_POST['prompt']))) {
        debug_log("ERROR: Empty prompt");
        http_response_code(400);
        echo json_encode(['error' => 'Prompt cannot be empty.']);
        exit;
    }

    // Check if API key is set
    if (empty(GEMINI_API_KEY)) {
        debug_log("ERROR: API key is empty");
        http_response_code(500);
        echo json_encode(['error' => 'API key is not configured.']);
        exit;
    }

    debug_log("Prompt received: " . $_POST['prompt']);
    debug_log("Using model: " . GEMINI_MODEL);
    debug_log("API key present: " . (empty(GEMINI_API_KEY) ? 'NO' : 'YES'));

    $prompt = $_POST['prompt'];
//    $full_prompt = "A high-resolution, photorealistic image of a custom sublimated hockey jersey, front view, on a mannequin. The design should be: " . $prompt;

//	$full_prompt = "A high-resolution, photorealistic image of a custom sublimated hockey jersey, front view, on a mannequin in a T-pose with arms extended horizontally at shoulder height, perfectly straight with no elbow bend. The design should be: " . $prompt;


//$full_prompt = "A high-resolution, photorealistic image of a custom sublimated hockey jersey, FRONT VIEW, on a white or neutral background. The jersey is on a headless mannequin in a perfect T-POSE. The mannequin's arms must be extended perfectly horizontal at 90-degrees from the body, straight with ZERO elbow bend. The jersey is perfectly centered and flat, with no folds or wrinkles. The design should be: " . $prompt;

//$full_prompt = "A high-resolution, photorealistic image of a custom sublimated hockey jersey, FRONT VIEW, on a TRANSPARENT BACKGROUND.  The jersey is on a headless mannequin in a perfect T-POSE. The mannequin's arms must be extended perfectly horizontal at 90-degrees from the body, straight with ZERO elbow bend. The jersey is perfectly centered and flat, with no folds or wrinkles. The design should be: " . $prompt;

// Fetch the reference image from your server
$reference_jersey_url = "https://jerseydesigner.hockeytron.com/reference_images/3d-Mesh-to-2D-for-AI-Texture.png";
$reference_image_data = file_get_contents($reference_jersey_url);
$reference_image_base64 = base64_encode($reference_image_data);

// Build the multimodal prompt
$text_prompt = "Using the first image as a strict shape and pose reference, generate a high-resolution, photorealistic image of a custom sublimated hockey jersey. The jersey should maintain the exact FRONT VIEW, pose, and dimensions shown in the reference image. Apply the following design to the jersey: " . $prompt;

// Prepare the API request with both images
$contents = [
    [
        'parts' => [
            // Reference image first
            [
                'inline_data' => [
                    'mime_type' => 'image/png',
                    'data' => $reference_image_base64
                ]
            ],
            // Text prompt
            [
                'text' => $text_prompt
            ]
        ]
    ]
];



    debug_log("Full prompt: " . $full_prompt);

    // The correct API endpoint for Gemini 2.5 Flash Image Preview
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent';
    debug_log("API URL: " . $apiUrl);

    // The correct payload format
    $payload = json_encode([
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $full_prompt
                    ]
                ]
            ]
        ]
    ]);

    debug_log("Payload: " . $payload);

    // Initialize cURL session
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-goog-api-key: ' . GEMINI_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    // Execute the request
    debug_log("Making API request...");
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    debug_log("HTTP Code: " . $httpcode);
    debug_log("cURL Error: " . ($error ?: 'None'));
    debug_log("Response length: " . strlen($response));
    debug_log("Response preview: " . substr($response, 0, 500));

    curl_close($ch);

    if ($error) {
        debug_log("cURL error occurred: " . $error);
        http_response_code(500);
        echo json_encode(['error' => 'cURL Error: ' . $error]);
        exit;
    }

    if ($httpcode >= 400) {
        debug_log("HTTP error: " . $httpcode);
        http_response_code($httpcode);
        // Try to decode the API's error message
        $error_details = json_decode($response, true);
        $message = isset($error_details['error']['message']) ? $error_details['error']['message'] : $response;
        debug_log("API error message: " . $message);
        echo json_encode(['error' => 'API Error: ' . $message]);
        exit;
    }

    $result = json_decode($response, true);

    if (!$result) {
        debug_log("Failed to decode JSON response");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to decode API response']);
        exit;
    }

    // Check if we got a valid response
    if (!isset($result['candidates'][0]['content']['parts'])) {
        debug_log("Unexpected response structure");
        http_response_code(500);
        echo json_encode(['error' => 'Unexpected API response structure']);
        exit;
    }

    // Look for the image in the response parts
    $imageData = null;
    foreach ($result['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['inlineData']['data'])) {
            $imageData = $part['inlineData']['data'];
            debug_log("Found image data, length: " . strlen($imageData));
            break;
        }
    }

    if (!$imageData) {
        debug_log("No image data found in response");
        http_response_code(500);
        echo json_encode(['error' => 'API did not return an image']);
        exit;
    }

    $decodedImage = base64_decode($imageData);

    if ($decodedImage === false) {
        debug_log("Failed to decode base64 image");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to decode image data']);
        exit;
    }

    // Ensure the output directory exists
    if (!is_dir('generated_images') && !mkdir('generated_images', 0775, true)) {
        debug_log("Failed to create generated_images directory");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create image directory']);
        exit;
    }

    // Save the image to a file
    $fileName = 'jersey_' . uniqid() . '.png';
    $filePath = 'generated_images/' . $fileName;
    
    if (file_put_contents($filePath, $decodedImage) === false) {
        debug_log("Failed to save image to: " . $filePath);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save image']);
        exit;
    }

/* NEW: Remove background
require_once 'remove_background.php';

$noBgFileName = 'jersey_nobg_' . uniqid() . '.png';
$noBgFilePath = 'generated_images/' . $noBgFileName;

// Start output buffering to capture any stray output
ob_start();

// Call the removeBackground function
$result = removeBackground($filePath, $noBgFilePath);

// Discard any output that was generated by the function
ob_end_clean();

// Now, send the JSON response
if ($result) {
    debug_log("SUCCESS: Background removed, saved to " . $noBgFilePath);
    header('Content-Type: application/json');
    echo json_encode(['imageUrl' => $noBgFilePath, 'originalUrl' => $filePath]);
} else {
    debug_log("Background removal failed or disabled, using original");
    header('Content-Type: application/json');
    echo json_encode(['imageUrl' => $filePath]);
}
exit;

*/ 

    debug_log("SUCCESS: Image saved to " . $filePath);
    echo json_encode(['imageUrl' => $filePath]);

} catch (Exception $e) {
    debug_log("Exception occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
} catch (Error $e) {
    debug_log("Fatal error occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Fatal Error: ' . $e->getMessage()]);
}


debug_log("=== End generate.php debug ===");
