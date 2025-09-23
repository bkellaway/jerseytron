# AI Hockey Jersey Designer

This is a PHP-based web application that provides a user interface for creating creative hockey jersey designs using the **Google Gemini API** (specifically, the model nicknamed "Nano Banana").

## Features

-   **Conversational Interface:** Describe the jersey you want, see the result, and refine your description.
-   **AI-Powered Image Generation:** Leverages Google's powerful Gemini model to create high-quality images from text.
-   **One-Click Save:** When you're happy with a design, click "Save" to have the final image and prompt emailed to a pre-configured address.

## Setup Instructions

### 1. Prerequisites

-   PHP 8.0 or newer.
-   [Composer](https://getcomposer.org/) for PHP dependency management.
-   An SMTP server for sending emails (e.g., a Gmail account with an App Password).

### 2. Get your Google Gemini API Key

1.  Go to **Google AI Studio**: [https://aistudio.google.com/app/apikey](https://aistudio.google.com/app/apikey)
2.  Sign in with your Google account.
3.  Click the "**Create API key**" button.
4.  Copy the generated key. This is the key you will use in the configuration step.

**Note:** Unlike the previous version, you **do not** need a full Google Cloud project or a service account JSON file. The API key is all you need for authentication.

### 3. Installation

1.  Clone this repository or download the files to your web server.
2.  Install the PHP dependencies using Composer. Open your terminal in the project's root directory and run:
    ```bash
    composer install
    ```
    This will download PHPMailer and set up the necessary autoloader.
3.  Make sure the `generated_images/` directory is writable by your web server. You may need to set its permissions:
    ```bash
    chmod 775 generated_images
    ```

### 4. Configuration

Open the `config.php` file and fill in all the required constants:

-   `GEMINI_API_KEY`: Paste the API key you generated from Google AI Studio here.
-   `RECIPIENT_EMAIL`: The email address where the final designs will be sent.
-   `RECIPIENT_NAME`: The name of the recipient.
-   **SMTP Settings**: Fill in your SMTP server details. If using a personal Gmail account, you will need to generate an **"App Password"** for the `SMTP_PASSWORD` field from your Google Account security settings. Using your regular Gmail password will not work.

### 5. Running the Application

You can use PHP's built-in web server for local testing. From the project's root directory, run:

```bash
php -S localhost:8000