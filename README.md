# CrewSwap - Flight Crew Trip Swap Management System

CrewSwap is a comprehensive full-stack application for managing flight crew trip swaps. Built with Laravel backend and Vue.js 3 frontend, it enables airline crew members to publish available trips, request swaps with other crew members, and includes a complete approval workflow with manager oversight.

## Features

- **Authentication System** - OTP verification via SMS (Africa's Talking integration)
- **🔥 Firebase Authentication** - Secure email/password authentication with Firebase + Laravel Sanctum fallback
- **⚡ Vue.js 3 Frontend** - Modern, reactive SPA with Composition API and Vite
- **Role-Based Access Control** - Using Spatie Laravel-Permission
- **Trip Management** - View assigned trips, publish for swap
- **Swap Marketplace** - Browse and request available trip swaps
- **Approval Workflow** - Two-step approval (owner + manager)
- **Real-time Chat** - Communication between crew members
- **💬 Real-time Messaging** - Live chat interface with auto-polling for updates
- **Reporting System** - Report inappropriate behavior
- **Admin Panel** - Filament-based admin dashboard
- **RESTful API** - Sanctum-authenticated API endpoints
- **🌍 Multi-Language Support** - Full Arabic (عربي) and English localization with RTL support
- **Dynamic Language Switching** - Switch between languages via API or query parameters
- **📱 Fully Responsive** - Mobile-first design with bottom navigation and adaptive layouts
- **🎨 Modern UI** - Tailwind CSS 4 with airline-themed blue/navy color scheme

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM
- Firebase Project (for authentication)

## Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd crewswap
```

2. **Install Backend Dependencies**
```bash
composer install
```

3. **Install Frontend Dependencies**
```bash
npm install
```

4. **Configure Environment**
```bash
cp .env.example .env
# Edit .env with your database and Firebase credentials
php artisan key:generate
```

5. **Run Database Migrations**
```bash
php artisan migrate --seed
```

6. **Build Frontend Assets**
```bash
# For development
npm run dev

# For production
npm run build
```

7. **Start Development Servers**
```bash
# Terminal 1 - Laravel Backend
php artisan serve

# Terminal 2 - Vite Frontend (if using npm run dev)
# Vite will run automatically
```

8. **Access the Application**
- Frontend: http://localhost:8000
- Admin Panel: http://localhost:8000/admin
- API: http://localhost:8000/api

## 🚀 Frontend Architecture

### Vue.js 3 Application

The frontend is a complete Single Page Application (SPA) built with:

- **Vue.js 3.5+** with Composition API
- **Vite 7.3+** for blazing-fast development
- **Vue Router 4** for client-side routing
- **Pinia 2** for state management
- **Axios** for API communication
- **Tailwind CSS 4** for styling
- **Firebase 11** for authentication

### Pages & Components

#### Authentication Pages
- `/login` - Firebase email/password login
- `/register` - Complete crew member registration
- `/verify-otp` - OTP verification (if enabled)

#### Dashboard Pages
- `/dashboard` - Home with stats and quick actions
- `/my-trips` - Trip management with publish functionality
- `/trips/:id` - Detailed trip view
- `/browse` - Marketplace to find swap opportunities
- `/swaps` - Track sent/received swap requests
- `/messages` - Conversation list
- `/chat/:id` - Real-time chat interface
- `/profile` - User profile and statistics
- `/settings` - App preferences and account settings

#### Reusable Components
- `FlightCard` - Trip display with route visualization
- `ChatBubble` - Message bubble with timestamps
- `SwapRequestCard` - Swap request with user info

### API Integration

All API calls are centralized in `resources/js/services/api.js`:

```javascript
// Example usage
import { apiService } from '@/services/api';

// Fetch trips
const trips = await apiService.trips.getMyTrips({ status: 'published' });

// Request swap
await apiService.swaps.requestSwap({ trip_id: 123, message: 'Hello' });

// Send message
await apiService.chat.sendMessage({ conversation_id: 1, message: 'Hi!' });
```

## 📚 Documentation

Comprehensive documentation is available for all aspects of the application:

- **[VUE_FRONTEND_COMPLETE.md](VUE_FRONTEND_COMPLETE.md)** - Complete frontend implementation guide
- **[TESTING_CHECKLIST.md](TESTING_CHECKLIST.md)** - Comprehensive testing checklist
- **[FIREBASE_SETUP_GUIDE.md](FIREBASE_SETUP_GUIDE.md)** - Firebase integration instructions
- **[FIREBASE_QUICK_START.md](FIREBASE_QUICK_START.md)** - Quick Firebase reference
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Backend API documentation
- **[LOCALIZATION_DEVELOPER_GUIDE.md](LOCALIZATION_DEVELOPER_GUIDE.md)** - Arabic localization guide

## 🔐 Authentication Flow

1. User registers/logs in through Vue.js frontend
2. Firebase authenticates and returns ID token
3. Frontend sends token to Laravel backend
4. Backend verifies token with Firebase and creates/updates user
5. Backend returns Laravel Sanctum token (optional fallback)
6. Token stored in Pinia store and localStorage
7. Axios automatically includes token in all API requests
8. Middleware verifies token on protected routes

## 🛠️ Development

### Frontend Development
```bash
npm run dev          # Start Vite with HMR
npm run build        # Build for production
npm run preview      # Preview production build
```

### Backend Development
```bash
php artisan serve    # Start development server
php artisan test     # Run tests
php artisan pint     # Format code
cd crewswap
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node dependencies**
```bash
npm install
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Update `.env` with your database and SMS credentials**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crewswap
DB_USERNAME=root
DB_PASSWORD=your_password

# SMS Configuration (Africa's Talking)
SMS_DRIVER=africastalking
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=your_api_key
AFRICASTALKING_FROM=CrewSwap
```

6. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

7. **Create storage link**
```bash
php artisan storage:link
```

8. **Build assets**
```bash
npm run build
```

9. **Start the development server**
```bash
php artisan serve
```

## Default Users (After Seeding)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@crewswap.com | password |
| Crew Manager | manager@crewswap.com | password |
| Purser | purser@crewswap.com | password |
| Flight Attendant | alice@crewswap.com | password |

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/verify-otp` - Verify OTP
- `POST /api/resend-otp` - Resend OTP
- `POST /api/logout` - Logout (auth required)

### Trips
- `GET /api/my-trips` - Get user's trips
- `GET /api/trip-details/{id}` - Get trip details
- `GET /api/browse-trips` - Browse available trips for swap
- `POST /api/publish-trip` - Publish a trip for swap
- `GET /api/swap-history` - Get swap history

### Swap Requests
- `POST /api/request-swap` - Request a swap
- `POST /api/confirm-swap/{id}` - Approve swap (owner/manager)
- `POST /api/reject-swap/{id}` - Reject swap

### Chat
- `GET /api/conversations` - Get conversations
- `GET /api/messages/{id}` - Get conversation messages
- `POST /api/send-message` - Send message
- `POST /api/messages/{id}/read` - Mark as read

### Reports
- `POST /api/report-user` - Report a user
- `GET /api/my-reports` - Get submitted reports

## Admin Panel

Access the Filament admin panel at `/admin`

## Queue Processing

For real-time notifications, run the queue worker:
```bash
php artisan queue:work
```

## Project Structure

```
app/
├── Events/           # Swap events for broadcasting
├── Filament/         # Filament admin resources
├── Http/
│   ├── Controllers/Api/  # API controllers
│   └── Requests/Api/     # Form request validation
├── Listeners/        # Event listeners for notifications
├── Models/           # Eloquent models
├── Notifications/    # Notification classes
├── Policies/         # Authorization policies
├── Providers/        # Service providers
└── Services/         # Business logic services

database/
├── migrations/       # Database migrations
└── seeders/          # Database seeders
```

## 🌍 Language Support (Arabic & English)

CrewSwap now supports **complete Arabic localization** alongside English. All API responses, messages, and the admin panel are fully translated.

### Supported Languages
- **English (en)** - Left-to-Right
- **Arabic (ar)** - Right-to-Left (العربية)

### Language API Endpoints

#### Get Supported Languages
```bash
GET /api/languages
```

#### Get Current Language
```bash
GET /api/current-language
```

#### Set Language
```bash
POST /api/set-language/{lang}
# Example: POST /api/set-language/ar
```

#### Change Language via Query Parameter
```bash
GET /api/my-trips?lang=ar
POST /api/login?lang=ar
```

### Example: Using Arabic

```bash
# 1. Set language to Arabic
curl -X POST http://localhost:8000/api/set-language/ar

# 2. Make API call (response will be in Arabic)
curl -X GET http://localhost:8000/api/my-trips

# Response example:
{
  "success": true,
  "data": [...],
  "message": "تم جلب الرحلات بنجاح"
}
```

### Frontend Integration Example

```javascript
// Set language to Arabic
fetch('/api/set-language/ar', { method: 'POST' });

// Frontend responds with RTL layout
const direction = currentLanguage === 'ar' ? 'rtl' : 'ltr';
document.documentElement.dir = direction;
```

### For More Details
- Read [ARABIC_LOCALIZATION.md](ARABIC_LOCALIZATION.md) for complete documentation
- See [LOCALIZATION_DEVELOPER_GUIDE.md](LOCALIZATION_DEVELOPER_GUIDE.md) for development guidelines

## License

This project is proprietary software.

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
