<?php
// Debug version of save.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't output errors to browser

// Check if vendor/autoload.php exists first
if (!file_exists('vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'PHPMailer not installed. Run "composer install" first.']);
    exit;
}

require 'vendor/autoload.php';
require 'config.php';

// Use statements must be at the top
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Log function
function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . ' - SAVE DEBUG: ' . $message . "\n", 3, 'save_debug.log');
}

debug_log("=== Starting save.php debug ===");

try {
    debug_log("Config loaded successfully");
    debug_log("SMTP_ENABLED: " . (SMTP_ENABLED ? 'true' : 'false'));
    debug_log("SMTP_USERNAME: " . SMTP_USERNAME);
    debug_log("RECIPIENT_EMAIL: " . RECIPIENT_EMAIL);

    // Validate input
    if (!isset($_POST['imageUrl']) || empty($_POST['imageUrl'])) {
        debug_log("ERROR: imageUrl missing from POST");
        http_response_code(400);
        echo json_encode(['error' => 'Image URL is missing.']);
        exit;
    }

    $imageUrl = $_POST['imageUrl'];
    $prompt = isset($_POST['prompt']) ? $_POST['prompt'] : 'N/A';
    
    debug_log("Image URL: " . $imageUrl);
    debug_log("Prompt: " . $prompt);

    $imagePath = __DIR__ . '/' . $imageUrl;
    debug_log("Full image path: " . $imagePath);

    if (!file_exists($imagePath)) {
        debug_log("ERROR: Image file not found at " . $imagePath);
        http_response_code(404);
        echo json_encode(['error' => 'Image file not found on server.']);
        exit;
    }

    debug_log("Image file exists, size: " . filesize($imagePath) . " bytes");

    // Check SMTP configuration
    if (SMTP_ENABLED) {
        if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-gmail-app-password') {
            debug_log("WARNING: SMTP credentials not configured");
            echo json_encode(['error' => 'Email configuration incomplete. Please update SMTP settings in config.php']);
            exit;
        }
    }

    $mail = new PHPMailer(true);
    debug_log("PHPMailer instance created");

    // Server settings
    if (SMTP_ENABLED) {
        debug_log("Configuring SMTP settings");
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        debug_log("SMTP configured");
    } else {
        debug_log("Using PHP mail() function");
    }

    // Recipients
    debug_log("Setting email recipients");
    $mail->setFrom(SMTP_ENABLED ? SMTP_USERNAME : 'noreply@' . $_SERVER['HTTP_HOST'], 'Jersey Design AI');
    $mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);

    // Attachments
    debug_log("Adding attachment: " . $imagePath);
    $mail->addAttachment($imagePath);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Hockey Jersey Design Created';
    $mail->Body    = 'A new jersey design has been finalized. Please see the attached image.<br><br><b>Final Prompt:</b><br>' . htmlspecialchars($prompt);
    $mail->AltBody = 'A new jersey design has been finalized. Please see the attached image.\n\nFinal Prompt:\n' . $prompt;

    debug_log("About to send email...");
    
    // For debugging, let's not actually send the email yet
    // Instead, let's return success to see if the issue is email-related
    debug_log("DEBUG MODE: Skipping actual email send");
    echo json_encode(['success' => 'Email would be sent successfully! (Debug mode - email not actually sent)']);

    // Uncomment the line below when ready to actually send emails:
    // $mail->send();
    // echo json_encode(['success' => 'Email has been sent successfully!']);

} catch (Exception $e) {
    debug_log("Exception: " . $e->getMessage());
    debug_log("Exception trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => "Message could not be sent. Error: {$e->getMessage()}"]);
} catch (Error $e) {
    debug_log("Fatal Error: " . $e->getMessage());
    debug_log("Error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => "Fatal error occurred: {$e->getMessage()}"]);
}

debug_log("=== End save.php debug ===");
?>