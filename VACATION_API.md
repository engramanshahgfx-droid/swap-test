# Vacation Publishing API Documentation

## Overview
New simplified API for publishing, browsing, and managing vacation month-ranges. Designed for mobile app with month-based UI (APR, MAY, JUN, etc).

---

## 1. Publish Vacation
**Endpoint:** `POST /api/publish-vacation`

**Authentication:** Required (Sanctum token)

**Purpose:** Publish a vacation availability for a specific month range

### Request Body
```json
{
  "publisher_id": 1,
  "departure_month": "APR",
  "arrival_month": "MAY",
  "position": "Captain",
  "notes": "Looking for swap during vacation"
}
```

### Request Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `publisher_id` | integer | ✅ Yes | User ID publishing the vacation |
| `departure_month` | string | ✅ Yes | 3-letter month code (JAN-DEC) |
| `arrival_month` | string | ✅ Yes | 3-letter month code (JAN-DEC) |
| `position` | string | ❌ No | Crew position (Captain, First Officer, Purser, Flight Attendant) |
| `notes` | string | ❌ No | Additional notes (max 500 chars) |

### Response (201 Created)
```json
{
  "success": true,
  "message": "Vacation published successfully",
  "data": {
    "id": 789,
    "publisher_id": 1,
    "publisher_name": "John Doe",
    "departure_month": "APR",
    "arrival_month": "MAY",
    "departure_date": "2026-04-01",
    "arrival_date": "2026-05-31",
    "position": "Captain",
    "status": "available",
    "published_at": "2026-04-05T10:30:00Z",
    "expires_at": "2026-05-31T23:59:59Z"
  }
}
```

### Example cURL
```bash
curl -X POST http://localhost:8000/api/publish-vacation \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "publisher_id": 1,
    "departure_month": "APR",
    "arrival_month": "MAY",
    "position": "Captain",
    "notes": "Available for swap"
  }'
```

---

## 2. Get My Vacations
**Endpoint:** `GET /api/my-vacations`

**Authentication:** Required (Sanctum token)

**Purpose:** Get all vacations published by the current user

### Query Parameters
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number for pagination |
| `per_page` | integer | 20 | Items per page (max 100) |

### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 789,
        "departure_month": "APR",
        "arrival_month": "MAY",
        "position": "Captain",
        "status": "available",
        "published_at": "2026-04-05T10:30:00Z",
        "expires_at": "2026-05-31T23:59:59Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 1
    }
  }
}
```

---

## 3. Browse Vacations
**Endpoint:** `GET /api/browse-vacations`

**Authentication:** Required (Sanctum token)

**Purpose:** Browse vacations published by other crew members

### Response (200 OK)
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 789,
        "publisher": {
          "id": 1,
          "name": "John Doe",
          "employee_id": "EMP001"
        },
        "departure_month": "APR",
        "arrival_month": "MAY",
        "position": "Captain",
        "notes": "Available for swap",
        "published_at": "2026-04-05T10:30:00Z",
        "expires_at": "2026-05-31T23:59:59Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 95
    }
  }
}
```

---

## Month Code Reference
JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC

---

## Troubleshooting

**Error: "Unauthenticated"**
- Solution: Ensure you're sending valid Bearer token in Authorization header
- Mobile app must login first with `POST /simple-login` to get token

**Error: "Select a vacation swap first"** (from UI)
- This was a frontend issue - user interface validation before API call
- Ensure publisher_id and month fields are filled

**Error: Publisher not found**
- Verify publisher_id exists in users table
- Use `GET /users` to list valid user IDs

