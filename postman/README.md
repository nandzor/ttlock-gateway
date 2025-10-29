# TTLock API Postman Collection

## 📋 Overview

Postman collection lengkap untuk testing TTLock API integration, termasuk authentication, callback handling, dan history management. Collection ini dirancang khusus untuk project TTLock yang menggunakan Laravel framework dengan PostgreSQL database.

## 🚀 Quick Start

### 1. Import Collection dan Environment

1. **Import Collection**: `TTLock_API_Collection.postman_collection.json`
2. **Import Environments**:
   - `TTLock_Local.postman_environment.json` (Local Development)
   - `TTLock_Staging.postman_environment.json` (Staging Environment)  
   - `TTLock_Production.postman_environment.json` (Production Environment)

### 2. Konfigurasi Environment Variables

Update variabel berikut di environment yang dipilih:

#### Required Variables:
- `baseUrl`: API base URL (contoh: `http://localhost:8000`)
- `clientId`: TTLock client ID dari dashboard TTLock
- `clientSecret`: TTLock client secret dari dashboard TTLock
- `username`: Admin username untuk authentication
- `password`: Admin password untuk authentication

#### Optional Variables:
- `testLockId`: Test lock ID untuk testing callback
- `testLockMac`: Test lock MAC address
- `testUsername`: Test username untuk callback
- `testAdmin`: Test admin user

### 3. Authentication Flow

1. **Get Access Token**: Jalankan request "Get Access Token"
2. **Token Auto-Set**: Access token akan otomatis disimpan di environment
3. **Gunakan Authenticated Requests**: Semua request selanjutnya akan menggunakan token

## 📁 Collection Structure

### Authentication
- **Get Access Token**: Mendapatkan OAuth access token untuk API authentication

### TTLock Callbacks
- **Single Callback - Unlock Event**: Test callback unlock dengan fingerprint
- **Single Callback - Battery Low**: Test callback battery low alert
- **Batch Callback - Multiple Events**: Test batch callback (scenario offline sync)

### Callback History
- **Get Callback History**: Ambil paginated callback history dengan filters
- **Get Callback Statistics**: Ambil metrics dan statistics callback

## 🔧 Environment Configuration

### Local Environment
```json
{
  "baseUrl": "http://localhost:8000",
  "environment": "local",
  "debug": "true",
  "timeout": "30000"
}
```

### Staging Environment
```json
{
  "baseUrl": "https://staging-api.yourdomain.com",
  "environment": "staging", 
  "debug": "true",
  "timeout": "30000"
}
```

### Production Environment
```json
{
  "baseUrl": "https://api.yourdomain.com",
  "environment": "production",
  "debug": "false",
  "timeout": "15000"
}
```

## 📊 Sample Payloads

### Single Callback - Unlock Event (Fingerprint)
```json
{
  "lockId": "12345678",
  "lockMac": "AA:BB:CC:DD:EE:FF",
  "admin": "admin_user",
  "notifyType": "1",
  "records": "[{\"recordType\":8,\"success\":1,\"username\":\"user123\",\"keyboardPwd\":\"\",\"lockDate\":1703123456789,\"serverDate\":1703123456789,\"electricQuantity\":85}]",
  "recordTypeFromLock": "20",
  "recordType": "8",
  "success": "1",
  "username": "user123",
  "keyboardPwd": "",
  "lockDate": "1703123456789",
  "serverDate": "1703123456789",
  "electricQuantity": "85"
}
```

### Single Callback - Battery Low
```json
{
  "lockId": "87654321",
  "lockMac": "FF:EE:DD:CC:BB:AA",
  "admin": "admin_user",
  "notifyType": "1",
  "records": "[{\"recordType\":44,\"success\":1,\"username\":\"\",\"keyboardPwd\":\"\",\"lockDate\":1703123456789,\"serverDate\":1703123456789,\"electricQuantity\":15}]",
  "recordTypeFromLock": "44",
  "recordType": "44",
  "success": "1",
  "username": "",
  "keyboardPwd": "",
  "lockDate": "1703123456789",
  "serverDate": "1703123456789",
  "electricQuantity": "15"
}
```

