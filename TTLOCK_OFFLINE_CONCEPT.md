# TTLock Offline Concept - Penjelasan Lengkap

## 🤔 **Pertanyaan Umum:**
"Ketika gateway TTLock internetnya mati, data disimpan di mana?"

## ✅ **Jawaban:**
**Data disimpan di GATEWAY TTLOCK itu sendiri!** Bukan di server kita.

## 🏗️ **Arsitektur TTLock Offline**

### **1. Komponen Sistem TTLock:**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   TTLock Lock   │    │ TTLock Gateway  │    │ TTLock Cloud    │
│   (Hardware)    │◄──►│   (Hardware)    │◄──►│   (Server)      │
│                 │    │                 │    │                 │
│ - Fingerprint   │    │ - Local Storage │    │ - User Data     │
│ - Keypad        │    │ - Lock Logs     │    │ - Lock Records  │
│ - Bluetooth     │    │ - Events        │    │ - Callbacks     │
│ - Battery       │    │ - User Data     │    │ - API Endpoints │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **2. Alur Data Normal (Online):**
```
Lock Event → Gateway → TTLock Cloud → Our Server
     ↓           ↓           ↓            ↓
  User opens  Gateway    Cloud stores  We receive
  door via    forwards   data & sends  callback &
  fingerprint data       callback      process it
```

### **3. Alur Data Offline (Internet Mati):**
```
Lock Event → Gateway → [STORED LOCALLY] → [WAIT FOR INTERNET]
     ↓           ↓            ↓                    ↓
  User opens  Gateway    Data stored in      When internet
  door via    stores     gateway memory      returns, auto-sync
  fingerprint locally    (NOT in our server) to TTLock cloud
```

### **4. Alur Data Sync (Internet Kembali) - Dua Kemungkinan:**

#### **Yang Tidak Diketahui dari Dokumentasi TTLock:**
```
Gateway ──► TTLock Cloud ──► Our Server
   │              │              │
   │ Sync Data:   │ Callback:     │ Process:
   │ - Events     │ - Format?     │ - Individual?
   │ - Offline    │ - Individual? │ - Batch?
   │ - Period     │ - Batch?      │ - Unknown
   │              │ - Unknown     │ - Unknown
```

**❌ Dokumentasi TTLock TIDAK menjelaskan:**
- Format callback saat offline sync
- Individual vs batch behavior  
- Timing callback delivery
- Data structure yang dikirim

## 🔧 **Cara Kerja Gateway TTLock Offline**

### **Ketika Internet Mati:**
1. **Gateway TTLock** tetap berfungsi normal
2. **Lock operations** tetap tercatat di gateway
3. **Data disimpan di memori internal gateway**
4. **User bisa tetap buka kunci** via Bluetooth
5. **Server kita TIDAK menerima data** (karena internet mati)

### **Ketika Internet Kembali:**
1. **Gateway otomatis sync** data ke TTLock cloud
2. **TTLock cloud** mengirim callback ke server kita
3. **Server kita** menerima dan memproses callback
4. **Data yang "hilang"** selama offline muncul kembali

### **Callback Behavior (Tidak Dijelaskan di Dokumentasi TTLock):**
- **Dokumentasi TTLock TIDAK menjelaskan** mekanisme callback saat offline sync
- **Tidak ada informasi resmi** tentang individual vs batch behavior
- **Yang diketahui**: Gateway sync data ke TTLock cloud, lalu cloud kirim callback ke server kita
- **Volume**: Events yang terjadi saat offline (jumlah tidak pasti)
- **Speed**: Callback datang setelah internet pulih (timing tidak pasti)
- **Content**: Events yang terjadi saat offline (format tidak pasti)

## 📊 **Implementasi di Sistem Kita**

### **1. Offline Sync Service:**
```php
// Ini untuk sync data yang sudah ada di TTLock cloud
// Bukan untuk menyimpan data offline
public function syncLockRecordsFromAPI($lockId, $lastUpdateDate)
{
    // Mengambil data yang sudah di-sync gateway ke TTLock cloud
    // Data ini adalah data yang tersimpan di gateway saat offline
}
```

### **2. Callback Controller:**
```php
// Ini menerima callback dari TTLock cloud
// Callback ini berisi data yang sudah di-sync dari gateway
public function callback(Request $request)
{
    // Menerima data yang sudah di-sync dari gateway
    // Bukan menyimpan data offline
}
```

## 🎯 **Kesimpulan Penting**

### **❌ Yang TIDAK Benar:**
- Data offline disimpan di server kita
- Kita perlu menyimpan data offline sendiri
- Gateway tidak punya storage

### **✅ Yang BENAR:**
- Data offline disimpan di **Gateway TTLock**
- Gateway punya **local storage/memory**
- Ketika internet kembali, gateway **otomatis sync** ke TTLock cloud
- TTLock cloud mengirim **callback** ke server kita
- Server kita **menerima dan memproses** callback

## 🔄 **Skenario Lengkap:**

### **Skenario 1: Internet Normal**
```
1. User buka kunci via fingerprint
2. Gateway forward ke TTLock cloud
3. TTLock cloud kirim callback ke server kita
4. Server kita proses dan simpan ke database
```

### **Skenario 2: Internet Mati**
```
1. User buka kunci via fingerprint
2. Gateway simpan di local memory
3. Server kita TIDAK terima data
4. Data "hilang" dari perspektif server kita
```

### **Skenario 3: Internet Kembali**
```
1. Gateway otomatis sync ke TTLock cloud
2. TTLock cloud kirim callback ke server kita
3. Server kita terima callback (data "hilang" muncul)
4. Server kita proses dan simpan ke database
```

## 🛠️ **Fungsi Sistem Offline Sync Kita**

### **Bukan untuk:**
- Menyimpan data offline (gateway sudah handle)
- Menggantikan fungsi gateway

### **Untuk:**
- **Monitoring**: Cek status sync gateway
- **Recovery**: Sync data yang mungkin terlewat
- **Management**: Kelola data yang sudah di-sync
- **Analytics**: Analisis data sync

## 📱 **Praktis:**

### **Yang Perlu Dilakukan:**
1. **Pastikan gateway TTLock** terhubung internet
2. **Monitor callback** dari TTLock cloud
3. **Sync data** jika ada yang terlewat
4. **Troubleshoot** jika callback tidak masuk

### **Yang TIDAK Perlu:**
1. Menyimpan data offline sendiri
2. Menggantikan fungsi gateway
3. Mengelola storage offline

---

**Intinya: Gateway TTLock sudah handle offline storage. Sistem kita hanya perlu menerima dan memproses data yang sudah di-sync gateway ke TTLock cloud.**
