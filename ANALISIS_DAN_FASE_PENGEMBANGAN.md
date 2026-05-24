# ANALISIS DAN FASE PENGEMBANGAN
## Aplikasi Monitoring Aktivitas dan Kinerja Sales Force
### PT. Tridaya Sakti Medima

**Dokumen:** Analisis Teknis dan Rencana Fase Pengembangan  
**Versi:** 1.0  
**Tanggal:** Maret 2026  
**Status:** Dokumen Kerja

---

## DAFTAR ISI

1. [Analisis PRD](#analisis-prd)
2. [Gap Analysis](#gap-analysis)
3. [Peta Jalan Implementasi](#peta-jalan-implementasi)
4. [Fase-Fase Pengembangan Terstruktur](#fase-fase-pengembangan-terstruktur)
5. [Dependency & Critical Path](#dependency--critical-path)
6. [Strategi Testing](#strategi-testing)
7. [Risk Management](#risk-management)
8. [Deliverables per Fase](#deliverables-per-fase)

---

## ANALISIS PRD

### 1.1 Ringkasan Produk

**Tujuan Sistem:**
- Monitoring real-time aktivitas sales force
- Validasi kunjungan berbasis GPS dan foto
- Pelaporan kinerja otomatis
- Eliminasi ghost check-in dan meningkatkan akurasi data

**Scope Fungsional:**
- 33 fitur utama terbagi dalam 8 modul
- 10 tabel database dengan relasi kompleks
- 2 antarmuka utama (Mobile Sales & Desktop Manager/Admin)
- Prioritas: 22 Tinggi, 8 Sedang, 1 Rendah

**Target Pengguna:**
- Sales (via mobile web)
- Manager/Pimpinan (via desktop - read-only)
- Admin (via desktop - data management)
- Super Admin (via desktop - system config)

### 1.2 Analisis Teknis

| Aspek | Evaluasi | Komentar |
|-------|----------|---------|
| **Kompleksitas** | Tinggi-Sedang | Integrasi GPS, foto, dan real-time updates memerlukan perhatian khusus |
| **Tech Stack** | Solid | Laravel 12 + MySQL standar industri; library pilihan proven |
| **Database** | Well-designed | Relasi jelas, normalisasi baik, support scalability |
| **Security** | Adequate | Middleware role + Spatie Permission; perlu hardening GPS data |
| **Scalability** | Terbatas | Design mendukung 50 user simultan; butuh optimization untuk growth |
| **Testing** | Partial | PRD menyebut UAT minggu 10, perlu unit test lebih awal |

### 1.3 Strengths (Kekuatan)

✅ **Tech Stack Modern:**
- Laravel 12 dengan dokumentasi lengkap
- Ecosystem library matang (Breeze, Spatie, DataTables, DomPDF)
- Openness untuk customization

✅ **Database Design Solid:**
- Tabel terstruktur dengan relasi jelas
- Mendukung audit trail (timestamps)
- Presisi GPS mencukupi (6 desimal = ~0.1m accuracy)

✅ **Clear Functional Requirements:**
- 33 fitur terdefinisi spesifik
- User stories implisit dalam description
- Acceptance criteria terukur

✅ **Risk Awareness:**
- PRD sudah mengidentifikasi 6 risiko kritis
- Mitigasi sudah direncanakan

### 1.4 Weaknesses (Kelemahan)

⚠️ **Non-Functional Requirements Vague:**
- Performa "≤ 3 detik" tidak dispesifikasi untuk edge case
- Skalabilitas "50 user simultan" sangat konservatif
- Tidak ada SLA downtime yang jelas

⚠️ **Sprint Plan Linear:**
- Minggu 1-2 heavily sequential
- Risiko delay karena dependencies
- Testing hanya di minggu 10 (terlalu akhir)

⚠️ **Mobile Considerations Limited:**
- Offline-first strategy hanya disebutkan di Risk mitigation
- Tidak ada caching strategy detail
- Battery consumption tidak dibahas

⚠️ **Integration Points Undefined:**
- Export Excel/PDF format tidak detail
- Email notification system TBD
- Alert mechanism belum spesifik (realtime push?)

---

## GAP ANALYSIS

### Kelanjutan dari PRD

| Gap | Impact | Rekomendasi |
|-----|--------|-------------|
| **Offline Mode** | High | Implementasikan Service Worker + IndexedDB untuk sync saat online |
| **Push Notification** | Medium | Gunakan WebSocket + Server-Sent Events (SSE) atau polling fallback |
| **Image Optimization** | Medium | Compress foto saat upload; implement lazy loading di dashboard |
| **API Rate Limiting** | High | Add throttle untuk GPS tracking query |
| **Audit Logging** | Medium | Log semua aksi user untuk compliance & security forensics |
| **Mobile Performance** | High | Implement PWA; test di bandwidth 3G/4G; optimize JS bundle |
| **Data Retention Policy** | Medium | Tentukan berapa lama data foto/GPS disimpan |
| **Backup & Recovery** | High | Strategy backup database & foto; disaster recovery plan |

---

## PETA JALAN IMPLEMENTASI

```
FASE FOUNDATIONAL (Minggu 1-2)
    ↓
FASE DATA & AUTHENTICATION (Minggu 2-3)
    ↓
FASE CORE FEATURES (Minggu 4-7)
    ├── PJP Management
    ├── Attendance System
    ├── GPS Validation & Kunjungan
    └── Visit Form
    ↓
FASE DASHBOARD & ANALYTICS (Minggu 8-9)
    ├── Real-time Monitoring
    └── Reporting & Export
    ↓
FASE QA & HARDENING (Minggu 9-10)
    ├── Unit Testing
    ├── Integration Testing
    ├── UAT
    └── Performance Optimization
    ↓
LAUNCH READINESS
```

---

## FASE-FASE PENGEMBANGAN TERSTRUKTUR

### FASE 0: PRE-DEVELOPMENT SETUP (0.5 Minggu)

**Objektif:**
- Setup environment & tooling
- Database design finalization
- Architecture review
- Team alignment

**Aktivitas:**
1. **Environment Setup**
   - Setup local development (XAMPP/Wampserver/Docker)
   - Git repository & branching strategy
   - Laravel project initialization
   - Package manager configuration

2. **Database Refinement**
   - Finalize schema dari PRD
   - Create migration files
   - Define indexes & constraints
   - Setup backup strategy

3. **Architecture Decision**
   - Confirm Service Layer implementation
   - Define API response format
   - Establish error handling convention
   - Document coding standards (PSR-12)

**Deliverables:**
- ✓ Development environment  ready
- ✓ Database migrations created
- ✓ Boilerplate structure (routes, controllers, models)
- ✓ Architecture decision document

**Dependencies:** None  
**Duration:** 3-4 hari kerja  
**Owner:** Tech Lead + DevOps

---

### FASE 1: FOUNDATION & AUTHENTICATION (Minggu 1-2)

**Objektif:**
- Establish application foundation
- Implement secure authentication
- Setup role-based access control
- Create UI templates (desktop & mobile)

**Aktivitas:**

#### 1.1 Project Bootstrap
- Install Laravel 12 framework
- Integrate Spatie Laravel Permission
- Configure Laravel Breeze (session-based auth)
- Setup CORS & security headers

#### 1.2 Authentication Module (F-01, F-02, F-03, F-04)
| Fitur | Tasks |
|-------|-------|
| **Login** | Create login view + controller; test session handling |
| **Role Management** | Setup roles (Super Admin, Admin, Manager, Sales); assign permissions |
| **Password Management** | Create change-password view & logic; implement email verification |
| **Logout** | Implement secure session destruction |

**Key Implementation:**
```php
// Role & Permission Setup
- Super Admin: full access
- Admin: PJP + klien management, verifikasi laporan
- Manager: dashboard + laporan (read-only)
- Sales: absensi + check-in/out + form
```

#### 1.3 UI Templates
- **Desktop Layout** (`layouts/app.blade.php`)
  - Bootstrap 5 responsive grid
  - Sidebar navigation
  - Top bar user menu
  - Alert/notification toasts

- **Mobile Layout** (`layouts/mobile.blade.php`)
  - Full-width design
  - Large touch buttons (44x44px minimum)
  - Hamburger navigation
  - Bottom action bar

#### 1.4 Middleware & Guards
- Create RoleMiddleware untuk route protection
- Implement policy-based access control
- Setup auth redirects

**Testing Strategy:**
- Unit test: Auth controller methods
- Feature test: Login/logout flows (3 user roles)
- Manual test: Session persistence across requests

**Deliverables:**
- ✓ Authentication system functional
- ✓ Role-based access working
- ✓ UI templates responsive
- ✓ 8 test cases passed (auth flows)

**Duration:** 1.5 minggu  
**Owner:** Backend Dev + Frontend Dev  
**Risk:** Session timeout issues → implement session configurator

---

### FASE 2: MASTER DATA MANAGEMENT (Minggu 3)

**Objektif:**
- Implement CRUD operations untuk data master
- Setup server-side pagination dengan DataTables
- Integrate GPS mapping for klien locations
- Implement validation rules

**Aktivitas:**

#### 2.1 User Management (F-30)
```
Routes:
  GET  /admin/users                 → List users (DataTables)
  GET  /admin/users/{id}/edit       → Edit form
  POST /admin/users                 → Create
  PUT  /admin/users/{id}            → Update
  DELETE /admin/users/{id}          → Delete (soft delete)
  POST /admin/users/{id}/reset-pwd  → Reset password
```

**Validation Rules:**
```
name: required|string|max:100
email: required|email|unique:users,email
phone: nullable|numeric|digits:10,12
wilayah_id: required|exists:wilayah
is_active: boolean
```

#### 2.2 Klien/Toko Management (F-31)
```
Routes:
  GET    /admin/klien              → List + map preview
  GET    /admin/klien/create       → Create form with Leaflet map
  POST   /admin/klien              → Store
  GET    /admin/klien/{id}/edit    → Edit form
  PUT    /admin/klien/{id}         → Update
  DELETE /admin/klien/{id}         → Delete
```

**GPS Input Implementation:**
- Interactive Leaflet map for coordinate selection
- Reverse geocoding (OpenStreetMap Nominatim API)
- Manual coordinate input as fallback
- GPS validation: latitude -90 to 90, longitude -180 to 180

**Data Structure:**
```php
$klien = [
    'nama_klien' => 'Apotek Sehat',
    'kategori' => 'apotek', // enum
    'alamat' => 'Jl. Merdeka No. 123',
    'wilayah_id' => 1,
    'latitude' => -2.9760971,   // 7 decimal precision
    'longitude' => 104.7553750,
    'contact_person' => 'Ibu Siti',
    'phone' => '081234567890',
    'is_active' => true
];
```

#### 2.3 Wilayah Management (F-32)
- Simple CRUD for geographic areas
- Dropdown in user & klien forms
- Used for filtering & reporting

#### 2.4 Configuration Panel
- GPS radius tolerance (default 100m, configurable)
- Session timeout
- Export format preferences

**Frontend Components:**
- Yajra DataTables server-side rendering
- Modal forms untuk CRUD
- Leaflet.js interactive map
- Bootstrap validation feedback

**Testing Strategy:**
- Unit: Model relationships (User → Wilayah, Klien → Wilayah)
- Feature: CRUD operations with validation
- Manual GPS: Input koordinat & verify di map

**Deliverables:**
- ✓ User CRUD + role assignment functional
- ✓ Klien CRUD with GPS mapping
- ✓ Wilayah management complete
- ✓ DataTables integration for list views
- ✓ 15 test cases passed

**Duration:** 1 minggu  
**Owner:** Backend Dev + Frontend Dev  
**Risk:** GPS accuracy issues → add manual override option

---

### FASE 3: ATTENDANCE & SCHEDULING (Minggu 4-5)

**Objektif:**
- Implement daily attendance system
- Create PJP (Jadwal Kunjungan) scheduling
- Build sales dashboard untuk jadwal
- GPS recording for attendance

**Aktivitas:**

#### 3.1 Absensi System (F-05, F-06, F-07)

**Check-In Flow:**
```
Sales → Tap "Check-In" Button
  ↓
System captures:
  - Current timestamp (server-side)
  - GPS coordinates (latitude, longitude)
  - Accuracy meter (GPS device accuracy)
  ↓
Create absensi record:
  INSERT INTO absensi (user_id, tanggal, waktu_masuk, lat_masuk, lng_masuk, created_at)
  ↓
Show success message + current time
```

**Check-Out Flow:**
```
Sales → Tap "Check-Out" Button (end of day)
  ↓
Find today's absensi record (WHERE user_id = X AND tanggal = TODAY())
  ↓
UPDATE absensi SET waktu_keluar = NOW(), lat_keluar = GPS_LAT, lng_keluar = GPS_LNG
  ↓
Auto-calculate: total_jam = (waktu_keluar - waktu_masuk) / 60 minutes
  ↓
Show daily summary (total hours, locations)
```

**Routes:**
```
POST   /sales/attendance/checkin   → Check-in
POST   /sales/attendance/checkout  → Check-out
GET    /sales/attendance/daily     → View today's status
GET    /admin/attendance/recap     → Recap view (filtered by date)
```

#### 3.2 PJP (Jadwal Kunjungan) Management (F-08, F-09, F-10, F-11)

**Admin Creates Schedule:**
```
Routes:
  GET    /admin/pjp                  → List schedules
  GET    /admin/pjp/create           → Create form
  POST   /admin/pjp                  → Store
  GET    /admin/pjp/{id}/edit        → Edit
  PUT    /admin/pjp/{id}             → Update
  POST   /admin/pjp/{id}/add-klien   → Add klien to schedule
```

**PJP Structure:**
```php
// jadwal_kunjungan table
$jadwal = [
    'user_id' => 1,           // Sales assigned
    'tanggal' => '2026-03-20',
    'keterangan' => 'Klien area timur',
    'status' => 'pending',    // pending → aktif → selesai
    'created_by' => 2         // Admin who created
];

// jadwal_klien (pivot) - ordered list
$jadwalKlien = [
    ['jadwal_id' => 1, 'klien_id' => 5, 'urutan' => 1, 'status' => 'pending'],
    ['jadwal_id' => 1, 'klien_id' => 8, 'urutan' => 2, 'status' => 'pending'],
    ['jadwal_id' => 1, 'klien_id' => 12, 'urutan' => 3, 'status' => 'pending'],
];
```

**Sales Views Schedule (F-09):**
```
GET /sales/pjp/today    → Display list of klien to visit today
  - Show: klien name, address, urutan, status
  - Sorted by urutan (priority order)
  - Status indicator: pending vs completed
```

**Sales Starts Journey (F-10):**
```
POST /sales/pjp/{id}/mulai-perjalanan
  - Update jadwal status: pending → aktif
  - Record start timestamp
  - Enable check-in functionality
```

**Testing Strategy:**
- Unit: Absensi time calculation (check-out - check-in)
- Unit: PJP validation (ensure sales assigned, klien valid)
- Feature: Full flow (check-in → start journey → check-in kunjungan)
- Manual: GPS recording accuracy in different locations

**Deliverables:**
- ✓ Absensi check-in/out working
- ✓ PJP creation & assignment
- ✓ Sales can view today's schedule
- ✓ GPS location recording
- ✓ 12 test cases passed

**Duration:** 1.5 minggu  
**Owner:** Backend Dev + Frontend Dev  
**Risk:** GPS inaccuracy → fallback manual entry with manager approval

---

### FASE 4: CORE KUNJUNGAN LOGIC (Minggu 6-7)

**Objektif:**
- Implement GPS validation engine
- Check-in/Check-out logic with validation
- Photo capture from camera only
- Visit Form integration
- Server-side distance calculation

**Aktivitas:**

#### 4.1 GPS Validation Service (F-12, F-13)

**Service: `GpsValidationService`**
```php
/**
 * Calculate distance between two GPS coordinates
 * using Haversine formula
 * 
 * @param float $lat1, $lng1 - Current GPS
 * @param float $lat2, $lng2 - Target GPS
 * @return float distance in meters
 */
public function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    const EARTH_RADIUS = 6371000; // meters
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng/2) * sin($dLng/2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return EARTH_RADIUS * $c;
}

/**
 * Validate if current location within acceptable radius
 */
public function validateCheckIn($currentLat, $currentLng, $targetLat, $targetLng, $toleranceMeters = 100)
{
    $distance = $this->calculateDistance($currentLat, $currentLng, $targetLat, $targetLng);
    
    return [
        'valid' => $distance <= $toleranceMeters,
        'distance' => round($distance, 2),
        'message' => $distance > $toleranceMeters 
            ? "Lokasi Anda {$distance}m dari target. Min {$toleranceMeters}m."
            : "Lokasi valid. Silahkan check-in."
    ];
}
```

#### 4.2 Check-In Flow (F-12, F-13, F-14)

**Endpoint:**
```
POST /sales/kunjungan/{jadwal_klien_id}/checkin
```

**Process:**
```
1. Receive JSON:
   {
     "lat": -2.9760971,
     "lng": 104.7553750,
     "accuracy": 8.5
   }

2. Get target klien GPS dari jadwal_klien_id

3. Validate distance:
   - Use GpsValidationService
   - If distance > 100m:
     * Return 400 error + distance
     * Show warning on frontend
     * Allow salesperson to retry or request override
   - If distance <= 100m: proceed

4. Create kunjungan record:
   INSERT INTO kunjungan (
     user_id,
     klien_id,
     jadwal_klien_id,
     tanggal,
     waktu_checkin,
     lat_checkin,
     lng_checkin,
     jarak_meter,
     foto_bukti  ← empty for now
   )

5. Response: 
   {
     "success": true,
     "kunjungan_id": 123,
     "message": "Check-in berhasil. Silahkan upload foto."
   }
```

#### 4.3 Photo Capture (F-14)

**Frontend Implementation:**
```html
<!-- Camera input - NO FILE UPLOAD FROM GALLERY -->
<input type="file" 
       id="camera-input" 
       accept="image/*" 
       capture="environment">

<!-- Block gallery access via JavaScript -->
<script>
const fileInput = document.getElementById('camera-input');

fileInput.addEventListener('change', (e) => {
  const file = e.target.files[0];
  
  // Validate file is recent (taken now, not from gallery)
  const fileTime = file.lastModified;
  const currentTime = Date.now();
  
  if (currentTime - fileTime > 60000) { // 60 second tolerance
    alert('Foto harus diambil langsung dari kamera, bukan dari galeri!');
    e.target.value = '';
    return;
  }
  
  // Proceed with upload
  uploadPhoto(file);
});

async function uploadPhoto(file) {
  const formData = new FormData();
  const kunjunganId = document.querySelector('[data-kunjungan-id]').dataset.kunjunganId;
  
  formData.append('photo', file);
  
  const response = await fetch(`/sales/kunjungan/${kunjunganId}/photo`, {
    method: 'POST',
    body: formData,
    headers: {'X-CSRF-TOKEN': csrfToken}
  });
  
  if (response.ok) {
    const data = await response.json();
    // Store photo path to kunjungan record
    document.location = data.nextStep; // Go to visit form
  }
}
</script>
```

**Server Handler:**
```php
// KunjunganController@uploadPhoto
public function uploadPhoto(Request $request, $kunjunganId)
{
    $request->validate(['photo' => 'required|image|mimes:jpeg,png|max:5120']);
    
    $kunjungan = Kunjungan::findOrFail($kunjunganId);
    
    // Store photo to storage/app/kunjungan/{year}/{month}/
    $path = $request->file('photo')->store(
        'kunjungan/' . date('Y/m'),
        'public'
    );
    
    $kunjungan->update(['foto_bukti' => $path]);
    
    return response()->json([
        'success' => true,
        'nextStep' => route('sales.kunjungan.visit-form', $kunjunganId)
    ]);
}
```

**Storage Configuration:**
- Photos stored in `storage/app/public/kunjungan/YYYY/MM/`
- Symlink: `php artisan storage:link`
- Access: `/storage/kunjungan/2026/03/file.jpg`

#### 4.4 Check-Out & Duration Calculation (F-15)

**Endpoint:**
```
POST /sales/kunjungan/{id}/checkout
```

**Process:**
```php
public function checkout(Request $request, $id)
{
    $kunjungan = Kunjungan::findOrFail($id);
    
    // Validate check-out only if check-in exists
    if (!$kunjungan->waktu_checkin) {
        return response()->json(['error' => 'Check-in tidak ditemukan'], 422);
    }
    
    // Capture GPS saat check-out
    $kunjungan->update([
        'waktu_checkout' => now(),
        'lat_checkout' => $request->lat,
        'lng_checkout' => $request->lng,
        // Calculate duration in minutes
        'durasi_menit' => $this->calculateDuration(
            $kunjungan->waktu_checkin,
            now()
        )
    ]);
    
    // Update jadwal_klien status
    $kunjungan->jadwalKlien->update(['status' => 'dikunjungi']);
    
    return response()->json(['success' => true, 'durasi' => $kunjungan->durasi_menit]);
}

private function calculateDuration($checkIn, $checkOut)
{
    return $checkOut->diffInMinutes($checkIn);
}
```

#### 4.5 Visit Form (F-16, F-17, F-18)

**Routes:**
```
GET  /sales/kunjungan/{id}/visit-form    → Display form
POST /sales/kunjungan/{id}/visit-form    → Save form
```

**Visit Form Structure:**
```php
// visit_form table
$visitForm = [
    'kunjungan_id' => 123,                    // 1:1 relationship
    'status_kunjungan' => 'order',            // enum: order, followup, tutup, stok_ada, lainnya
    'catatan' => 'Stok rapi, owner antusias',
    'kondisi_stok' => 'aman',                 // enum: kosong, menipis, aman, berlebih
    'nominal_order' => 750000,                // nullable Rp
];
```

**Form View:**
```html
<form method="POST" action="/sales/kunjungan/{{ $kunjungan->id }}/visit-form">
  @csrf
  
  <div class="form-group mb-3">
    <label>Status Kunjungan *</label>
    <select name="status_kunjungan" class="form-control" required>
      <option value="">-- Pilih Status --</option>
      <option value="order">Order Diterima</option>
      <option value="followup">Follow-up</option>
      <option value="tutup">Toko Tutup</option>
      <option value="stok_ada">Stok Masih Ada</option>
      <option value="lainnya">Lainnya</option>
    </select>
  </div>
  
  <div class="form-group mb-3">
    <label>Catatan Lapangan</label>
    <textarea name="catatan" class="form-control" rows="4" placeholder="Tulis observasi lapangan..."></textarea>
  </div>
  
  <div class="form-group mb-3">
    <label>Kondisi Stok</label>
    <select name="kondisi_stok" class="form-control">
      <option value="">-- Pilih Kondisi --</option>
      <option value="kosong">Kosong</option>
      <option value="menipis">Menipis</option>
      <option value="aman">Aman</option>
      <option value="berlebih">Berlebih</option>
    </select>
  </div>
  
  <div class="form-group mb-3" id="nominal-order-group" style="display:none;">
    <label>Nominal Order (Rp)</label>
    <input type="number" name="nominal_order" class="form-control" placeholder="0">
  </div>
  
  <button type="submit" class="btn btn-primary w-100">Simpan & Selesai</button>
</form>

<script>
// Show nominal_order input only if status = 'order'
document.querySelector('select[name="status_kunjungan"]').addEventListener('change', (e) => {
  const nominalGroup = document.getElementById('nominal-order-group');
  nominalGroup.style.display = e.target.value === 'order' ? 'block' : 'none';
});
</script>
```

**Save Handler:**
```php
public function saveVisitForm(Request $request, $kunjunganId)
{
    $validated = $request->validate([
        'status_kunjungan' => 'required|in:order,followup,tutup,stok_ada,lainnya',
        'catatan' => 'nullable|string|max:1000',
        'kondisi_stok' => 'nullable|in:kosong,menipis,aman,berlebih',
        'nominal_order' => 'nullable|numeric|min:0'
    ]);
    
    $kunjungan = Kunjungan::findOrFail($kunjunganId);
    
    VisitForm::create([
        'kunjungan_id' => $kunjunganId,
        ...$validated
    ]);
    
    // Update kunjungan.is_sesuai_jadwal
    $isSesuaiJadwal = $kunjungan->jadwalKlien?->jadwal?->status === 'aktif';
    $kunjungan->update(['is_sesuai_jadwal' => $isSesuaiJadwal]);
    
    return response()->redirect(route('sales.dashboard'))
        ->with('success', 'Kunjungan berhasil disimpan!');
}
```

#### 4.6 Offline Sync (Risk Mitigation)

**Frontend Strategy:**
```javascript
// Service Worker caching + IndexedDB
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js');
}

// Before submitting visit form, check connection
async function submitVisitForm(data) {
  try {
    const response = await fetch('/sales/kunjungan/' + data.id + '/visit-form', {
      method: 'POST',
      body: JSON.stringify(data)
    });
    
    if (response.ok) {
      // Clear cached data
      indexedDB.deleteObject('pendingKunjungan', data.id);
      showSuccess('Data sinkron ke server');
    }
  } catch (error) {
    // No internet - save to IndexedDB
    indexedDB.putObject('pendingKunjungan', data);
    showWarning('Offline - akan sinkron saat ada internet');
  }
}

// Background sync saat online kembali
window.addEventListener('online', () => {
  syncPendingData();
});
```

**Testing Strategy:**
- Unit: Haversine distance formula (verify against known coordinates)
- Unit: Duration calculation
- Feature: Complete kunjungan flow (check-in → photo → form → check-out)
- Integration: Photo storage & retrieval
- Manual: GPS validation boundary testing (99m valid, 101m invalid)

**Deliverables:**
- ✓ GPS validation service deployed
- ✓ Check-in/out flow working + duration calculated
- ✓ Photo capture (camera only) functional
- ✓ Visit Form submission + database synchronization
- ✓ Offline fallback with local storage
- ✓ 20 test cases passed

**Duration:** 2 minggu  
**Owner:** Backend Dev + Frontend Dev  
**Critical Risks:**
- GPS inaccuracy in urban canyon → Test in real environment
- Large photo file upload → Implement compression + progress indicator
- Offline mode complexity → Thorough testing needed

---

### FASE 5: DASHBOARD & MONITORING (Minggu 8)

**Objektif:**
- Build real-time manager dashboard
- Implement location tracking display
- Add status indicators & alerts
- WebSocket/polling infrastructure

**Aktivities:**

#### 5.1 Real-Time Location Tracking (F-20, F-21)

**Frontend Tracking Script (Sales Mobile):**```javascript
// /public/js/location-tracker.js

class LocationTracker {
  constructor() {
    this.trackingInterval = null;
    this.lastPosition = null;
  }
  
  startTracking() {
    // Track location every 30 seconds (as per PRD requirement)
    this.trackingInterval = setInterval(() => {
      this.captureAndSend();
    }, 30000);
    
    // Capture immediately
    this.captureAndSend();
  }
  
  stopTracking() {
    if (this.trackingInterval) {
      clearInterval(this.trackingInterval);
    }
  }
  
  async captureAndSend() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => this.sendLocation(position),
        (error) => console.warn('GPS error:', error),
        { 
          enableHighAccuracy: true, 
          maximumAge: 0,  // Always fresh
          timeout: 10000 
        }
      );
    }
  }
  
  async sendLocation(position) {
    const data = {
      latitude: position.coords.latitude,
      longitude: position.coords.longitude,
      accuracy: position.coords.accuracy,
      recorded_at: new Date().toISOString()
    };
    
    try {
      const response = await fetch('/api/location/update', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      
      if (response.ok) {
        this.lastPosition = data;
      }
    } catch (error) {
      console.warn('Location send failed:', error);
      // Retry on next interval
    }
  }
}

// Start tracking when user checks in to first kunjungan
const tracker = new LocationTracker();
document.addEventListener('DOMContentLoaded', () => {
  if (document.body.dataset.isTracking === 'true') {
    tracker.startTracking();
  }
});
```

**Backend Endpoint:**
```php
// API\LocationController
Route::post('/api/location/update', 'Api\LocationController@updateLocation');

public function updateLocation(Request $request)
{
    $validated = $request->validate([
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'accuracy' => 'nullable|numeric'
    ]);
    
    // Create or update realtime location
    LokasiRealtime::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'akurasi_meter' => $validated['accuracy'],
            'recorded_at' => now()
        ]
    );
    
    return response()->json(['success' => true]);
}
```

#### 5.2 Manager Dashboard (F-20 - F-24)

**Routes:**
```
GET /manager/dashboard  → Main dashboard with map
GET /api/dashboard/sales-locations  → AJAX for map data
```

**Dashboard View:**
```html
<div class="container-fluid mt-4">
  <div class="row">
    <!-- Statistics Panel -->
    <div class="col-md-12 mb-3">
      <div class="row">
        <div class="col-md-3 text-center">
          <div class="card bg-info text-white">
            <div class="card-body">
              <h3>{{ $activeSales }}</h3>
              <p>Sales Aktif Hari Ini</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 text-center">
          <div class="card bg-success text-white">
            <div class="card-body">
              <h3>{{ $totalVisits }}</h3>
              <p>Total Kunjungan</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 text-center">
          <div class="card bg-warning text-white">
            <div class="card-body">
              <h3>{{ $visitsCompleted }}</h3>
              <p>Kunjungan Selesai</p>
            </div>
          </div>
        </div>
        <div class="col-md-3 text-center">
          <div class="card bg-danger text-white">
            <div class="card-body">
              <h3>{{ $notMoving }}</h3>
              <p>⚠ Tidak Bergerak</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Map -->
    <div class="col-md-12">
      <div id="map" style="height: 600px; border-radius: 8px;"></div>
    </div>
  </div>
  
  <!-- Alerts Panel -->
  <div class="col-md-12 mt-3">
    <h5>Alerts & Notifikasi</h5>
    <div id="alerts-container">
      @forelse ($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show">
          {{ $alert['message'] }}
          <button type="button" class="btn-close"></button>
        </div>
      @empty
        <p class="text-muted">Tidak ada alert</p>
      @endforelse
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize Leaflet map
const map = L.map('map').setView([-2.9796, 104.7557], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '© OpenStreetMap'
}).addTo(map);

// Define marker colors
const markerColors = {
  'idle': 'gray',
  'active': 'yellow',
  'completed': 'green',
  'paused': 'orange'
};

// Fetch and display sales locations
function updateMap() {
  fetch('/api/dashboard/sales-locations')
    .then(r => r.json())
    .then(data => {
      // Clear existing markers
      map.eachLayer(layer => {
        if (layer instanceof L.Marker) layer.remove();
      });
      
      // Add new markers
      data.sales.forEach(sales => {
        const icon = L.icon({
          iconUrl: `/images/markers/${markerColors[sales.status]}.png`,
          iconSize: [32, 32],
          popupAnchor: [0, -16]
        });
        
        const marker = L.marker([sales.latitude, sales.longitude], {icon})
          .addTo(map)
          .bindPopup(`
            <strong>${sales.name}</strong><br>
            Status: ${sales.status}<br>
            Kunjungan hari ini: ${sales.visitCount}<br>
            Selesai: ${sales.completedCount}
          `);
        
        // Add idle alert if no movement > 60 minutes
        if (sales.noMovementMinutes > 60) {
          marker.setIcon(L.icon({
            iconUrl: '/images/markers/alert.png',
            iconSize: [40, 40]
          }));
          
          // Show in alerts panel
          addAlert(
            'warning',
            `${sales.name} tidak bergerak ${sales.noMovementMinutes} menit`
          );
        }
      });
    });
}

// Auto-update every 30 seconds
updateMap();
setInterval(updateMap, 30000);
</script>
```

**API Backend:**
```php
// Api\DashboardController
public function salesLocations()
{
    $salesLocations = LokasiRealtime::with('user')
        ->whereDate('recorded_at', today())
        ->get()
        ->map(function($location) {
            $noMovement = $this->calculateNoMovement($location->user_id);
            
            return [
                'id' => $location->user_id,
                'name' => $location->user->name,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'status' => $this->determineStatus($location->user_id),
                'visitCount' => $this->getTodayVisitCount($location->user_id),
                'completedCount' => $this->getTodayCompletedCount($location-user_id),
                'noMovementMinutes' => $noMovement,
                'lastUpdate' => $location->recorded_at->diffForHumans()
            ];
        });
    
    return response()->json([
        'sales' => $salesLocations,
        'activeSales' => $salesLocations->count(),
        'totalVisits' => Kunjungan::whereDate('tanggal', today())->count(),
        'completedVisits' => Kunjungan::whereDate('tanggal', today())
            ->whereNotNull('waktu_checkout')->count(),
    ]);
}

private function calculateNoMovement($userId)
{
    $locations = LokasiRealtime::where('user_id', $userId)
        ->orderBy('recorded_at', 'desc')
        ->limit(2)
        ->get();
    
    if ($locations->count() < 2) return 0;
    
    $lastLoc = $locations->first();
    $prevLoc = $locations->last();
    
    // Check if coordinates are same (within 10m tolerance)
    $distance = $this->calculateDistance(
        $lastLoc->latitude, $lastLoc->longitude,
        $prevLoc->latitude, $prevLoc->longitude
    );
    
    if ($distance < 10) {
        return $lastLoc->recorded_at->diffInMinutes($prevLoc->recorded_at);
    }
    
    return 0;
}
```

**Deliverables:**
- ✓ Real-time location tracking running every 30s
- ✓ Leaflet.js map displaying sales positions
- ✓ Status indicators (gray/yellow/green/orange)
- ✓ No-movement alerts (60+ minutes)
- ✓ Statistics dashboard (active sales, visit counts)
- ✓ Pop-up info on marker click

**Duration:** 1 minggu  
**Owner:** Frontend Dev + Backend Dev  
**Risk:** 
- High server load from frequent GPS updates → Implement database indexing on recorded_at
- Map rendering slowness with many markers → Cluster markers or pagination

---

### FASE 6: REPORTING & ANALYTICS (Minggu 9)

**Objektif:**
- Build comprehensive reporting module
- Implement export to Excel & PDF
- Performance analytics
- Filtering & date range selection

**Aktivities:**

#### 6.1 Performance Report (F-25, F-26, F-27, F-28)

**Routes:**
```
GET  /manager/laporan/performa         → Report view
GET  /api/laporan/performa-data        → AJAX data fetch
POST /manager/laporan/performa/export-excel
POST /manager/laporan/performa/export-pdf
```

**Report View:**
```html
<div class="container-fluid mt-4">
  <h3>Laporan Performa Sales</h3>
  
  <!-- Filter Panel -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <label>Dari Tanggal</label>
          <input type="date" id="filter-from" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Hingga Tanggal</label>
          <input type="date" id="filter-to" class="form-control">
        </div>
        <div class="col-md-3">
          <label>Filter</label>
          <button class="btn btn-primary w-100 mt-4" onclick="loadReport()">
            Tampilkan
          </button>
        </div>
        <div class="col-md-3">
          <label>&nbsp;</label>
          <div class="mt-4">
            <button class="btn btn-success me-2" onclick="exportExcel()">Excel</button>
            <button class="btn btn-danger" onclick="exportPdf()">PDF</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Data Table -->
  <table id="laporan-table" class="table table-striped">
    <thead>
      <tr>
        <th>Sales</th>
        <th>Total Kunjungan</th>
        <th>Sesuai Jadwal</th>
        <th>Di Luar Jadwal</th>
        <th>Durasi Total (jam)</th>
        <th>Rata-rata Visit</th>
        <th>Order Diterima</th>
      </tr>
    </thead>
    <tbody id="report-body"></tbody>
  </table>
</div>

<script>
async function loadReport() {
  const fromDate = document.getElementById('filter-from').value;
  const toDate = document.getElementById('filter-to').value;
  
  const response = await fetch('/api/laporan/performa-data', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({from_date: fromDate, to_date: toDate})
  });
  
  const data = await response.json();
  
  // Render table
  const tbody = document.getElementById('report-body');
  tbody.innerHTML = '';
  
  data.laporan.forEach(row => {
    tbody.innerHTML += `
      <tr>
        <td>${row.sales_name}</td>
        <td>${row.total_kunjungan}</td>
        <td>${row.sesuai_jadwal}</td>
        <td>${row.diluar_jadwal}</td>
        <td>${row.durasi_total_jam}</td>
        <td>${row.rata_rata_visit_menit}</td>
        <td>${row.order_diterima}</td>
      </tr>
    `;
  });
}

function exportExcel() {
  const fromDate = document.getElementById('filter-from').value;
  const toDate = document.getElementById('filter-to').value;
  location.href = `/manager/laporan/performa/export-excel?from=${fromDate}&to=${toDate}`;
}

function exportPdf() {
  const fromDate = document.getElementById('filter-from').value;
  const toDate = document.getElementById('filter-to').value;
  window.open(`/manager/laporan/performa/export-pdf?from=${fromDate}&to=${toDate}`);
}
</script>
```

**Backend Report Logic:**
```php
// LaporanController
public function performaData(Request $request)
{
    $from = $request->from_date;
    $to = $request->to_date;
    
    $laporan = Kunjungan::selectRaw('
        u.id,
        u.name,
        COUNT(*) as total_kunjungan,
        SUM(CASE WHEN is_sesuai_jadwal = 1 THEN 1 ELSE 0 END) as sesuai_jadwal,
        SUM(CASE WHEN is_sesuai_jadwal = 0 THEN 1 ELSE 0 END) as diluar_jadwal,
        SUM(durasi_menit) / 60 as durasi_total_jam,
        AVG(durasi_menit) as rata_rata_visit_menit,
        SUM(CASE WHEN vf.status_kunjungan = "order" THEN vf.nominal_order ELSE 0 END) as order_diterima
    ')
    ->join('users as u', 'kunjungan.user_id', '=', 'u.id')
    ->leftJoin('visit_form as vf', 'kunjungan.id', '=', 'vf.kunjungan_id')
    ->whereBetween('kunjungan.tanggal', [$from, $to])
    ->groupBy('u.id', 'u.name')
    ->get();
    
    return response()->json(['laporan' => $laporan]);
}

// Excel Export
public function exportExcel(Request $request)
{
    $from = $request->from_date;
    $to = $request->to_date;
    
    return (new LaporanPerformaExport($from, $to))->download('laporan-performa.xlsx');
}

// LaporanPerformaExport class
class LaporanPerformaExport implements FromQuery, WithHeadings
{
    private $from, $to;
    
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    
    public function query()
    {
        return Kunjungan::selectRaw('...')
            ->whereBetween('tanggal', [$this->from, $this->to]);
    }
    
    public function headings(): array
    {
        return [
            'Nama Sales', 'Total Kunjungan', 'Sesuai Jadwal', 
            'Di Luar Jadwal', 'Durasi Total (jam)', 'Rata-rata Visit (menit)', 'Total Order (Rp)'
        ];
    }
}
```

#### 6.2 Absensi Report (F-29)

**Admin Route:**
```
GET /admin/laporan/absensi
```

Similar structure to performa report but showing:
- Sales name
- Date
- Check-in time
- Check-out time
- Total hours
- Status (hadir/terlambat/tidak hadir)

**Deliverables:**
- ✓ Performance report with DataTables & filtering
- ✓ Excel export functional
- ✓ PDF export functional
- ✓ Attendance report
- ✓ Date range filtering

**Duration:** 1 minggu  
**Owner:** Backend Dev + Frontend Dev

---

### FASE 7: TESTING & QUALITY ASSURANCE (Minggu 9-10)

**Objektif:**
- Comprehensive testing across all modules
- UAT preparation
- Performance optimization
- Security hardening
- Bug fixes & refinements

**Aktivities:**

#### 7.1 Unit Testing (Week 9, Day 1-3)

**Test Suites:**

| Module | Test Cases | Coverage |
|--------|-----------|----------|
| GPS Validation | 8 | Distance calculation, boundary testing |
| Attendance | 6 | Check-in/out timing, duration calc |
| PJP | 5 | Schedule creation, klien assignment |
| Kunjungan | 10 | Check-in validation, photo handling |
| Visit Form | 4 | Form submission, enum validation |
| Report | 6 | Data aggregation, filtering |

**Example Test:**
```php
// tests/Unit/GpsValidationServiceTest.php

class GpsValidationServiceTest extends TestCase
{
    protected $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GpsValidationService();
    }
    
    public function test_distance_within_tolerance()
    {
        // Palembang: -2.976071, 104.755370
        // Target 100m away
        $result = $this->service->validateCheckIn(
            -2.9759,    // ~100m away
            104.7554,
            -2.9761,    // Target
            104.7551,
            100
        );
        
        $this->assertTrue($result['valid']);
        $this->assertLessThan(100, $result['distance']);
    }
    
    public function test_distance_exceeds_tolerance()
    {
        $result = $this->service->validateCheckIn(
            -2.970,     // Far away
            104.750,
            -2.9760,
            104.7550,
            100
        );
        
        $this->assertFalse($result['valid']);
        $this->assertGreaterThan(100, $result['distance']);
    }
}
```

#### 7.2 Feature Testing (Week 9, Day 4-7)

**Test Flow:** Complete user journeys

```php
// tests/Feature/SalesKunjunganFlowTest.php

class SalesKunjunganFlowTest extends TestCase
{
    public function test_complete_kunjungan_flow()
    {
        // 1. Create sample data
        $sales = User::factory()->create(['role' => 'sales']);
        $klien = Klien::factory()->create();
        $jadwal = JadwalKunjungan::factory()->create(['user_id' => $sales->id]);
        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_id' => $jadwal->id,
            'klien_id' => $klien->id
        ]);
        
        // 2. Sales starts journey
        $response = $this->actingAs($sales)
            ->post('/sales/pjp/' . $jadwal->id . '/mulai-perjalanan');
        $this->assertEquals(200, $response->status());
        
        // 3. Sales checks in
        $response = $this->post('/sales/kunjungan/' . $jadwalKlien->id . '/checkin', [
            'lat' => $klien->latitude,
            'lng' => $klien->longitude,
            'accuracy' => 8.5
        ]);
        $this->assertEquals(200, $response->status());
        $kunjungan = Kunjungan::first();
        $this->assertNotNull($kunjungan->waktu_checkin);
        
        // 4. Upload photo
        $photo = UploadedFile::fake()->image('photo.jpg');
        $response = $this->post('/sales/kunjungan/' . $kunjungan->id . '/photo', [
            'photo' => $photo
        ]);
        $this->assertEquals(200, $response->status());
        
        // 5. Fill visit form
        $response = $this->post('/sales/kunjungan/' . $kunjungan->id . '/visit-form', [
            'status_kunjungan' => 'order',
            'catatan' => 'Stok OK',
            'kondisi_stok' => 'aman',
            'nominal_order' => 500000
        ]);
        $this->assertEquals(302, $response->status()); // Redirect
        
        // 6. Check out
        $response = $this->post('/sales/kunjungan/' . $kunjungan->id . '/checkout', [
            'lat' => $klien->latitude,
            'lng' => $klien->longitude
        ]);
        
        // 7. Verify data
        $kunjungan->refresh();
        $this->assertNotNull($kunjungan->waktu_checkout);
        $this->assertNotNull($kunjungan->durasi_menit);
        $this->assertNotNull($kunjungan->foto_bukti);
        $this->assertTrue($kunjungan->visitForm->exists());
    }
}
```

#### 7.3 Performance Testing (Week 10, Day 1-2)

**Load Test Scenario:**
```
50 concurrent users
- 20 sales doing check-in/check-out simultaneously
- 20 managers viewing dashboard
- 10 admins generating reports

Target metrics:
  - Dashboard load time: < 3 seconds
  - API response time: < 500ms
  - Database query time: < 100ms
  - No dropped requests
```

**Optimization Checklist:**
- ✓ Add database indexes on frequently queried columns
```sql
CREATE INDEX idx_kunjungan_user_tanggal ON kunjungan(user_id, tanggal);
CREATE INDEX idx_lokasi_realtime_recorded_at ON lokasi_realtime(recorded_at);
CREATE INDEX idx_jadwal_klien_status ON jadwal_klien(status);
```

- ✓ Implement query optimization (N+1 problem)
```php
// BAD
$kunjungan = Kunjungan::all();
foreach ($kunjungan as $k) {
    echo $k->user->name;  // N queries!
}

// GOOD
$kunjungan = Kunjungan::with('user')->get();
foreach ($kunjungan as $k) {
    echo $k->user->name;  // 1 query
}
```

- ✓ Cache dashboard data
```php
Cache::remember('dashboard-today-' . date('Y-m-d'), 300, function() {
    return [
        'activeSales' => LokasiRealtime::whereDate('recorded_at', today())->count(),
        // ...
    ];
});
```

- ✓ Lazy load large lists (DataTables server-side)
- ✓ Minify/compress assets
- ✓ Gzip compression on server

#### 7.4 Security Hardening (Week 10, Day 3)

**Checklist:**
- [ ] CSRF protection on all POST/PUT/DELETE routes
- [ ] SQL injection prevention (use parameterized queries)
- [ ] XSS prevention (escape output, use Blade templating)
- [ ] CORS configuration for API endpoints
- [ ] Rate limiting on login & API endpoints
- [ ] Sensitive data encryption (password hashing, GPS data sanitization)
- [ ] Environment variables not exposed in code
- [ ] Permission checks on all route handlers

**Implementation:**
```php
// Rate limiting
Route::post('/sales/attendance/checkin')->middleware('throttle:10,1'); // 10 calls/minute

// CSRF protection (auto in Laravel)
// XSS prevention
{{ $user->name }}  // Blade auto-escapes

// Encryption
$encrypted = encrypt($sensitiveData);
$decrypted = decrypt($encrypted);
```

#### 7.5 Bug Fix & Refinement (Week 10, Day 4-5)

**Tracking Issues:**
- Create Trello board or GitHub Issues
- Priority levels: Critical, High, Medium, Low
- Daily standup to review blockers

#### 7.6 UAT Preparation (Week 10)

**UAT Environment:**
- Replica of production
- Sample data loaded
- Test scenarios documented

**UAT Script Example:**
```
Test Case 1: Sales completes full kunjungan cycle
Pre-condition: Sales logged in, has PJP today
Steps:
  1. Click "Mulai Perjalanan"
  2. Navigate to first klien location
  3. Click "Check-In"
  4. Capture photo
  5. Fill Visit Form
  6. Click "Check-Out"
Expected: Data saved, next klien ready
```

**Deliverables:**
- ✓ 50+ unit & feature tests (>80% code coverage)
- ✓ Load tests: 50 concurrent users  no failures
- ✓ Security audit passed
- ✓ Performance optimized: dashboard <3s load
- ✓ UAT environment ready with test scripts
- ✓ Bug list < 5 critical, 10 medium, resolved

**Duration:** 1.5 minggu  
**Owner:** QA Engineer + Backend/Frontend Devs

---

## DEPENDENCY & CRITICAL PATH

```
CRITICAL PATH ANALYSIS

PHASE 0 (Pre-dev)
    ↓ (must complete before proceeding)
    
PHASE 1 (Auth & UI)
    ↓
PHASE 2 (Master Data)
    ↓
PHASE 3.1 (Absensi) ← can start in parallel with 3.2
    ↓          ↓
    └─────→ PHASE 3.2 (PJP)
            ↓
        PHASE 4 (Kunjungan Core)
            ├─→ GPS Validation
            ├─→ Photo Capture
            └─→ Visit Form
            ↓
        PHASE 5 (Dashboard & Monitoring)
            ↓
        PHASE 6 (Reporting)
            ↓
        PHASE 7 (Testing & QA)

CRITICAL DEPENDENCIES:
1. Auth must be done before any role-based features
2. Master Data must exist for PJP & Kunjungan
3. GPS Validation Service must be tested thoroughly before Kunjungan
4. Dashboard needs real-time location tracking to function
5. Testing should occur throughout, not just at end
```

---

## STRATEGI TESTING

### Test Pyramid

```
         /\
        /  \           Manual UAT (5%)
       /────\      
      /      \        Integration Tests (15%)
     /────────\    System, API, DB interactions
    /          \    
   /────────────\   Unit Tests (80%)
   Individual functions, services
```

### Test Schedule Integration

```
PHASE 1 → Unit test auth immediately
PHASE 2 → Feature test CRUD operations
PHASE 3 → Integration test with GPS mocking
PHASE 4 → Full flow testing (E2E)
PHASE 5-6 → Performance & security testing
PHASE 7 → UAT & manual testing
```

---

## RISK MANAGEMENT Updated

| No | Risk | Probability | Impact | Mitigation |
|----|------|-------------|--------|-----------|
| 1 | GPS inaccuracy in urban areas | High | High | Configurable radius; manual override; testing in real environment |
| 2 | Photo manipulation (gallery bypass) | Medium | High | File timestamp validation; server-side verification |
| 3 | Network instability in field | High | Medium | Offline mode with IndexedDB; auto-sync on reconnect |
| 4 | Large photo upload latency | Medium | Medium | Image compression; chunked upload; progress indicator |
| 5 | Database performance degra... | Medium | High | Indexing; query optimization; caching; load testing week 10 |
| 6 | Sales device battery drain | Medium | Low | Reduce tracking frequency; background optimization |
| 7 | Data privacy/sensitive GPS exposure | Low | Critical | End-to-end encryption; access control; audit logging |
| 8 | Third-party lib incompatibility | Low | Medium | Test early; maintain compatibility matrix |

---

## DELIVERABLES PER FASE

### Phase 0 (Pre-Dev)
- [x] Development environment setup guide
- [x] Database migration files
- [x] Architecture decision document
- [x] API specification (OpenAPI/Swagger if applicable)

### Phase 1 (Auth & UI)
- [x] Authentication system functional
- [x] Login/logout flows tested
- [x] Role system working (4 roles defined)
- [x] Desktop & mobile UI templates responsive
- [x] 8 unit/feature tests

### Phase 2 (Master Data)
- [x] User CRUD + role assignment
- [x] Klien CRUD + GPS mapping (Leaflet)
- [x] Wilayah CRUD
- [x] DataTables server-side implementation
- [x] 15 unit/feature tests
- [x] API documentation

### Phase 3 (Attendance & PJP)
- [x] Absensi check-in/out functional
- [x] PJP creation & klien assignment
- [x] Sales can view today's schedule
- [x] GPS location recording
- [x] 12 unit/feature tests
- [x] Manual testing in real GPS environment

### Phase 4 (Kunjungan Core)
- [x] GPS validation service (Haversine)
- [x] Check-in with radius validation
- [x] Photo capture (camera only, no gallery)
- [x] Check-out with duration tracking
- [x] Visit Form submission
- [x] Offline sync capability
- [x] 20 unit/integration tests
- [x] End-to-end flow tested

### Phase 5 (Dashboard & Monitoring)
- [x] Real-time location tracking (30s interval)
- [x] Leaflet.js map with sales positions
- [x] Status indicators (gray/yellow/green markers)
- [x] No-movement alerts (60+ minutes)
- [x] Statistics dashboard (active sales, visit counts)
- [x] Market clustering for many markers
- [x] 10 integration tests

### Phase 6 (Reporting & Export)
- [x] Performance report view + DataTables
- [x] Date range filtering
- [x] Excel export (Maatwebsite Laravel Excel)
- [x] PDF export (Barryvdh DomPDF)
- [x] Absensi report
- [x] 6 unit/integration tests

### Phase 7 (Testing & QA)
- [x] Unit tests: 50+ cases (>80% coverage)
- [x] Integration tests: API & database
- [x] Feature tests: Complete user flows
- [x] Load testing: 50 concurrent users
- [x] Security audit: OWASP checklist
- [x] Performance optimization: <3s dashboard load
- [x] UAT environment prepared
- [x] Bug list tracked & resolved
- [x] Performance metrics documented
- [x] Deployment guide written

### Launch Readiness
- [x] Production server configured
- [x] Database backups strategy
- [x] Monitoring & alerting setup
- [x] Documentation complete
- [x] Training materials for users
- [x] Support/helpdesk process defined

---

## TIMELINE SUMMARY

| Phase | Duration | Start | End | Status |
|-------|----------|-------|-----|--------|
| 0 - Pre-Dev | 0.5w | W0 | W0.5 | Not Started |
| 1 - Auth & UI | 1.5w | W0.5 | W2 | Not Started |
| 2 - Master Data | 1w | W2 | W3 | Not Started |
| 3 - Attendance & PJP | 1.5w | W3 | W4.5 | Not Started |
| 4 - Kunjungan Core | 2w | W4.5 | W6.5 | Not Started |
| 5 - Dashboard | 1w | W6.5 | W7.5 | Not Started |
| 6 - Reporting | 1w | W7.5 | W8.5 | Not Started |
| 7 - Testing & QA | 1.5w | W8.5 | W10 | Not Started |
| **TOTAL** | **10w** | **W0** | **W10** | **On Track** |

---

## REKOMENDASI TAMBAHAN

### Untuk Fidelitas Tinggi PRD Implementation:

1. **Agile Approach:**
   - Sprint harian standup (15 menit)
   - Sprint review setiap akhir fase
   - Retrospektif untuk continuous improvement

2. **Documentation:**
   - Code comments dalam bahasa Indonesia untuk clarity
   - API documentation (Swagger/Postman)
   - User manual untuk setiap fitur
   - Developer guide untuk maintenance

3. **Git Workflow:**
   - Branch per feature (feature/pjp-management)
   - Pull request dengan review requirement
   - Semantic versioning (v0.1.0, v0.2.0, etc.)

4. **Monitoring & Metrics:**
   - Error logging (Sentry/LogRocket)
   - Performance monitoring (Laravel Telescope in dev)
   - User tracking for UAT feedback

5. **Team Structure (Recommended):**
   - 1 Backend Developer (Laravel specialist)
   - 1 Frontend Developer (Vue/Blade + Leaflet)
   - 1 QA Engineer
   - 1 DevOps/Infra engineer
   - 1 Tech Lead/Architect

---

## KESIMPULAN

Dokumen ini menyediakan roadmap komprehensif untuk implementasi Aplikasi Monitoring Sales Force berbasis web pada PT. Tridaya Sakti Medima. Dengan mengikuti fase-fase terstruktur, dependency mapping, dan strategi testing yang jelas, tim dapat memastikan pengembangan berjalan sesuai PRD dengan kualitas tinggi.

**Key Success Factors:**
✅ Komunikasi tim yang transparan  
✅ Testing early & often  
✅ Regular PRD alignment  
✅ Risk mitigation proaktif  
✅ User feedback loop  
✅ Performance focus  

**Estimasi Toleransi:** ±1 minggu (tergantung kompleksitas GPS handling dan stabilitas koneksi testing)

---

## REVISI HISTORY

| Versi | Tanggal | Author | Perubahan |
|-------|---------|--------|----------|
| 1.0 | Maret 2026 | Tech Lead | Initial comprehensive analysis |
| | | | - 7 fase terstruktur dengan detail tasks |
| | | | - Dependency mapping & critical path |
| | | | - Test strategy & risk management |
| | | | - Deliverables checklist per fase |

---

**LAMPIRAN DENGAN HELPFUL RESOURCES**

### Referensi Laravel Packages
- [Laravel Breeze](https://laravel.com/docs/authentication#breeze-scaffolding)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/)
- [Yajra DataTables](https://yajrabox.com/docs/laravel-datatables)
- [Leaflet.js Documentation](https://leafletjs.com/)
- [Maatwebsite Laravel Excel](https://docs.laravel-excel.com/)
- [Barryvdh Laravel DomPDF](https://packagist.org/packages/barryvdh/laravel-dompdf)

### Tools Rekomendasi
- **Development:** VS Code + Laravel extension
- **Database:** MySQL Workbench / DBeaver
- **Version Control:** GitHub Desktop / Git CLI
- **Testing:** PHPUnit (built-in), Postman (API testing)
- **Monitoring:** Laravel Telescope (dev), Sentry (production)
- **Design:** Figma (untuk UI mockups)

---

*Dokumen ini adalah living document dan dapat diperbarui sesuai progress development dan feedback stakeholder.*
