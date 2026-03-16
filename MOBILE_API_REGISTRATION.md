# CrewSwap Mobile API - Registration & Authentication

## Base URL
```
http://{{base_url}}/api
```

---

## 📋 Preregistration Data

### Get Registration Options
Returns airlines, positions, plane types for registration form.

**Endpoint:**
```
GET /registration-options
```

**Optional Query Params:**
```
?airline_id=1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "airlines": [
      {
        "id": 1,
        "name": "Air Pacific",
        "iata_code": "FJ",
        "country": "Fiji"
      },
      {
        "id": 2,
        "name": "Fiji Airways",
        "iata_code": "FJ",
        "country": "Fiji"
      }
    ],
    "positions": [
      {
        "id": 1,
        "name": "Pilot",
        "code": "PILOT"
      },
      {
        "id": 2,
        "name": "Co-Pilot",
        "code": "COPILOT"
      },
      {
        "id": 3,
        "name": "Flight Attendant",
        "code": "FA"
      },
      {
        "id": 4,
        "name": "Purser",
        "code": "PURSER"
      },
      {
        "id": 5,
        "name": "Chief Purser",
        "code": "CHIEF_PURSER"
      }
    ],
    "plane_types": [
      {
        "id": 1,
        "name": "Boeing 737",
        "manufacturer": "Boeing"
      },
      {
        "id": 2,
        "name": "Airbus A380",
        "manufacturer": "Airbus"
      }
    ]
  }
}
```

---

## 🔐 Authentication APIs

### 1. Register User

**Endpoint:**
```
POST /register
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "airline_id": 1,
  "position_id": 3,
  "plane_type_id": 1,
  "employee_id": "EMP001",
  "date_of_birth": "1990-05-15"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Registration successful. Please verify your email.",
  "data": {
    "id": 123,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "employee_id": "EMP001",
    "airline": {
      "id": 1,
      "name": "Air Pacific"
    },
    "position": {
      "id": 3,
      "name": "Flight Attendant"
    },
    "plane_type": {
      "id": 1,
      "name": "Boeing 737"
    },
    "status": "inactive",
    "created_at": "2026-03-17T10:30:00Z"
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

### 2. Login User

**Endpoint:**
```
POST /login
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john.doe@example.com",
  "password": "SecurePassword123!",
  "device_name": "iPhone 12 Pro"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 123,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "employee_id": "EMP001",
      "status": "active",
      "airline": {
        "id": 1,
        "name": "Air Pacific"
      },
      "position": {
        "id": 3,
        "name": "Flight Attendant"
      },
      "plane_type": {
        "id": 1,
        "name": "Boeing 737"
      }
    },
    "token": "8|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ..."
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

**Error Response (403 Forbidden - Inactive User):**
```json
{
  "success": false,
  "message": "Your account is inactive. Please contact administrator."
}
```

---

### 3. Verify OTP

**Endpoint:**
```
POST /verify-otp
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john.doe@example.com",
  "otp": "123456"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "data": {
    "token": "8|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ..."
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "message": "Invalid or expired OTP"
}
```

---

### 4. Resend OTP

**Endpoint:**
```
POST /resend-otp
```

**Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "john.doe@example.com"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "OTP sent to your email"
}
```

---

### 5. Logout (Protected)

**Endpoint:**
```
POST /logout
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Logout successful"
}
```

---

### 6. Refresh Token (Protected)

**Endpoint:**
```
POST /refresh-token
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Token refreshed",
  "data": {
    "token": "9|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ..."
  }
}
```

---

## 👤 User Information (Protected)

### Get Current User Profile

**Endpoint:**
```
GET /user
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "employee_id": "EMP001",
    "date_of_birth": "1990-05-15",
    "status": "active",
    "email_verified_at": "2026-03-17T10:35:00Z",
    "phone_verified_at": "2026-03-17T10:35:00Z",
    "airline": {
      "id": 1,
      "name": "Air Pacific"
    },
    "position": {
      "id": 3,
      "name": "Flight Attendant"
    },
    "plane_type": {
      "id": 1,
      "name": "Boeing 737"
    }
  }
}
```

---

### Update User Profile (Protected)

**Endpoint:**
```
PUT /user
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890",
  "airline_id": 1,
  "position_id": 3,
  "plane_type_id": 1
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 123,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+1234567890",
    "airline": {
      "id": 1,
      "name": "Air Pacific"
    },
    "position": {
      "id": 3,
      "name": "Flight Attendant"
    },
    "plane_type": {
      "id": 1,
      "name": "Boeing 737"
    }
  }
}
```

---

## 🔑 User Statuses

| Status | Description |
|--------|------------|
| `inactive` | New users start as inactive (needs admin approval) |
| `active` | User can login and use app |
| `blocked` | User cannot login (admin action) |

---

## ⚠️ Error Handling

All API errors include a structured response:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Error message"]
  }
}
```

**Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (access denied)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `500` - Server Error

---

## 📱 Implementation Tips

### 1. Store Token Securely
```javascript
// Save token after login/registration
const token = response.data.token;
localStorage.setItem('auth_token', token);  // or use secure storage
```

### 2. Include Token in Requests
```javascript
const headers = {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
};
```

### 3. Handle Token Expiration
```javascript
// Check if response is 401, refresh token
if (response.status === 401) {
  const newToken = await refreshToken();
  retryRequest(newToken);
}
```

### 4. Load Form Options on App Load
```javascript
// Call once during app initialization
const options = await fetch('/api/registration-options');
// Store airlines, positions, plane_types for form dropdowns
```

---

## 🧪 Test Credentials

After seeding database:

```
Email: admin@crewswap.com
Password: password
Status: active (can login immediately)
```

---

## 📞 Support

For API issues or questions, contact the backend team.

**Last Updated:** March 17, 2026
