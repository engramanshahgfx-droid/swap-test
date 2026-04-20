# API New And Updated (Mobile Handoff)

Date: 2026-04-05
Source of truth: routes/api.php
Updated collection: CrewSwap_API_Collection.json

## New APIs

1. GET /api/registration-option
- Alias of /api/registration-options for client compatibility.
- Public endpoint (no token).

2. POST /api/verify-otp
- Public endpoint used after register/simple-register.
- Request body:
```json
{
  "user_id": 15,
  "otp": "631927"
}
```

3. POST /api/resend-otp
- Public endpoint to resend OTP.
- Request body:
```json
{
  "user_id": 15
}
```

## Updated APIs

1. GET /api/registration-options and GET /api/registration-option
- Added `airports` in response payload.
- Airports now include: `id`, `name`, `iata_code`, `city`, `country`.
- Mobile can use this list to select base airport instead of hardcoding 3-letter values.

2. POST /api/simple-register
- Supports optional employee_id in request.
- If employee_id is not provided, API auto-generates it.
- Request body (updated):
```json
{
  "employee_id": "AD123456",
  "email": "tester@crewswap.com",
  "password": "Secret123!",
  "full_name": "Postman Tester"
}
```

3. User payload enrichment (mobile fields)
- Affected endpoints:
  - GET /api/user
  - GET /api/users
  - GET /api/users/{id}
  - PUT /api/user
- Added/ensured fields in payload:
  - company_id
  - company_name
  - position_id (already part of user payload)
  - position_name

4. POST /api/report-user
- Server-side safety fix for missing admin roles.
- Prevents RoleDoesNotExist 500 when roles are absent.

## Collection Changes Applied

1. Added requests in CrewSwap_API_Collection.json:
- POST /verify-otp
- POST /resend-otp
- GET /registration-options
- GET /registration-option (alias)

2. Updated request in CrewSwap_API_Collection.json:
- POST /simple-register now includes optional employee_id in sample body.

## Notes For Mobile Team

1. For reporting endpoint, reported_user_id must be numeric database user id (not employee_id).
2. Registration options are available using both:
- /api/registration-options
- /api/registration-option
3. Register endpoint accepts either:
- `country_base` (legacy behavior), or
- `airport_id` (recommended, value from registration-options.airports)
4. OTP flow is:
- register/simple-register -> verify-otp -> login/simple-login
