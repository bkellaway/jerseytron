<?php
// Fixed version of generate.php with reference image support
header('Content-Type: application/json');
error_reporting(E_ALL);
// FIX: Set display_errors to 0 (or 'Off') to prevent PHP from outputting HTML error messages
// This ensures only clean JSON is returned to the client. Errors will still be logged.
ini_set('display_errors', 0); 

// Include dependencies
// NOTE: Assuming config.php defines GEMINI_API_KEY and GEMINI_MODEL
require 'config.php';

// Log function to help debug
function debug_log($message) {
    // Using a static variable to avoid file path errors if needed
    static $log_path = 'debug.log';
    error_log(date('Y-m-d H:i:s') . ' - DEBUG: ' . $message . "\n", 3, $log_path);
}

debug_log("=== Starting generate.php debug ===");

// --- BEGIN: Exponential Backoff Helper Functions (Highly Recommended for API calls) ---
function call_api_with_retry($ch, $apiUrl, $payload) {
    global $GEMINI_API_KEY, $GEMINI_MODEL;

    $max_retries = 5;
    $initial_delay = 1; // seconds

    for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
        // Reset and set cURL options for the attempt
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

        debug_log("Making API request (Attempt $attempt)...");
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            // cURL error, retry on non-fatal issues
            debug_log("cURL error on attempt $attempt: " . $error);
        } elseif ($httpcode === 429 || $httpcode >= 500) {
            // Rate limit or server error, retry
            debug_log("HTTP error $httpcode on attempt $attempt. Retrying...");
        } else {
            // Success or non-retryable client error (4xx)
            return ['response' => $response, 'httpcode' => $httpcode, 'error' => $error];
        }

        if ($attempt < $max_retries) {
            $delay = $initial_delay * pow(2, $attempt - 1);
            debug_log("Waiting for $delay seconds before retrying...");
            sleep($delay);
        }
    }

    // If all retries fail, return the last result/error
    return ['response' => $response, 'httpcode' => $httpcode, 'error' => $error];
}
// --- END: Exponential Backoff Helper Functions ---

