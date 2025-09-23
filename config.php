 <?php

// ** Google Gemini API Configuration **
// Get your key from Google AI Studio: https://aistudio.google.com/app/apikey
define('GEMINI_API_KEY', 'AIzaSyC5YfRnmYtUfGWGZhEJ1K9eFOtIWY_kM0k');

// The model name for "Nano Banana" / Gemini 2.5 Flash Image
// Note: This model name might change. Check the Google AI docs for the latest identifier.
define('GEMINI_MODEL', 'gemini-2.5-flash-image-preview'); // Fixed: added -preview


// ** Email Configuration **
// The email address to send the final designs to
define('RECIPIENT_EMAIL', 'bill@billkellaway.com');
define('RECIPIENT_NAME', 'Graphic Design Team');

// ** PHPMailer SMTP Configuration (e.g., using Gmail) **
// Set to true to enable SMTP
define('SMTP_ENABLED', true);
// Your SMTP host
define('SMTP_HOST', 'smtp.gmail.com');
// Your SMTP username
define('SMTP_USERNAME', 'your-email@gmail.com');
// Your SMTP password or App Password
define('SMTP_PASSWORD', 'your-gmail-app-password');
// SMTP port (587 for TLS, 465 for SSL)
define('SMTP_PORT', 587);
// SMTP encryption (tls or ssl)
define('SMTP_SECURE', 'tls');

?>