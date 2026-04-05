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
                    <label>Employee ID (optional)</label>
                    <input type="text" id="registerEmployeeId" placeholder="e.g. AD123456">
                </div>
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

            <!-- OTP VERIFICATION -->
            <div class="card">
                <h2>✅ Verify OTP</h2>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">📧 OTP sent to registered email (Check spam if not found)</p>
                <div class="form-group">
                    <label>User ID (numeric id from registration response)</label>
                    <input type="text" id="otpUserId" placeholder="Example: 15 (not employee_id)">
                </div>
                <div class="form-group">
                    <label>OTP Code</label>
                    <input type="text" id="otpCode" placeholder="6-digit code">
                </div>
                <button onclick="verifyOtp()">Verify OTP</button>
                <div id="otpResponse"></div>
            </div>

            <!-- REPORT USER -->
            <div class="card">
                <h2>🚩 Report User</h2>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Tests POST /api/report-user used by mobile app</p>
                <div class="form-group">
                    <label>Reported User Identifier</label>
                    <input type="text" id="reportUserId" placeholder="Example: 15 or AD123456">
                    <small style="color: #666; font-size: 12px;">You can use numeric user id or employee_id. Request will send numeric reported_user_id.</small>
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <input type="text" id="reportReason" placeholder="spam" value="spam">
                </div>
                <div class="form-group">
                    <label>Details</label>
                    <textarea id="reportDetails" placeholder="User sent inappropriate messages">User sent inappropriate messages</textarea>
                </div>
                <button onclick="reportUser()">Send Report</button>
                <button onclick="getMyReports()" style="margin-top: 10px;">Get My Reports</button>
                <div id="reportResponse" style="margin-top: 10px;"></div>
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
                <h2>👤 User Profile & Data</h2>
                <button onclick="getUserProfile()">Get Full Profile</button>
                <button onclick="logout()" class="danger" style="margin-top: 10px;">Logout</button>
                <div id="userProfileResponse"></div>
            </div>

            <!-- TEST USERS LIST -->
            <div class="card">
                <h2>👥 All Test Users</h2>
                <button onclick="getAllUsers()">Load All Users</button>
                <div id="usersListResponse" style="margin-top: 15px;"></div>
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
                    <label>Endpoint (e.g., /user or /report-user)</label>
                    <input type="text" id="customEndpoint" placeholder="/user">
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

        function parseApiResponse(response) {
            return response.text().then(text => {
                const hasBody = text && text.trim().length > 0;
                let data = null;

                if (hasBody) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        data = null;
                    }
                }

                if (!response.ok) {
                    const jsonMessage = data && data.message ? data.message : null;
                    const fallbackMessage = hasBody
                        ? text.slice(0, 300)
                        : `${response.status} ${response.statusText}`;

                    throw new Error(jsonMessage || fallbackMessage);
                }

                if (!data) {
                    throw new Error('Server returned non-JSON response. Check backend logs for the exact error.');
                }

                return data;
            });
        }

        function apiFetch(url, options = {}) {
            const headers = options.headers || {};

            return fetch(url, {
                ...options,
                headers: {
                    'Accept': 'application/json',
                    ...headers,
                }
            }).then(parseApiResponse);
        }

        // Check API status on load
        window.onload = function() {
            checkAPIStatus();
        };

        function checkAPIStatus() {
            apiFetch('/api/languages')
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
            const employeeId = document.getElementById('registerEmployeeId').value.trim();
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const name = document.getElementById('registerName').value;

            if (!email || !password || !name) {
                showResponse('registerResponse', 'All fields required', 'error');
                return;
            }

            const payload = { email, password, full_name: name };
            if (employeeId) {
                payload.employee_id = employeeId;
            }

            apiFetch('/api/simple-register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(data => {
                if (data.data && data.data.token) {
                    authToken = data.data.token;
                    localStorage.setItem('authToken', authToken);
                    const userId = data.data.user.id;
                    document.getElementById('otpUserId').value = userId;
                    const responseEmployeeId = data.data.user.employee_id || '-';
                    showResponse('registerResponse', '✅ Registration successful!\n\nUser ID: ' + userId + '\nEmployee ID: ' + responseEmployeeId + '\n\n📧 OTP sent to ' + email + '\n\nCheck your email for OTP code or check in spam folder.\n\nResponse:\n' + JSON.stringify(data, null, 2), 'success');
                    document.getElementById('registerEmployeeId').value = '';
                    document.getElementById('registerEmail').value = '';
                    document.getElementById('registerPassword').value = '';
                    document.getElementById('registerName').value = '';
                } else {
                    showResponse('registerResponse', JSON.stringify(data, null, 2), 'error');
                }
            })
            .catch(e => showResponse('registerResponse', 'Error: ' + e.message, 'error'));
        }

        function verifyOtp() {
            const userId = document.getElementById('otpUserId').value;
            const otp = document.getElementById('otpCode').value;

            if (!userId || !otp) {
                showResponse('otpResponse', 'User ID and OTP required', 'error');
                return;
            }

            if (!/^\d+$/.test(userId)) {
                showResponse('otpResponse', 'User ID must be numeric (example: 15). Do not use employee_id like BRANDRAI9450.', 'error');
                return;
            }

            apiFetch('/api/verify-otp', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, otp: otp })
            })
            .then(data => {
                if (data.data && data.data.token) {
                    authToken = data.data.token;
                    localStorage.setItem('authToken', authToken);
                    showResponse('otpResponse', '✅ OTP Verified Successfully!\n\n' + JSON.stringify(data, null, 2), 'success');
                    document.getElementById('otpCode').value = '';
                } else {
                    showResponse('otpResponse', JSON.stringify(data, null, 2), 'error');
                }
            })
            .catch(e => showResponse('otpResponse', 'Error: ' + e.message, 'error'));
        }

        function reportUser() {
            if (!authToken) {
                showResponse('reportResponse', 'Please login first', 'error');
                return;
            }

            const reportUserInput = document.getElementById('reportUserId').value.trim();
            const reason = document.getElementById('reportReason').value;
            const details = document.getElementById('reportDetails').value;

            if (!reportUserInput || !reason) {
                showResponse('reportResponse', 'Reported User Identifier and reason are required', 'error');
                return;
            }

            const sendReport = (resolvedUserId) => apiFetch('/api/report-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + authToken,
                },
                body: JSON.stringify({ reported_user_id: resolvedUserId, reason, details })
            });

            if (/^\d+$/.test(reportUserInput)) {
                sendReport(parseInt(reportUserInput, 10))
                    .then(data => showResponse('reportResponse', '✅ Report sent successfully\n\n' + JSON.stringify(data, null, 2), 'success'))
                    .catch(e => showResponse('reportResponse', 'Error: ' + e.message, 'error'));
                return;
            }

            apiFetch('/api/users?search=' + encodeURIComponent(reportUserInput) + '&per_page=20', {
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(searchResult => {
                const users = searchResult?.data?.items || [];
                const exactMatches = users.filter(u =>
                    (u.employee_id || '').toLowerCase() === reportUserInput.toLowerCase() ||
                    (u.email || '').toLowerCase() === reportUserInput.toLowerCase()
                );

                if (exactMatches.length !== 1) {
                    throw new Error('Identifier not unique. Use numeric user id or exact employee_id/email.');
                }

                return sendReport(exactMatches[0].id);
            })
            .then(data => showResponse('reportResponse', '✅ Report sent successfully\n\n' + JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('reportResponse', 'Error: ' + e.message, 'error'));
        }

        function getMyReports() {
            if (!authToken) {
                showResponse('reportResponse', 'Please login first', 'error');
                return;
            }

            apiFetch('/api/my-reports', {
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(data => showResponse('reportResponse', '✅ My Reports\n\n' + JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('reportResponse', 'Error: ' + e.message, 'error'));
        }

        function login() {
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            if (!email || !password) {
                showResponse('loginResponse', 'Email and password required', 'error');
                return;
            }

            apiFetch('/api/simple-login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
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

            apiFetch('/api/user/device-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + authToken
                },
                body: JSON.stringify({ device_token: deviceToken })
            })
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

            apiFetch('/api/user', {
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(data => {
                if (data.id) {
                    let html = '<div style="background: #f0fdf4; padding: 15px; border-radius: 8px; margin-top: 10px;">';
                    html += '<h3 style="color: #065f46; margin-top: 0;">👤 User Profile Information</h3>';
                    html += '<table style="width: 100%; font-size: 14px;">';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold; width: 40%;">ID:</td><td style="padding: 8px;">' + data.id + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Name:</td><td style="padding: 8px;">' + (data.full_name || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Email:</td><td style="padding: 8px;">' + (data.email || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Phone:</td><td style="padding: 8px;">' + (data.phone || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Employee ID:</td><td style="padding: 8px;">' + (data.employee_id || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Country:</td><td style="padding: 8px;">' + (data.country_base || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Company:</td><td style="padding: 8px;">' + (data.company_name || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Position:</td><td style="padding: 8px;">' + (data.position_name || '-') + '</td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Status:</td><td style="padding: 8px;"><span style="background: ' + (data.status === 'active' ? '#10b981' : '#f59e0b') + '; color: white; padding: 4px 8px; border-radius: 4px;">' + (data.status || '-') + '</span></td></tr>';
                    html += '<tr style="border-bottom: 1px solid #d1fae5;"><td style="padding: 8px; font-weight: bold;">Created:</td><td style="padding: 8px; font-size: 12px;">' + (data.created_at ? new Date(data.created_at).toLocaleString() : '-') + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '<div class="response success" style="margin-top: 10px;">' + JSON.stringify(data, null, 2) + '</div>';
                    
                    const responseEl = document.getElementById('userProfileResponse');
                    responseEl.innerHTML = html;
                } else {
                    showResponse('userProfileResponse', JSON.stringify(data, null, 2), 'error');
                }
            })
            .catch(e => showResponse('userProfileResponse', 'Error: ' + e.message, 'error'));
        }

        function logout() {
            if (!authToken) {
                showResponse('userProfileResponse', 'Not logged in', 'error');
                return;
            }

            apiFetch('/api/logout', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(data => {
                authToken = null;
                localStorage.removeItem('authToken');
                document.getElementById('tokenStatus').style.display = 'none';
                showResponse('userProfileResponse', 'Logged out successfully', 'success');
            })
            .catch(e => showResponse('userProfileResponse', 'Error: ' + e.message, 'error'));
        }

        function getLanguages() {
            apiFetch('/api/languages')
            .then(data => showResponse('languagesResponse', JSON.stringify(data, null, 2), 'success'))
            .catch(e => showResponse('languagesResponse', 'Error: ' + e.message, 'error'));
        }

        function getAllUsers() {
            if (!authToken) {
                showResponse('usersListResponse', 'Please login first to view users', 'error');
                return;
            }

            apiFetch('/api/users?per_page=100', {
                headers: { 'Authorization': 'Bearer ' + authToken }
            })
            .then(data => {
                if (data.data && data.data.items && data.data.items.length > 0) {
                    let html = '<table style="width:100%; border-collapse: collapse; margin-top: 10px;">';
                    html += '<thead style="background: #667eea; color: white;"><tr>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">User ID</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Employee ID</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Full Name</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Email</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Phone</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Company</th>';
                    html += '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">Status</th>';
                    html += '</tr></thead><tbody>';

                    data.data.items.forEach(user => {
                        html += '<tr style="border-bottom: 1px solid #ddd;">';
                        html += '<td style="padding: 10px;">' + user.id + '</td>';
                        html += '<td style="padding: 10px;">' + (user.employee_id || '-') + '</td>';
                        html += '<td style="padding: 10px;">' + (user.full_name || '-') + '</td>';
                        html += '<td style="padding: 10px;">' + (user.email || '-') + '</td>';
                        html += '<td style="padding: 10px;">' + (user.phone || '-') + '</td>';
                        html += '<td style="padding: 10px;">' + (user.company_name || '-') + '</td>';
                        html += '<td style="padding: 10px;"><span style="background: ' + (user.status === 'active' ? '#10b981' : '#f59e0b') + '; color: white; padding: 4px 8px; border-radius: 4px;">' + (user.status || '-') + '</span></td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table>';
                    html += '<p style="margin-top: 10px; color: #666; font-size: 12px;">Total Users: ' + data.data.pagination.total + '</p>';
                    
                    const responseEl = document.getElementById('usersListResponse');
                    responseEl.innerHTML = '<div class="response success" style="max-height: 600px; overflow-y: auto;">' + html + '</div>';
                } else {
                    showResponse('usersListResponse', 'No users found', 'error');
                }
            })
            .catch(e => showResponse('usersListResponse', 'Error: ' + e.message, 'error'));
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

            apiFetch('/api' + (endpoint.startsWith('/') ? endpoint : '/' + endpoint), options)
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