try {
    // Basic input validation
    if (!isset($_POST['prompt']) || empty(trim($_POST['prompt']))) {
        debug_log("ERROR: Empty prompt");
        http_response_code(400);
        echo json_encode(['error' => 'Prompt cannot be empty.']);
        exit;
    }

    // Define constants if they were not in config.php (for runtime safety)
    if (!defined('GEMINI_API_KEY')) define('GEMINI_API_KEY', '');
    if (!defined('GEMINI_MODEL')) define('GEMINI_MODEL', 'gemini-2.5-flash-image-preview');

    // Check if API key is set
    if (empty(GEMINI_API_KEY)) {
        debug_log("ERROR: API key is empty");
        http_response_code(500);
        echo json_encode(['error' => 'API key is not configured.']);
        exit;
    }

    $prompt = $_POST['prompt'];
    debug_log("Prompt received: " . $prompt);
    debug_log("Using model: " . GEMINI_MODEL);

    // Fetch the reference image from your server
    $reference_jersey_url = "https://jerseydesigner.hockeytron.com/reference_images/3d-Mesh-to-2D-for-AI-Texture.png";
    debug_log("Fetching reference image from: " . $reference_jersey_url);
    
    $reference_image_data = @file_get_contents($reference_jersey_url);
    
    if ($reference_image_data === false) {
        debug_log("ERROR: Failed to fetch reference image. Check URL and server access.");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to load reference jersey template. Check external URL access.']);
        exit;
    }
    
    $reference_image_base64 = base64_encode($reference_image_data);
    debug_log("Reference image loaded, base64 length: " . strlen($reference_image_base64));

    // --- USING THE CREATIVE PROMPT REPLACEMENT (Recommended for solving 500 error & boosting creativity) ---
    // The previous highly restrictive prompt sometimes causes internal API errors.
    $text_prompt = "Generate a high-resolution, photorealistic custom sublimated hockey jersey design. Apply the following design elements directly onto the jersey surface, using the provided image as an EXACT TEMPLATE for the jersey's pose, angle, and dimensions.
    
    Design to apply: " . $prompt . "
    
    CRITICAL REQUIREMENTS:
    1. TEMPLATE ADHERENCE: The jersey must strictly follow the EXACT pose, angle, and position of the reference image that is a perfectly straight-on, front-facing view with no tilt or rotation.
    2. INTEGRATED DESIGN: The design should appear fully integrated into the fabric, showcasing creative graphics and patterns as if part of a high-quality sublimation print.
    3. TRANSPARENT BACKGROUND: The final output must have a completely transparent background.
    
    The overall image should be vibrant and dynamic, ready for immediate use, maintaining the exact forward-facing pose of the reference.";

    // The old, restrictive prompt has been removed to increase the chance of success.


    debug_log("Text prompt created for sublimation-ready template mapping");

    // Prepare the API request with both reference image and text
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
                // Text prompt second
                [
                    'text' => $prompt
                ]
            ]
        ]
    ];

    // The correct API endpoint for Gemini 2.5 Flash Image Preview
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent';
    debug_log("API URL: " . $apiUrl);

    // Build the payload with the multimodal contents
    $payload = json_encode([
        'contents' => $contents
    ]);

    debug_log("Payload structure prepared (length: " . strlen($payload) . " bytes)");

    // Initialize cURL session
    $ch = curl_init();

    // Call API with backoff/retry logic
    $api_response = call_api_with_retry($ch, $apiUrl, $payload);
    
    $response = $api_response['response'];
    $httpcode = $api_response['httpcode'];
    $error = $api_response['error'];

    debug_log("HTTP Code: " . $httpcode);
    debug_log("cURL Error: " . ($error ?: 'None'));
    debug_log("Response length: " . strlen($response));
    debug_log("Response preview: " . substr($response, 0, 500));

    curl_close($ch);

    if ($error) {
        debug_log("Fatal cURL error occurred: " . $error);
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
        debug_log("Failed to decode JSON response. Raw response: " . $response);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to decode API response. Check debug log for raw response.']);
        exit;
    }

    // Check if we got a valid response
    if (!isset($result['candidates'][0]['content']['parts'])) {
        debug_log("Unexpected response structure");
        debug_log("Full response: " . json_encode($result));
        http_response_code(500);
        echo json_encode(['error' => 'Unexpected API response structure (Missing candidates/content/parts).']);
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
        debug_log("Response parts: " . json_encode($result['candidates'][0]['content']['parts']));
        http_response_code(500);
        echo json_encode(['error' => 'API did not return an image.']);
        exit;
    }

    $decodedImage = base64_decode($imageData);

    if ($decodedImage === false) {
        debug_log("Failed to decode base64 image");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to decode image data.']);
        exit;
    }

    // Ensure the output directory exists
    if (!is_dir('generated_images') && !mkdir('generated_images', 0775, true)) {
        debug_log("Failed to create generated_images directory");
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create image directory on server.']);
        exit;
    }

    // Save the image to a file
    $fileName = 'jersey_' . uniqid() . '.png';
    $filePath = 'generated_images/' . $fileName;
    
    if (file_put_contents($filePath, $decodedImage) === false) {
        debug_log("Failed to save image to: " . $filePath);
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save image to disk.']);
        exit;
    }

    debug_log("SUCCESS: Image saved to " . $filePath);
    echo json_encode(['imageUrl' => $filePath]);

} catch (Exception $e) {
    debug_log("Exception occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server Exception: ' . $e->getMessage()]);
} catch (Error $e) {
    debug_log("Fatal error occurred: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Fatal Server Error: ' . $e->getMessage()]);
}

debug_log("=== End generate.php debug ===");
