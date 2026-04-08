# Complete Notifications System

## Overview

The application now includes a **complete notifications system** that automatically creates notifications when:
1. ✅ User receives a **chat message** from another user
2. ✅ User receives a **support message** from admin/support staff
3. ✅ Admin sends **system notifications**

## Features Implemented

### 1. **Chat Message Notifications**
When User A sends a message to User B:
- A notification is automatically created for User B
- Notification includes: Sender's name + Message preview (first 120 chars)
- Type: `chat` with sound `chat_message_sound.mp3`
- Real-time broadcast via Laravel Events

### 2. **Support Message Notifications**
When admin replies to a user's support conversation:
- A notification is automatically created for the user
- Notification includes: Message content
- Type: `system` with sound `system_notification_sound.mp3`
- Manages support tickets as regular conversations

### 3. **Manual Admin Notifications**
Admins can send notifications directly to users or groups:
- Via API: `POST /api/notifications/send`
- Supports custom title, message, type, and payload
- Batch sending to multiple users

### 4. **Notification Management**
- **View all notifications**: `GET /api/notifications`
- **Filter by type**: `GET /api/notifications?type=chat` or `?type=system`
- **Filter by read status**: `GET /api/notifications?is_read=false`
- **Mark as read**: `PUT /api/notifications/{id}/read`
- **Search notifications**: `GET /api/notifications?search=keyword`

## Testing the System

### **Frontend Test Page**
Access the complete testing interface:
```
http://localhost:8000/notifications-test
```

#### Three Testing Sections:

**1. Authentication Panel** (Left)
- Login with test account
- Logout
- Display current user info

**2. Chat Messages Panel** (Middle)
- Send messages to other users
- View recent chat messages
- Each message sent → notification created

**3. Support Messages Panel** (Right)
- Create support conversation with admin
- Send support messages
- View support chat history
- Each support reply → notification created

**4. Notifications Panel** (Full Width Below)
- View all notifications in real-time
- Auto-refreshes every 5 seconds
- Filter by type (all/chat/system)
- Filter unread only
- Mark individual notifications as read
- Mark all as read
- Real-time unread counter badge

## API Endpoints

### Authentication
```bash
POST /api/login
POST /api/register
POST /api/verify-otp
```

### Chat Messages
```bash
GET /api/conversations                          # List all conversations
GET /api/messages/{conversation}                # Get messages from conversation
POST /api/send-message                          # Send chat message
POST /api/messages/{conversation}/read          # Mark conversation as read
GET /api/chat/unread-count                      # Get total unread count
```

### Support Messages (NEW)
```bash
GET /api/support/conversation                   # Get or create support conversation
POST /api/support/send-message                  # Send message to support
GET /api/support/messages/{conversationId}      # Get support conversation messages
GET /api/support/conversations                  # Admin: List all support conversations
```

### Notifications
```bash
GET /api/notifications                          # List notifications
POST /api/notifications/send                    # Send notification (admin only)
PUT /api/notifications/{id}/read                # Mark notification as read
```

### Query Parameters for Notifications
```bash
# List with pagination
/api/notifications?per_page=20&page=1

# Filter by type
/api/notifications?type=chat

# Filter by read status
/api/notifications?is_read=false

# Search
/api/notifications?search=keyword

# Combine filters
/api/notifications?type=chat&is_read=false&per_page=50
```

## Notification Types

| Type | Sound | Usage |
|------|-------|-------|
| `chat` | `chat_message_sound.mp3` | User messages |
| `system` | `system_notification_sound.mp3` | Admin/support replies |
| `swap` | `swap_request_sound.mp3` | Swap requests |
| `report` | `report_alert_sound.mp3` | Report alerts |

## Response Examples

### GET /api/notifications
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "title": "New Message",
                "message": "John Doe: Hey, how are you?",
                "type": "chat",
                "sound": "chat_message_sound.mp3",
                "created_at": "2026-04-08 10:30:45",
                "read": false,
                "payload": {
                    "conversation_id": "5",
                    "message_id": "42"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 2,
            "per_page": 20,
            "total": 35
        }
    }
}
```

### POST /api/send-message
```json
{
    "success": true,
    "message": "Message sent successfully",
    "data": {
        "message_id": 42,
        "sender_id": 1,
        "receiver_id": 2,
        "message": "Hello!",
        "message_type": "text",
        "delivery_status": "delivered",
        "created_at": "2026-04-08T10:30:45.000000Z"
    }
}
```

### POST /api/support/send-message
```json
{
    "success": true,
    "message": "Support message sent",
    "data": {
        "message_id": 43,
        "sender_id": 5,
        "message": "I need help with my account",
        "message_type": "text",
        "created_at": "2026-04-08T10:35:20.000000Z"
    }
}
```

### PUT /api/notifications/{id}/read
```json
{
    "success": true,
    "message": "Notification marked as read",
    "data": {
        "id": 1,
        "read": true
    }
}
```

## Testing Steps

### Step 1: Login
1. Navigate to `http://localhost:8000/notifications-test`
2. Use test credentials:
   - Email: `admin@test.com`
   - Password: `password`
