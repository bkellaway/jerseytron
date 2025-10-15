<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't output errors to browser

// Check if vendor/autoload.php exists
if (!file_exists('vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'PHPMailer not installed. Run "composer install" first.']);
    exit;
}

require 'vendor/autoload.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Log function for debugging
function debug_log($message) {
    error_log(date('Y-m-d H:i:s') . ' - HANDLE_SEND: ' . $message . "\n", 3, 'handle_send_debug.log');
}

debug_log("=== Starting handle_send.php ===");

try {
    // Validate required fields
    $requiredFields = ['name', 'email', 'phone', 'message', 'imageUrl', 'prompt'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            debug_log("ERROR: Missing required field: " . $field);
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: " . $field]);
            exit;
        }
    }

    // Sanitize and validate inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);
    $imageUrl = trim($_POST['imageUrl']);
    $prompt = trim($_POST['prompt']);

    debug_log("Customer Name: " . $name);
    debug_log("Customer Email: " . $email);
    debug_log("Customer Phone: " . $phone);
    debug_log("Image URL: " . $imageUrl);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        debug_log("ERROR: Invalid email format");
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address format.']);
        exit;
    }

    // Validate phone number (US format, minimum 10 digits)
    $phoneDigits = preg_replace('/\D/', '', $phone);
    if (strlen($phoneDigits) < 10) {
        debug_log("ERROR: Invalid phone number - too short");
        http_response_code(400);
        echo json_encode(['error' => 'Phone number must contain at least 10 digits.']);
        exit;
    }

    // Check if image file exists
    $imagePath = __DIR__ . '/' . $imageUrl;
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
            debug_log("ERROR: SMTP credentials not configured");
            http_response_code(500);
            echo json_encode(['error' => 'Email configuration incomplete. Please contact the administrator.']);
            exit;
        }
    }

    // Create PHPMailer instance
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
        debug_log("SMTP configured: " . SMTP_HOST . ":" . SMTP_PORT);
    } else {
        debug_log("Using PHP mail() function");
    }

	// Recipients
	$mail->setFrom(SMTP_ENABLED ? SMTP_USERNAME : 'noreply@' . $_SERVER['HTTP_HOST'], 'JerseyTron Quote System');
	$mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);
	$mail->addReplyTo($email, $name);

	// Add multiple BCCs
	$bccAddresses = explode(',', BCC_EMAILS);
	foreach ($bccAddresses as $bccEmail) {
		$bccEmail = trim($bccEmail);
		if (!empty($bccEmail)) {
			$mail->addBCC($bccEmail);
			debug_log("BCC added: " . $bccEmail);
		}
	}

    debug_log("Email recipients configured");
    debug_log("From: " . (SMTP_ENABLED ? SMTP_USERNAME : 'noreply@' . $_SERVER['HTTP_HOST']));
    debug_log("To: " . RECIPIENT_EMAIL);
    debug_log("Reply-To: " . $email);

    // Attachments
    debug_log("Adding attachment: " . $imagePath);
    $mail->addAttachment($imagePath);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Jersey Quote Request from ' . $name;
    
    $htmlBody = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a324f; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .field-label { font-weight: bold; color: #1a324f; }
            .field-value { margin-top: 5px; }
            .prompt-box { background-color: #e1f5fe; padding: 15px; border-radius: 5px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>New Jersey Quote Request</h1>
            </div>
            <div class="content">
                <h2>Customer Information</h2>
                
                <div class="field">
                    <div class="field-label">Name:</div>
                    <div class="field-value">' . htmlspecialchars($name) . '</div>
                </div>
                
                <div class="field">
                    <div class="field-label">Email:</div>
                    <div class="field-value"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></div>
                </div>
                
                <div class="field">
                    <div class="field-label">Phone:</div>
                    <div class="field-value">' . htmlspecialchars($phone) . '</div>
                </div>
                
                <div class="field">
                    <div class="field-label">Additional Details:</div>
                    <div class="field-value">' . nl2br(htmlspecialchars($message)) . '</div>
                </div>
                
                <div class="prompt-box">
                    <div class="field-label">Original Design Prompt:</div>
                    <div class="field-value">' . htmlspecialchars($prompt) . '</div>
                </div>
                
                <p style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ccc; color: #666; font-size: 0.9em;">
                    The jersey design image is attached to this email. Please respond to the customer directly at the email address provided above.
                </p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $plainBody = "HOCKEYTRON JERSEY QUOTE REQUEST\n\n";
    $plainBody .= "CUSTOMER INFORMATION\n";
    $plainBody .= "Name: " . $name . "\n";
    $plainBody .= "Email: " . $email . "\n";
    $plainBody .= "Phone: " . $phone . "\n\n";
    $plainBody .= "ADDITIONAL DETAILS:\n" . $message . "\n\n";
    $plainBody .= "ORIGINAL DESIGN PROMPT:\n" . $prompt . "\n\n";
    $plainBody .= "The jersey design image is attached to this email.\n";
    $plainBody .= "Please respond to the customer directly at the email address provided above.";

    $mail->Body    = $htmlBody;
    $mail->AltBody = $plainBody;

    debug_log("Email content prepared");
    debug_log("Subject: " . $mail->Subject);

    // Send email
    debug_log("Attempting to send email...");
    $mail->send();
    debug_log("Email sent successfully!");

    echo json_encode(['success' => 'Quote request sent successfully!']);

} catch (Exception $e) {
    debug_log("PHPMailer Exception: " . $e->getMessage());
    debug_log("Exception trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => "Failed to send email. Please try again or contact support. Error: {$e->getMessage()}"]);
} catch (Error $e) {
    debug_log("Fatal Error: " . $e->getMessage());
    debug_log("Error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => "A system error occurred. Please contact support."]);
}

debug_log("=== End handle_send.php ===");
?>