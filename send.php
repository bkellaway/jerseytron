<?php
// Get parameters from URL
$imageUrl = isset($_GET['image']) ? $_GET['image'] : '';
$prompt = isset($_GET['prompt']) ? $_GET['prompt'] : '';

// Validate that we have the required parameters
if (empty($imageUrl) || empty($prompt)) {
    die('Error: Missing required parameters. Please go back and generate a design first.');
}

// Check if the image file exists
$imagePath = __DIR__ . '/' . $imageUrl;
if (!file_exists($imagePath)) {
    die('Error: Image file not found. Please generate a new design.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Quote - HockeyTron.com</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 40px;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        header h1 {
            color: #1a324f;
            margin: 0 0 10px 0;
            font-size: 1.8em;
        }
        header p {
            color: #666;
            margin: 0;
            font-size: 1.1em;
        }
        .jersey-preview {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .jersey-preview img {
            max-width: 100%;
            width: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group label .required {
            color: #dc3545;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            font-family: inherit;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9em;
            margin-top: 5px;
            display: none;
        }
        .form-group.error input,
        .form-group.error textarea {
            border-color: #dc3545;
        }
        .form-group.error .error-message {
            display: block;
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .submit-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .success-message {
            display: none;
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .success-message.show {
            display: block;
        }
        .error-alert {
            display: none;
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .error-alert.show {
            display: block;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Request a Custom Jersey Quote</h1>
            <p>Please send me a custom jersey quote from HockeyTron.com.</p>
        </header>

        <div class="jersey-preview">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Your Jersey Design">
        </div>

        <div class="success-message" id="success-message">
            <strong>Success!</strong> Your email has been sent to HockeyTron.com. A member of HockeyTron.com will be in contact with you soon.
        </div>

        <div class="error-alert" id="error-alert"></div>

        <form id="quote-form">
            <input type="hidden" name="imageUrl" value="<?php echo htmlspecialchars($imageUrl); ?>">
            <input type="hidden" name="prompt" value="<?php echo htmlspecialchars($prompt); ?>">

            <div class="form-group">
                <label for="name">Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" required>
                <div class="error-message">Please enter your name</div>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
                <div class="error-message">Please enter a valid email address</div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" placeholder="(555) 123-4567" required>
                <div class="error-message">Please enter a valid US phone number (minimum 10 digits)</div>
            </div>

            <div class="form-group">
                <label for="message">Additional Details <span class="required">*</span></label>
                <textarea id="message" name="message" placeholder="Tell us about your jersey needs (quantity, sizes, timeline, etc.)" required></textarea>
                <div class="error-message">Please provide some details about your jersey needs</div>
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">Send Quote Request</button>
        </form>

        <div class="back-link">
            <a href="index.php">? Back to Designer</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('quote-form');
        const submitBtn = document.getElementById('submit-btn');
        const successMessage = document.getElementById('success-message');
        const errorAlert = document.getElementById('error-alert');

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.substring(0, 10);
            }
            if (value.length > 6) {
                e.target.value = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
            } else if (value.length > 3) {
                e.target.value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
            } else if (value.length > 0) {
                e.target.value = `(${value}`;
            }
        });

        // Form validation
        function validateForm() {
            let isValid = true;

            // Clear all errors
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('error');
            });

            // Validate name
            const name = document.getElementById('name').value.trim();
            if (name === '') {
                document.getElementById('name').closest('.form-group').classList.add('error');
                isValid = false;
            }

            // Validate email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('email').closest('.form-group').classList.add('error');
                isValid = false;
            }

            // Validate phone (US format, minimum 10 digits)
            const phone = document.getElementById('phone').value.replace(/\D/g, '');
            if (phone.length < 10) {
                document.getElementById('phone').closest('.form-group').classList.add('error');
                isValid = false;
            }

            // Validate message
            const message = document.getElementById('message').value.trim();
            if (message === '') {
                document.getElementById('message').closest('.form-group').classList.add('error');
                isValid = false;
            }

            return isValid;
        }

        // Form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Hide previous messages
            successMessage.classList.remove('show');
            errorAlert.classList.remove('show');

            // Validate form
            if (!validateForm()) {
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            try {
                const formData = new FormData(form);
                const response = await fetch('handle_send.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'An error occurred while sending your request.');
                }

                // Show success message
                successMessage.classList.add('show');
                
                // Hide form
                form.style.display = 'none';

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } catch (error) {
                errorAlert.textContent = error.message;
                errorAlert.classList.add('show');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Quote Request';
            }
        });
    </script>
</body>
</html>