3. Or use any existing user account

### Step 2: Test Chat Notifications
1. Enter a recipient User ID in the Chat panel
2. Type a message
3. Click "Send Chat Message"
4. Check the **Notifications Panel** - a new chat notification appears
5. Click on notification to mark as read
6. Notification disappears from unread list

### Step 3: Test Support Notifications
1. Click "Get Support Conversation" in Support panel
2. Type a support message
3. Click "Send Support Message"
4. Check Notifications Panel - support notification appears
5. (Admin can reply from admin panel to test receiving support reply)

### Step 4: Filter Notifications
1. Click "Unread Only" to see only unread notifications
2. Click "Chat" to filter only chat type
3. Click "All" to see all notifications
4. Use "Refresh" button to manually update

### Step 5: Mark as Read
1. Click on any notification to mark it as read
2. Or click "Mark All as Read" to mark everything
3. Watch the unread badge update in real-time

## Database Schema

### notifications table
```sql
- id: Primary Key
- user_id: Recipient user ID
- title: Notification title
- message: Notification message (max 2000 chars)
- type: Notification type (chat, system, swap, report, etc.)
- sound: Sound file to play
- is_read: Boolean read status
- payload: JSON with extra data (conversation_id, message_id, etc.)
- data: JSON with title and message (auto-filled)
- created_at: Timestamp
- updated_at: Timestamp
```

## Firebase Push Notifications

The system also integrates with Firebase for push notifications:
- If user has a `device_token` stored, notifications are sent to device
- Payload includes: type, sound, notification_id
- Firebase service handles delivery to mobile/web clients

## Real-Time Features

### WebSocket Broadcast
- Chat messages broadcast instantly via Laravel Events
- Components subscribe to channels and update in real-time
- Support messages also broadcast to admin panel

### Auto-Refresh
- Frontend test page auto-refreshes notifications every 5 seconds
- Can be disabled if WebSocket is connected

## Error Handling

### Common Issues

**Issue**: "No support admin available"
- **Solution**: Ensure at least one user with admin/super-admin role exists

**Issue**: Notifications not appearing
- **Solutions**:
  - Check user is logged in (token is valid)
  - Verify user IDs are correct
  - Flush notification cache: `php artisan cache:clear`

**Issue**: Sound not playing
- **Solutions**:
  - Check browser allows audio playback
  - Check device volume settings
  - Verify sound file exists: `public/sounds/`

## Implementation Details

### Files Modified/Created

1. **App/Http/Controllers/Api/SupportController.php** (NEW)
   - Handles support conversation and message endpoints
   - Creates notifications when messages received

2. **App/Services/ChatService.php** (UPDATED)
   - Already creates notifications on message send
   - Uses MobileNotificationService

3. **App/Http/Controllers/Api/NotificationController.php** (EXISTS)
   - Lists notifications with filters
   - Marks notifications as read
   - Sends admin notifications

4. **App/Models/MobileNotification.php** (EXISTS)
   - Maps to 'notifications' table
   - Auto-fills data field

5. **routes/api.php** (UPDATED)
   - Added support routes
   - Added SupportController import

6. **routes/web.php** (UPDATED)  
   - Added /notifications-test route
   - Returns test HTML page

7. **public/notifications-test.html** (NEW)
   - Complete test interface
   - 3-panel layout (Auth, Chat, Support, Notifications)
   - Real-time auto-refresh
   - Filter and search

## Architecture

```
User A sends message to User B
    ↓
ChatController.sendMessage()
    ↓
ChatService.sendMessage()
    ↓
1. Create Message record
2. Broadcast NewMessage event
3. MobileNotificationService.createForUser(User B)
    ↓
1. Create notification record
2. Send Firebase push (if device_token)
    ↓
User B receives notification in real-time
```

## Performance Considerations

- Notifications are paginated (default 20 per page)
- Indexes on user_id, created_at in notifications table
- Soft deletes can be added for archive behavior
- Consider archiving old notifications monthly

## Security

- All endpoints require Sanctum authentication token
- Support endpoints verify user is part of conversation
- Admin notification endpoint checks user has admin role
- Notifications only return data for authenticated user

## Future Enhancements

1. **Notification Preferences**
   - Let users customize sounds per type
   - Mute/unmute notification types
   - Do Not Disturb schedules

2. **Notification Archive**
   - Soft delete old notifications
   - Archive notifications > 30 days

3. **Rich Notifications**
   - Images in notifications
   - Action buttons (Reply, Accept, Reject)
   - Custom templates

4. **Real-Time Websocket**
   - Live notifications via WebSocket
   - Removed auto-refresh delay
   - Better scalability

5. **Email Digest**
   - Daily/weekly email summaries
   - Customizable digest frequency

6. **Notification Scheduling**
   - Send notifications at specific times
   - Queue notifications for later

## Support & Documentation

For issues or questions:
1. Check this documentation first
2. Review test page for examples
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify database: `php artisan tinker` then `MobileNotification::all()`
