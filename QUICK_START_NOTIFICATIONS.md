# 🚀 Quick Start - Notifications System

## Access the Test Page
```
http://localhost:8000/notifications-test
```

## Test Credentials
- **Email**: `admin@test.com`
- **Password**: `password`

Or use any existing user account from your database.

## What You Can Test

### 1. **Send Chat Messages** 💬
- Get a recipient user ID from your database
- Send a message in the Chat panel
- Watch notification appear automatically in Notifications panel
- Unread count updates in real-time

### 2. **Send Support Messages** 🆘
- Click "Get Support Conversation" to start support chat
- Send a message to admin
- (Admin can reply from admin panel)
- Notifications appear automatically

### 3. **Manage Notifications** 🔔
- View all notifications in real-time
- Filter: All / Unread / Chat type
- Mark individual notifications as read (click on them)
- Mark all as read with one button
- Auto-refreshes every 5 seconds
- Unread badge updates live

## API Usage Examples

### Get all notifications
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/notifications
```

### Get only unread chat notifications
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  "http://localhost:8000/api/notifications?type=chat&is_read=false"
```

### Send a chat message (creates notification)
```bash
curl -X POST http://localhost:8000/api/send-message \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"recipient_id\": 2,
    \"message\": \"Hello from API\",
    \"message_type\": \"text\"
  }"
```

### Send a support message
```bash
curl -X POST http://localhost:8000/api/support/send-message \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"conversation_id\": 1,
    \"message\": \"I need help\",
    \"message_type\": \"text\"
  }"
```

### Mark notification as read
```bash
curl -X PUT http://localhost:8000/api/notifications/1/read \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## How It Works

### When message is sent:
```
User A sends message to User B
  → Message saved to database
  → Notification created for User B automatically
  → If User B has device_token → Firebase push sent
  → If User B logged in → Sees notification in 5 seconds
```

### Notification Flow:

**Chat Messages:**
```
ChatController → ChatService → MobileNotificationService → Database ✓
```

**Support Messages:**
```
SupportController → ChatService → MobileNotificationService → Database ✓
```

## Features

✅ **Real-time Notifications** - Auto-refresh every 5 seconds  
✅ **Push Support** - Firebase integration for mobile  
✅ **Unread Counter** - Badge shows unread count  
✅ **Filter Options** - By type and read status  
✅ **Mark as Read** - Individual or all at once  
✅ **Search** - Find notifications by keyword  
✅ **Multiple Types** - chat, system, swap, report  
✅ **Payload Data** - Extra info like conversation_id, message_id  

## Database

All notifications stored in `notifications` table:
- `id`: Primary key
- `user_id`: Recipient user ID
- `title`: Notification title
- `message`: Message content
- `type`: Type (chat, system, etc.)
- `sound`: Sound file to play
- `is_read`: Boolean
- `payload`: JSON with extra data
- `created_at`: Timestamp

## Files Added/Modified

**New Files:**
- `app/Http/Controllers/Api/SupportController.php` - Support endpoints
- `public/notifications-test.html` - Test interface
- `NOTIFICATIONS_SYSTEM.md` - Full documentation

**Updated Files:**
- `routes/api.php` - Added support routes
- `routes/web.php` - Added test page route
- `app/Services/ChatService.php` - Already creates notifications

## Next Steps

1. ✅ Test chat message notifications
2. ✅ Test support message notifications  
3. ✅ Verify unread counter works
4. ✅ Test mark as read functionality
5. ⏭️ Deploy to production
6. ⏭️ Configure Firebase for mobile push

## For Developers

### To create a notification manually:
```php
MobileNotificationService::createForUser(
    $user,
    'Title',
    'Message content',
    'chat',
    'chat_message_sound.mp3',
    ['payload_key' => 'value']
);
```

### To create notifications for multiple users:
```php
MobileNotificationService::createForUsers(
    [$user1, $user2, $user3],
    'Title',
    'Message',
    'system'
);
```

## Troubleshooting

**Q: Notifications not appearing?**
- A: Ensure user is logged in, check browser console for errors

**Q: Sound not playing?**
- A: Check browser allows audio, verify device volume

**Q: API returns 401 Unauthorized?**
- A: Ensure token is valid and passed in Authorization header

**Q: No support admin available?**
- A: Create a user with admin role in the system

## Support

See `NOTIFICATIONS_SYSTEM.md` for complete documentation.