### Batch Callback - Multiple Events (Offline Sync)
```json
{
  "lockId": "11111111",
  "lockMac": "11:22:33:44:55:66",
  "admin": "admin_user",
  "notifyType": "1",
  "records": "[{\"recordType\":8,\"success\":1,\"username\":\"user1\",\"keyboardPwd\":\"\",\"lockDate\":1703123456789,\"serverDate\":1703123456789,\"electricQuantity\":85},{\"recordType\":20,\"success\":1,\"username\":\"user2\",\"keyboardPwd\":\"\",\"lockDate\":1703123516789,\"serverDate\":1703123516789,\"electricQuantity\":82},{\"recordType\":44,\"success\":1,\"username\":\"\",\"keyboardPwd\":\"\",\"lockDate\":1703123576789,\"serverDate\":1703123576789,\"electricQuantity\":15}]",
  "batch_sync": "true"
}
```

## ✅ Test Scripts

### Global Tests
- **Response Time**: Memastikan response time di bawah 5000ms
- **Content Type**: Validasi format JSON response
- **Response Logging**: Log response time untuk monitoring

### Authentication Tests
- **Status Code**: Validasi 200 OK response
- **Access Token**: Memastikan access token ada
- **Error Code**: Validasi errcode adalah 0 (success)
- **Auto-Set Token**: Otomatis set access token di environment

### Callback Tests
- **Status Code**: Validasi 200 OK response
- **Success Flag**: Memastikan success adalah true
- **Callback ID**: Validasi callback_id ada
- **Event Type**: Validasi event_type ada

### History Tests
- **Pagination**: Validasi struktur data pagination
- **Data Array**: Memastikan data adalah array
- **Required Fields**: Validasi field yang diperlukan ada

## 🔍 Filter Examples

### Get Callback History dengan Filters
```
GET /api/v1/ttlock-callback/history?page=1&per_page=10&search=user123&event_type=lock_operation&processed=true&date_from=2024-01-01&date_to=2024-01-31
```

### Query Parameters:
- `page`: Nomor halaman (default: 1)
- `per_page`: Item per halaman (default: 10)
- `search`: Search term (mencari lock_id, lock_mac, username, message)
- `event_type`: Filter berdasarkan event type (lock_operation, battery_low, security_alert)
- `processed`: Filter berdasarkan status processed (true, false)
- `date_from`: Filter dari tanggal (YYYY-MM-DD)
- `date_to`: Filter sampai tanggal (YYYY-MM-DD)

## 📈 Response Examples

### Successful Callback Response
```json
{
  "success": true,
  "message": "Callback processed and saved successfully",
  "data": {
    "callback_id": 123,
    "event_type": "lock_operation",
    "message": "Unlocked via fingerprint",
    "mode": "online"
  }
}
```

### Batch Callback Response
```json
{
  "success": true,
  "message": "Batch callback processed successfully",
  "data": {
    "total_processed": 3,
    "successful": 3,
    "failed": 0,
    "results": [
      {
        "callback_id": 125,
        "event_type": "lock_operation",
        "message": "Unlocked via fingerprint",
        "status": "success"
      }
    ]
  }
}
```

### History Response
```json
{
  "success": true,
  "message": "Callback history retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "lock_id": "12345678",
        "event_type": "lock_operation",
        "message": "Unlocked via fingerprint",
        "record_type_description": "Fingerprint Unlock",
        "battery_level_description": "Normal (85%)",
        "formatted_lock_date": "2024-01-01 12:00:56",
        "created_at": "2024-01-01T12:00:00.000000Z"
      }
    ],
    "total": 50,
    "per_page": 10
  }
}
```

