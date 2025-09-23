  <?php
/**
 * Test Google Generative Language API (Gemini 2.0 Flash) via PHP cURL
 */

$apiKey   = getenv('GOOGLE_API_KEY') ?: 'AIzaSyC5YfRnmYtUfGWGZhEJ1K9eFOtIWY_kM0k';
$endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Explain how AI works in a few words"]
            ]
        ]
    ]
];

$ch = curl_init($endpoint);

curl_setopt_array($ch, [
    CURLOPT_POST            => true,
    CURLOPT_HTTPHEADER      => [
        'Content-Type: application/json',
        'X-goog-api-key: ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS      => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_TIMEOUT         => 30,
    CURLOPT_SSL_VERIFYPEER  => true,
]);

$response = curl_exec($ch);

if ($response === false) {
    $err = curl_error($ch);
    $code = curl_errno($ch);
    curl_close($ch);
    http_response_code(500);
    die("cURL error ($code): $err\n");
}

$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpStatus < 200 || $httpStatus >= 300) {
    // Print nicely if the API returns an error JSON
    echo "HTTP $httpStatus\n";
    echo $response . "\n";
    exit(1);
}

// Pretty-print JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Fallback to raw output if it wasn't valid JSON
    echo $response . "\n";
    exit;
}

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
