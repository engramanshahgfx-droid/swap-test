# 📱 CrewSwap Mobile App - API Integration Quick Start

## 🎯 Your Mobile API is Ready! ✅

Your backend API has been fully set up and is ready for mobile app integration. This document contains everything you need to get started.

---

## 🚀 Quick Start (5 minutes)

### Step 1: Import Postman Collection
1. Download **Postman** from https://www.postman.com/downloads
2. Open Postman → Click **Import**
3. Select `CrewSwap_Mobile_API_Collection.json` from the project root
4. ✅ You now have all API endpoints ready to test

### Step 2: Test the API
1. Open the imported collection
2. In **Variables**, set `base_url` to `http://localhost:8000` (if testing locally)
3. Click **"1. Register New User"** → Click **Send**
4. Look for `test_otp` in the response (should be `123456`)
5. Click **"2. Verify OTP"** → Click **Send** (token auto-populated)
6. ✅ You're authenticated!

### Step 3: Start Integration
Follow the guide below to integrate into your mobile app.

---

## 📚 Documentation Available

| Document | Purpose |
|----------|---------|
| [MOBILE_API_COMPLETE_GUIDE.md](MOBILE_API_COMPLETE_GUIDE.md) | 📖 Full API reference with all endpoints |
| [MOBILE_API_QUICK_START.md](MOBILE_API_QUICK_START.md) | ⚡ Quick reference with curl examples |
| [MOBILE_API_IMPLEMENTATION_STATUS.md](MOBILE_API_IMPLEMENTATION_STATUS.md) | ✅ Complete status of all features |
| [CrewSwap_Mobile_API_Collection.json](CrewSwap_Mobile_API_Collection.json) | 📮 Postman collection |

---

## 🔑 Test Credentials

```
Email:       test@example.com
Password:    password123
Phone:       +1234567890
OTP:         123456 (test mode only)
Employee ID: EMP001
Airline ID:  1
Position ID: 1
Plane Type:  1
```

---

## 🔐 Authentication Flow (3 Steps)

### 1️⃣ Register
```
POST /api/register
```
Send user details → Get `token` + `test_otp`

### 2️⃣ Verify OTP
```
POST /api/verify-otp
```
Send `user_id` + `otp` → Get new verification token

### 3️⃣ Login
```
POST /api/login
```
Send `email` + `password` → Get authenticated token

**After this**, all requests use the token in header:
```
Authorization: Bearer {your_token}
```

---

## 📱 Integration Guide

### For iOS (Swift)

```swift
import Foundation

// 1. Initialize API Client
let baseURL = URL(string: "http://localhost:8000")

// 2. Register
let registerBody = """
{
    "employee_id": "EMP001",
    "full_name": "John Doe",
    "phone": "+1234567890",
    "email": "john@example.com",
    "country_base": "US",
    "airline_id": 1,
    "plane_type_id": 1,
    "position_id": 1,
    "password": "password123"
}
"""

var request = URLRequest(url: baseURL!.appendingPathComponent("api/register"))
request.httpMethod = "POST"
request.setValue("application/json", forHTTPHeaderField: "Content-Type")
request.httpBody = registerBody.data(using: .utf8)

// 3. Handle Response
URLSession.shared.dataTask(with: request) { data, response, error in
    let json = try? JSONDecoder().decode(RegisterResponse.self, from: data!)
    let token = json?.data.token
    let testOTP = json?.data.test_otp  // Display to user
}.resume()
```

### For Android (Kotlin)

```kotlin
// Using Retrofit
interface CrewSwapAPI {
    @POST("api/register")
    suspend fun register(@Body request: RegisterRequest): RegisterResponse
    
    @POST("api/verify-otp")
    suspend fun verifyOtp(@Body request: OTPRequest): OTPResponse
    
    @POST("api/login")
    suspend fun login(@Body request: LoginRequest): LoginResponse
}

// Usage
val retrofit = Retrofit.Builder()
    .baseUrl("http://localhost:8000")
    .addConverterFactory(GsonConverterFactory.create())
    .build()

val api = retrofit.create(CrewSwapAPI::class.java)

// Register
val response = api.register(
    RegisterRequest(
        employee_id = "EMP001",
        full_name = "John Doe",
        phone = "+1234567890",
        email = "john@example.com",
        country_base = "US",
        airline_id = 1,
        plane_type_id = 1,
        position_id = 1,
        password = "password123"
    )
)

val token = response.data.token
val testOTP = response.data.test_otp
```

---

## 🔒 Security Checklist

Before deploying to production, ensure:

- [ ] ✅ Tokens are stored securely (Keychain/Keystore)
- [ ] ✅ HTTPS is used (never HTTP in production)
- [ ] ✅ No sensitive data in logs
- [ ] ✅ API calls have proper error handling
- [ ] ✅ Token refresh is implemented
- [ ] ✅ 401 responses redirect to login
- [ ] ✅ Passwords are never cached

---

## 🧪 Testing Scenarios

