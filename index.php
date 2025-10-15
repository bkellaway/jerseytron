<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hockeytron AI Hockey Jersey Designer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 800px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            height: 90vh;
        }
        header {
            background-color: #1a324f;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .chat-window {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            padding: 10px 15px;
            border-radius: 12px;
            max-width: 80%;
            line-height: 1.4;
        }
        .user-prompt {
            background-color: #e1f5fe;
            align-self: flex-end;
        }
        .ai-response {
            background-color: #f1f1f1;
            align-self: flex-start;
        }
        .ai-response img {
            max-width: 100%;
            width: 300px;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
        }
        .input-form {
            padding: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
        }
        .input-form textarea {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: none;
            font-size: 1em;
            height: 50px;
        }
        .input-form button {
            padding: 0 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .generate-btn {
            background-color: #007bff;
            color: white;
        }
        .generate-btn:hover {
            background-color: #0056b3;
        }
        .save-btn {
            background-color: #28a745;
            color: white;
            display: none;
        }
        .save-btn:hover {
            background-color: #218838;
        }
        .loader {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>HockeyTron AI Hockey Jersey Designer</h1>
        </header>
        <div class="chat-window" id="chat-window">
            <div class="message ai-response">
                Hello! Describe the hockey jersey you want to create. For example: "A black jersey with fiery orange and white stripes on the sleeves and a roaring panther logo on the chest."
            </div>
        </div>
        <div id="loader" class="loader" style="display: none;">
            <div class="spinner"></div>
            <p>Generating your design... this can take up to 30 seconds.</p>
        </div>
        <form id="design-form" class="input-form">
            <textarea id="prompt-input" placeholder="Enter your design ideas..." required></textarea>
            <button type="submit" class="generate-btn">Generate</button>
            <button type="button" id="save-btn" class="save-btn">Save</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('design-form');
        const input = document.getElementById('prompt-input');
        const chatWindow = document.getElementById('chat-window');
        const loader = document.getElementById('loader');
        const saveBtn = document.getElementById('save-btn');
        let lastImageUrl = null;
        let lastPrompt = null;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const promptText = input.value.trim();
            if (!promptText) return;

            // Add user prompt to chat
            appendMessage(promptText, 'user-prompt');
            input.value = '';
            input.disabled = true;
            form.querySelector('.generate-btn').disabled = true;
            loader.style.display = 'block';
            saveBtn.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('prompt', promptText);
                const response = await fetch('generate.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.error || 'An unknown error occurred.');
                }
                // Add AI image to chat
                lastImageUrl = result.imageUrl;
                lastPrompt = promptText;
                appendImage(result.imageUrl, 'ai-response');
                saveBtn.style.display = 'block';
            } catch (error) {
                appendMessage(`Error: ${error.message}`, 'ai-response');
            } finally {
                loader.style.display = 'none';
                input.disabled = false;
                form.querySelector('.generate-btn').disabled = false;
            }
        });

        saveBtn.addEventListener('click', () => {
            if (!lastImageUrl || !lastPrompt) {
                alert('No image to save!');
                return;
            }
            // Redirect to send.php with URL parameters
            window.location.href = 'send.php?image=' + encodeURIComponent(lastImageUrl) + '&prompt=' + encodeURIComponent(lastPrompt);
        });

        function appendMessage(text, className) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${className}`;
            messageDiv.textContent = text;
            chatWindow.appendChild(messageDiv);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        function appendImage(url, className) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${className}`;

            const img = document.createElement('img');
            img.src = url;
            messageDiv.appendChild(img);

            const clarification = document.createElement('p');
            clarification.textContent = "What do you think? You can refine the design by describing what you want to change.";
            messageDiv.appendChild(clarification);

            chatWindow.appendChild(messageDiv);
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    </script>
</body>
</html>