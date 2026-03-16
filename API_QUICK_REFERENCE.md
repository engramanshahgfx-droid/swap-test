# 📱 CrewSwap Mobile API - Quick Reference

## Base URL
```
http://localhost:8000/api
```

---

## 🚀 Quick Start

### 1️⃣ Get Registration Form Data
```bash
curl -X GET "http://localhost:8000/api/registration-options"
```

### 2️⃣ Register New User
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "airline_id": 1,
    "position_id": 3,
    "plane_type_id": 1,
    "employee_id": "EMP001",
    "date_of_birth": "1990-05-15"
  }'
```

### 3️⃣ Login
```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123!",
    "device_name": "iPhone 12"
  }'
```

---

## 📋 API Endpoints Summary

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| GET | `/registration-options` | ❌ | Get airlines, positions, plane types |
| POST | `/register` | ❌ | Register new user |
| POST | `/login` | ❌ | Login user |
| POST | `/verify-otp` | ❌ | Verify OTP code |
| POST | `/resend-otp` | ❌ | Resend OTP |
| GET | `/user` | ✅ | Get current user profile |
| PUT | `/user` | ✅ | Update user profile |
| POST | `/logout` | ✅ | Logout user |
| POST | `/refresh-token` | ✅ | Get new token |

---

## 🔑 Authentication

**All protected endpoints require header:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Get token from login response:**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "8|eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ..."
  }
}
```

---

## ✅ Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (token missing/invalid) |
| 403 | Forbidden (account inactive) |
| 422 | Validation failed |
| 500 | Server error |

---

## 🎯 Response Format

**Success:**
```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "...",
  "errors": {
    "field": ["error message"]
  }
}
```

---

## 📲 Test Credentials

```
Email:    admin@crewswap.com
Password: password
```

---

## 🛠️ Common Issues

### ❌ "Validation failed" (422)
Check all required fields are provided and valid.

### ❌ "Invalid credentials" (401 on login)
Verify email and password are correct.

### ❌ "Your account is inactive" (403)
Admin must activate the account first.

### ❌ "Token missing/invalid" (401 on protected routes)
Include valid Authorization header.

---

## 📞 Need Help?

Check full documentation: [MOBILE_API_REGISTRATION.md](./MOBILE_API_REGISTRATION.md)

