 <?php

// ** Google Gemini API Configuration **
// Get your key from Google AI Studio: https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', '');

// The model name for "Nano Banana" / Gemini 2.5 Flash Image
// Note: This model name might change. Check the Google AI docs for the latest identifier.
define('GEMINI_MODEL', 'gemini-2.5-flash-image-preview'); // Fixed: added -preview


// Background Removal Configuration
define('REMOVEBG_API_KEY', '');
define('ENABLE_BG_REMOVAL', true);

// ** Email Configuration **
// The email address to send the final designs to
define('RECIPIENT_EMAIL', 'joe@hockeywest.com');
define('RECIPIENT_NAME', 'HockeyTron Jersery Graphic Design Team');
define('BCC_EMAILS', 'bill@billkellaway.com,chrism@hockeytron.com'); // Change this to your BCC address

// ** PHPMailer SMTP Configuration (e.g., using Gmail) **
// Set to true to enable SMTP
define('SMTP_ENABLED', true);
// Your SMTP host
define('SMTP_HOST', 'smtp.gmail.com');
// Your SMTP username
define('SMTP_USERNAME', 'billkellaway@gmail.com');
// Your SMTP password or App Password
define('SMTP_PASSWORD', '');
// SMTP port (587 for TLS, 465 for SSL)
define('SMTP_PORT', 587);
// SMTP encryption (tls or ssl)
define('SMTP_SECURE', 'tls');


?>

