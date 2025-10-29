# TTLock Management System

A comprehensive Laravel-based management system for TTLock smart locks, featuring real-time callback processing, user management, and detailed analytics dashboard.

## 🚀 Features

### Core Functionality
- **TTLock Integration**: Complete integration with TTLock API (https://euopen.ttlock.com/)
- **Real-time Callbacks**: Process and store lock events in real-time
- **User Management**: Full CRUD operations for system users
- **Dashboard Analytics**: Comprehensive statistics and charts
- **Export Capabilities**: PDF and Excel export for callback histories
- **Authentication**: Secure session-based authentication

### TTLock Features
- **Lock Operations**: Remote unlock/lock functionality
- **Event Processing**: Handle various lock events (unlock, battery low, tamper, etc.)
- **OAuth2 Integration**: Secure token management
- **Callback History**: Complete audit trail of all lock activities
- **Multi-format Support**: Support for different record types and vendor codes

## 🛠 Technology Stack

- **Framework**: Laravel 12.x
- **Database**: PostgreSQL
- **Frontend**: Blade templates with Tailwind CSS
- **Authentication**: Laravel Sanctum
- **Queue Management**: Laravel Horizon
- **Export**: PHPSpreadsheet & DomPDF
- **Charts**: Chart.js
- **Cache**: Redis (Predis)

## 📋 Requirements

- PHP 8.2+
- PostgreSQL 12+
- Redis 6+
- Composer
- Node.js & NPM

## 🚀 Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd ttlock
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration
Update `.env` with your database credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ttlock
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 5. TTLock API Configuration
Add TTLock credentials to `.env`:
```env
TTLOCK_CLIENT_ID=your_client_id
TTLOCK_CLIENT_SECRET=your_client_secret
TTLOCK_USERNAME=your_username
TTLOCK_PASSWORD=your_password
\n+# Static API Token for protected endpoints (required)
API_STATIC_TOKEN=your_static_token_here
```

### 6. Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### 7. Build Assets
```bash
npm run build
```

### 8. Start Development Server
```bash
php artisan serve
```

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   ├── Api/V1/                 # API Controllers
│   │   ├── TTLockCallbackController.php
│   │   ├── TTLockOAuthController.php
│   │   ├── TTLockLockController.php
│   │   └── UserController.php
│   ├── AuthController.php      # Web Authentication
│   ├── DashboardController.php # Dashboard
│   └── TTLockCallbackHistoryController.php
├── Models/
│   ├── TTLockCallbackHistory.php
│   └── User.php
├── Services/
│   ├── BaseService.php         # Reusable service base
│   ├── TTLockService.php       # TTLock API integration
│   └── TTLockCallbackHistoryService.php
└── Exports/
    └── TTLockCallbackHistoryExport.php

resources/views/
├── dashboard/                  # Dashboard views
├── ttlock-callback-history/   # History management
└── layouts/                   # Layout templates

routes/
├── web.php                    # Web routes
└── api_v1.php                # API routes
```

## 🔌 API Endpoints

### TTLock Callbacks
- `POST /api/v1/ttlock-callback` - Receive lock callbacks
- `GET /api/v1/ttlock-callback/history` - Get callback history (Protected by static token)
- `GET /api/v1/ttlock-callback/statistics` - Get statistics (Protected by static token)

### TTLock Operations
- `POST /api/v1/ttlock/oauth/token` - Get OAuth token
- `POST /api/v1/ttlock/oauth/refresh` - Refresh token
- `POST /api/v1/ttlock/lock/unlock` - Remote unlock (Protected by static token)
- `POST /api/v1/ttlock/lock/lock` - Remote lock (Protected by static token)
- `GET /api/v1/ttlock/lock/status` - Get lock status (Protected by static token)

### User Management
- `GET /api/v1/users` - List users
- `POST /api/v1/users` - Create user
- `GET /api/v1/users/{id}` - Show user
- `PUT /api/v1/users/{id}` - Update user
- `DELETE /api/v1/users/{id}` - Delete user

## 🎯 Web Interface

### Dashboard
- **Statistics Cards**: Total users, callbacks, processed/unprocessed
- **Recent Events**: Last 5 TTLock events
- **Charts**: Last 7 days callback trends
- **Today Overview**: Current day statistics

### Callback History Management
- **Filtering**: By lock ID, event type, record type, processed status, date range
- **Search**: Full-text search across lock data
- **Export**: PDF and Excel export with filters
- **Pagination**: Server-side pagination with customizable per-page options

## 🔧 Configuration

### TTLock API Integration
The system integrates with TTLock's European API (https://euopen.ttlock.com/) and supports:

- **Record Types**: Standard and vendor-specific codes
- **Event Types**: Lock operations, passcode, card, fingerprint, battery, tamper alerts
- **OAuth2**: Secure token management with automatic refresh
- **Callbacks**: Real-time event processing
- **Static Token Middleware**: Protected routes require `Authorization: Bearer <API_STATIC_TOKEN>`

### Postman Collection
- Collection: `postman/TTLock_API_Collection.postman_collection.json`
- Environments: `postman/TTLock_Local.postman_environment.json`, `postman/TTLock_Staging.postman_environment.json`, `postman/TTLock_Production.postman_environment.json`
- Set `API_STATIC_TOKEN` in the selected environment; protected requests already include `Authorization: Bearer {{API_STATIC_TOKEN}}`.

### Supported Lock Events
- **Lock Operations**: Unlock/Lock via various methods
- **Passcode Operations**: Keypad entry events
- **Card Operations**: RFID card events
- **Fingerprint Operations**: Biometric unlock events
- **Battery Alerts**: Low battery notifications
- **Tamper Alerts**: Security breach notifications
- **Gateway Status**: Online/offline status

## 📊 Data Management

### Callback History
All TTLock callbacks are stored in `ttlock_callback_history` table with:
- Lock identification (ID, MAC address)
- Event details (type, message, timestamps)
- User information (username, admin)
- Raw data and processing status
- Battery levels and security alerts

### Export Features
- **PDF Export**: Formatted reports with filters
- **Excel Export**: Spreadsheet format with all data
- **Filtered Exports**: Apply same filters as web interface
- **Dynamic Filenames**: Timestamped export files

## 🚀 Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

### Queue Processing
```bash
php artisan horizon
```

### Cache Management
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🔒 Security

- **Authentication**: Session-based with Laravel Sanctum
- **CSRF Protection**: Built-in Laravel CSRF tokens
- **Input Validation**: Comprehensive request validation
- **SQL Injection**: Eloquent ORM protection
- **XSS Protection**: Blade template escaping

## 📈 Monitoring

- **Laravel Horizon**: Queue monitoring and management
- **Logging**: Comprehensive application logging
- **Error Tracking**: Built-in error handling and reporting
- **Performance**: Optimized queries and caching

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License.

## 📞 Support

For support and questions, please contact the development team or create an issue in the repository.

---

**Built with ❤️ using Laravel and TTLock API**
