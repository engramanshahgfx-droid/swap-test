<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CrewSwap API Testing</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 5px;
        }

        .status {
            font-size: 14px;
            color: #666;
        }

        .status.success {
            color: #10b981;
        }

        .status.error {
            color: #ef4444;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            width: 100%;
            transition: background 0.3s;
        }

        button:hover {
            background: #5568d3;
        }

        button.danger {
            background: #ef4444;
        }

        button.danger:hover {
            background: #dc2626;
        }

        .response {
            background: #f3f4f6;
            border-left: 4px solid #667eea;
            padding: 12px;
            border-radius: 4px;
            margin-top: 12px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-all;
            color: #333;
        }

        .response.error {
            border-left-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }

        .response.success {
            border-left-color: #10b981;
            background: #f0fdf4;
            color: #065f46;
        }

        .token-display {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 11px;
            word-break: break-all;
            color: #92400e;
        }

        .success-message {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 12px;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .button-group button {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🚀 CrewSwap API Testing</h1>
            <p class="status" id="apiStatus">Testing API connection...</p>
        </header>

        <div class="grid">
            <!-- REGISTRATION -->
            <div class="card">
                <h2>📝 Register</h2>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="registerEmail" placeholder="test@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="registerPassword" placeholder="password">
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="registerName" placeholder="John Doe">
                </div>
                <button onclick="register()">Register</button>
                <div id="registerResponse"></div>
            </div>

            <!-- LOGIN -->
            <div class="card">
                <h2>🔑 Login</h2>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="loginEmail" placeholder="test@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="loginPassword" placeholder="password">
                </div>
                <button onclick="login()">Login</button>
                <div id="loginResponse"></div>
            </div>

            <!-- DEVICE TOKEN -->
            <div class="card">
                <h2>📱 Register Device Token</h2>
                <div id="tokenStatus" class="success-message" style="display:none;">
                    ✅ Token stored! You can now receive push notifications.
                </div>
                <div class="form-group">
                    <label>Device Token</label>
                    <textarea id="deviceToken" placeholder="Paste Firebase Cloud Messaging device token here..."></textarea>
                </div>
                <button onclick="storeDeviceToken()">Store Token</button>
                <div id="deviceTokenResponse"></div>
            </div>

            <!-- USER PROFILE -->
            <div class="card">
                <h2>👤 User Profile</h2>
                <button onclick="getUserProfile()">Get Profile</button>
                <button onclick="logout()" class="danger" style="margin-top: 10px;">Logout</button>
                <div id="userProfileResponse"></div>
            </div>

            <!-- GET LANGUAGES -->
            <div class="card">
                <h2>🌐 Languages</h2>
                <button onclick="getLanguages()">Get Supported Languages</button>
                <div id="languagesResponse"></div>
            </div>

            <!-- CUSTOM API CALL -->
            <div class="card">
                <h2>⚙️ Custom API Call</h2>
                <div class="form-group">
                    <label>Endpoint (e.g., /api/user)</label>
                    <input type="text" id="customEndpoint" placeholder="/api/user">
                </div>
                <div class="form-group">
                    <label>Method</label>
                    <select id="customMethod">
                        <option>GET</option>
                        <option>POST</option>
                        <option>PUT</option>
                        <option>DELETE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Body (JSON)</label>
                    <textarea id="customBody" placeholder='{"key": "value"}'></textarea>
                </div>
                <button onclick="customAPICall()">Send Request</button>
                <div id="customResponse"></div>
            </div>
        </div>
    </div>

    <script>
        let authToken = localStorage.getItem('authToken');

        // Check API status on load
        window.onload = function() {
            checkAPIStatus();
        };

        function checkAPIStatus() {
            fetch('/api/languages')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('apiStatus').textContent = '✅ API is online';
                    document.getElementById('apiStatus').classList.add('success');
                })
                .catch(e => {
                    document.getElementById('apiStatus').textContent = '❌ API is offline';
                    document.getElementById('apiStatus').classList.add('error');
                });
        }

        function register() {
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const name = document.getElementById('registerName').value;

            if (!email || !password || !name) {
                showResponse('registerResponse', 'All fields required', 'error');
                return;
            }

            fetch('/api/simple-register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password, full_name: name })
            })
            .then(r => r.json())
            .then(data => {
                if (data.data && data.data.token) {
                    authToken = data.data.token;
                    localStorage.setItem('authToken', authToken);
                    showResponse('registerResponse', JSON.stringify(data, null, 2), 'success');
                    document.getElementById('registerEmail').value = '';
                    document.getElementById('registerPassword').value = '';
                    document.getElementById('registerName').value = '';
                } else {
                    showResponse('registerResponse', JSON.stringify(data, null, 2), 'error');
                }
            })
            .catch(e => showResponse('registerResponse', 'Error: ' + e.message, 'error'));
        }

        function login() {
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            if (!email || !password) {
                showResponse('loginResponse', 'Email and password required', 'error');
                return;
            }

            fetch('/api/simple-login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
            .then(r => r.json())
            .then(data => {
                if (data.data && data.data.token) {
                    authToken = data.data.token;
                    localStorage.setItem('authToken', authToken);
                    showResponse('loginResponse', JSON.stringify(data, null, 2), 'success');
                    document.getElementById('loginEmail').value = '';
                    document.getElementById('loginPassword').value = '';
                } else {
                    showResponse('loginResponse', JSON.stringify(data, null, 2), 'error');
                }
            })
            .catch(e => showResponse('loginResponse', 'Error: ' + e.message, 'error'));
        }

        function storeDeviceToken() {
            if (!authToken) {
                showResponse('deviceTokenResponse', 'Please login first', 'error');
                return;
            }

            const deviceToken = document.getElementById('deviceToken').value;
            if (!deviceToken) {
                showResponse('deviceTokenResponse', 'Device token required', 'error');
                return;
            }

            fetch('/api/user/device-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + authToken
                },
                body: JSON.stringify({ device_token: deviceToken })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('tokenStatus').style.display = 'block';
                showResponse('deviceTokenResponse', JSON.stringify(data, null, 2), 'success');
                document.getElementById('deviceToken').value = '';
            })
            .catch(e => showResponse('deviceTokenResponse', 'Error: ' + e.message, 'error'));
        }

        function getUserProfile() {
            if (!authToken) {
                showResponse('userProfileResponse', 'Please login first', 'error');
                return;
            }

            fetch('/api/user', {
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(r => r.json())
            .then(data => showResponse('userProfileResponse', JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('userProfileResponse', 'Error: ' + e.message, 'error'));
        }

        function logout() {
            if (!authToken) {
                showResponse('userProfileResponse', 'Not logged in', 'error');
                return;
            }

            fetch('/api/logout', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(r => r.json())
            .then(data => {
                authToken = null;
                localStorage.removeItem('authToken');
                document.getElementById('tokenStatus').style.display = 'none';
                showResponse('userProfileResponse', 'Logged out successfully', 'success');
            })
            .catch(e => showResponse('userProfileResponse', 'Error: ' + e.message, 'error'));
        }

        function getLanguages() {
            fetch('/api/languages')
            .then(r => r.json())
            .then(data => showResponse('languagesResponse', JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('languagesResponse', 'Error: ' + e.message, 'error'));
        }

        function customAPICall() {
            if (!authToken) {
                showResponse('customResponse', 'Please login first (for most endpoints)', 'error');
                return;
            }

            const endpoint = document.getElementById('customEndpoint').value;
            const method = document.getElementById('customMethod').value;
            const bodyText = document.getElementById('customBody').value;

            if (!endpoint) {
                showResponse('customResponse', 'Endpoint required', 'error');
                return;
            }

            const options = {
                method: method,
                headers: { 'Authorization': 'Bearer ' + authToken }
            };

            if (method !== 'GET' && bodyText) {
                options.headers['Content-Type'] = 'application/json';
                options.body = bodyText;
            }

            fetch('/api' + (endpoint.startsWith('/') ? endpoint : '/' + endpoint), options)
            .then(r => r.json())
            .then(data => showResponse('customResponse', JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('customResponse', 'Error: ' + e.message, 'error'));
        }

        function showResponse(elementId, message, type) {
            const el = document.getElementById(elementId);
            el.innerHTML = `<div class="response ${type}">${message}</div>`;
        }
    </script>
</body>
</html>