### Statistics Response
```json
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_callbacks": 150,
    "processed_callbacks": 145,
    "unprocessed_callbacks": 5,
    "recent_callbacks_24h": 25,
    "event_type_breakdown": {
      "lock_operation": 120,
      "battery_low": 25,
      "security_alert": 5
    },
    "last_7_days": [
      {"date": "2024-01-01", "count": 25},
      {"date": "2024-01-02", "count": 30}
    ]
  }
}
```

## 🚨 Error Handling

### Common Error Responses
```json
{
  "success": false,
  "message": "Authentication failed",
  "error": {
    "code": "AUTH_FAILED",
    "details": "Invalid credentials"
  }
}
```

### Validation Errors
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "lockId": ["The lock id field is required."],
    "records": ["The records field is required."]
  }
}
```

## 🔐 Security Notes

1. **Environment Variables**: Simpan data sensitif (passwords, tokens) sebagai environment variables
2. **Token Management**: Access tokens dikelola otomatis oleh collection
3. **HTTPS**: Selalu gunakan HTTPS di production environment
4. **Rate Limiting**: Perhatikan API rate limits di production

## 📝 Usage Tips

1. **Start with Authentication**: Selalu dapatkan access token dulu
2. **Use Test Scripts**: Jalankan tests untuk validasi responses
3. **Check Environment**: Pastikan environment yang benar dipilih
4. **Monitor Logs**: Cek console untuk debugging information
5. **Batch Testing**: Gunakan collection runner untuk testing komprehensif

## 🐛 Troubleshooting

### Common Issues:
1. **401 Unauthorized**: Cek access token dan credentials
2. **422 Validation Error**: Verifikasi format request payload
3. **500 Server Error**: Cek server logs dan environment configuration
4. **Timeout**: Tingkatkan timeout value di environment variables

### Debug Steps:
1. Cek environment variables
2. Verifikasi base URL configuration
3. Test authentication endpoint
4. Cek request payload format
5. Review server logs

## 🔧 TTLock Integration Details

### Record Types Mapping
- `8`: Unlock
- `20`: Fingerprint Unlock (recordTypeFromLock)
- `44`: Battery Low
- `45`: Battery Critical
- `46`: Battery Normal
- `47`: Lock Jammed
- `48`: Lock Reset
- `49`: Lock Malfunction
- `50`: Lock Tampered

### Event Types
- `lock_operation`: Lock Operation (unlock, lock, etc.)
- `battery_low`: Battery Low Alert
- `security_alert`: Security Alert

### Callback Processing
1. **Single Callback**: Satu event per request
2. **Batch Callback**: Multiple events dalam satu request (offline sync)
3. **Auto-Detection**: Sistem otomatis detect batch vs single callback
4. **Error Handling**: Setiap event dalam batch diproses individual

## 📊 Database Integration

### TTLock Callback History Table
- `id`: Primary key
- `lock_id`: TTLock device ID
- `lock_mac`: Device MAC address
- `event_type`: Jenis event (lock_operation, battery_low, etc.)
- `record_type`: TTLock record type code
- `record_type_from_lock`: Vendor-specific record type
- `message`: Human-readable event message
- `processed`: Status processing
- `created_at`: Timestamp creation

### Indexes
- `lock_id` + `created_at`
- `record_type` + `created_at`
- `event_type` + `created_at`
- `processed`

## 🚀 Performance Considerations

### Response Time Targets
- **Authentication**: < 2 seconds
- **Single Callback**: < 1 second
- **Batch Callback**: < 5 seconds
- **History Query**: < 3 seconds
- **Statistics**: < 2 seconds

### Rate Limits
- **Local**: No limits
- **Staging**: 100 requests/minute
- **Production**: 60 requests/minute

## 📞 Support

Untuk issues atau pertanyaan:
1. Cek API documentation
2. Review server logs
3. Test dengan environment berbeda
4. Hubungi development team

---

**Last Updated**: 2024-01-01  
**Version**: 1.0.0  
**Compatible with**: TTLock API v3.0+, Laravel 10+, PostgreSQL 13+
