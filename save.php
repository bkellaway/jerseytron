 <?php
header('Content-Type: application/json');

require 'vendor/autoload.php';
require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_POST['imageUrl']) || empty($_POST['imageUrl'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Image URL is missing.']);
    exit;
}

$imageUrl = $_POST['imageUrl'];
$prompt = isset($_POST['prompt']) ? $_POST['prompt'] : 'N/A';
$imagePath = __DIR__ . '/' . $imageUrl;

if (!file_exists($imagePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Image file not found on server.']);
    exit;
}

$mail = new PHPMailer(true);

try {
    //Server settings
    if (SMTP_ENABLED) {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
    }

    //Recipients
    $mail->setFrom(SMTP_USERNAME, 'Jersey Design AI');
    $mail->addAddress(RECIPIENT_EMAIL, RECIPIENT_NAME);

    //Attachments
    $mail->addAttachment($imagePath);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'New Hockey Jersey Design Created';
    $mail->Body    = 'A new jersey design has been finalized. Please see the attached image.<br><br><b>Final Prompt:</b><br>' . htmlspecialchars($prompt);
    $mail->AltBody = 'A new jersey design has been finalized. Please see the attached image.\n\nFinal Prompt:\n' . $prompt;

    $mail->send();
    echo json_encode(['success' => 'Email has been sent successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>