### Scenario 1: Complete Registration Flow
1. POST `/api/register` with test credentials
2. POST `/api/verify-otp` with `123456`
3. POST `/api/login` with email + password
4. GET `/api/profile` to confirm authentication

### Scenario 2: Phone Verification
1. POST `/api/register`
2. POST `/api/login` (returns 403 - not verified)
3. Get `test_otp` from 403 response
4. POST `/api/verify-otp` with that OTP
5. POST `/api/login` again (now returns 200)

### Scenario 3: Token Refresh
1. Get valid token from login
2. Wait until near expiry
3. POST `/api/refresh-token` with current token
4. Use new token for next requests

---

## 📊 Main Features Available

### User Management
- ✅ Register new users
- ✅ Login with credentials
- ✅ Get user profile
- ✅ Update profile
- ✅ Logout

### Trip Management
- ✅ Get user's assigned trips
- ✅ Search available trips
- ✅ Filter by date/airport

### Swap Management
- ✅ Request swap with another crew
- ✅ Approve/reject swap requests
- ✅ Track swap history

### Messaging
- ✅ Send messages to crew
- ✅ Get message history
- ✅ Real-time notifications

### Notifications
- ✅ Swap request notifications
- ✅ Approval/rejection notifications
- ✅ New message alerts

---

## 🐛 Troubleshooting

### Problem: "Unauthenticated" or 401 error
**Solution:** 
- Check token is recent
- Call `/api/refresh-token` to get new token
- Verify Authorization header is set correctly

### Problem: OTP not working
**Solution:**
- Make sure using `123456` for test environment
- Check OTP hasn't expired (10 minute limit)
- Verify it matches the one in registration response

### Problem: CORS errors
**Solution:**
- Check backend has CORS headers set
- Verify your mobile app domain is whitelisted
- Contact backend team if issue persists

### Problem: Connection timeout
**Solution:**
- Check server is running
- Verify correct base URL
- Check network connectivity

---

## 📞 API Endpoint Reference

### Authentication
| Method | Endpoint | Status |
|--------|----------|--------|
| POST | `/api/register` | ✅ Ready |
| POST | `/api/login` | ✅ Ready |
| POST | `/api/verify-otp` | ✅ Ready |
| POST | `/api/refresh-token` | ✅ Ready |
| POST | `/api/logout` | ✅ Ready |

### Trips & Swaps
| Method | Endpoint | Status |
|--------|----------|--------|
| GET | `/api/user-trips` | ✅ Ready |
| GET | `/api/available-trips` | ✅ Ready |
| POST | `/api/swap-requests` | ✅ Ready |
| GET | `/api/swap-requests` | ✅ Ready |
| POST | `/api/swap-requests/{id}/approve` | ✅ Ready |
| POST | `/api/swap-requests/{id}/reject` | ✅ Ready |

### User & Profile
| Method | Endpoint | Status |
|--------|----------|--------|
| GET | `/api/profile` | ✅ Ready |
| PUT | `/api/profile` | ✅ Ready |

### Messages & Notifications
| Method | Endpoint | Status |
|--------|----------|--------|
| GET | `/api/messages` | ✅ Ready |
| POST | `/api/messages` | ✅ Ready |
| GET | `/api/notifications` | ✅ Ready |

---

## 💡 Best Practices

### Token Management
```
1. Store token securely after login
2. Check expiry before each request
3. Call refresh if approaching expiry
4. Remove token on logout
5. Redirect to login on 401 error
```

### Error Handling
```
1. Always check response.success
2. Parse error messages
3. Show user-friendly messages
4. Log errors for debugging
5. Implement retry logic
```

### Network Optimization
```
1. Use gzip compression
2. Set reasonable timeouts (30s)
3. Implement connection pooling
4. Cache responses when appropriate
5. Use CDN for static assets
```

---

## 🚀 Next Steps

1. **Import Postman collection** and test all endpoints
2. **Review complete guide** at [MOBILE_API_COMPLETE_GUIDE.md](MOBILE_API_COMPLETE_GUIDE.md)
3. **Start coding** - Begin authentication integration
4. **Test thoroughly** - Use provided test credentials
5. **Deploy to staging** - Before production release

---

## 📖 Full Documentation

For detailed endpoint documentation, request/response formats, and advanced topics, see:
→ **[MOBILE_API_COMPLETE_GUIDE.md](MOBILE_API_COMPLETE_GUIDE.md)**

---

## ✅ Quick Verification

Run this to verify your setup is working:

```bash
# 1. Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": "EMP001",
    "full_name": "Test User",
    "phone": "+1234567890",
    "email": "test@example.com",
    "country_base": "US",
    "airline_id": 1,
    "plane_type_id": 1,
    "position_id": 1,
    "password": "password123"
  }'

# Expected: Returns token + test_otp: "123456"
```

---

**Status**: 🟢 Production Ready  
**Last Updated**: January 2024  
**Version**: 1.0

**Questions?** Check the documentation files or contact the backend team.

**Ready to build amazing things!** 🚀
