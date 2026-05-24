# 📋 FASE 1 - ARCHITECTURE & FLOW DIAGRAM

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      CLIENT LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐       │
│  │   Desktop    │    │   Mobile     │    │    Guest     │       │
│  │   Layout     │    │   Layout     │    │   Layout     │       │
│  │  (Admin/Mgr) │    │   (Sales)    │    │   (Login)    │       │
│  └──────────────┘    └──────────────┘    └──────────────┘       │
│       ↓                     ↓                     ↓               │
│   Bootstrap 5 + FontAwesome JavaScript                           │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                   ROUTING LAYER                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Public Routes:        Protected Routes:    Role-Based Routes:  │
│  • GET  /              • POST /logout        • /admin/*          │
│  • GET  /login         • GET  /password/*    • /manager/*        │
│  • POST /login         • POST /password/*    • /sales/*          │
│                                                                   │
│  ↓ MIDDLEWARE                                                    │
│  Guest | Authenticated | Role Check (Super Admin|Admin|Manager) │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                  CONTROLLER LAYER                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────┐        ┌──────────────────────────┐        │
│  │ LoginController │        │ DashboardControllers     │        │
│  ├─────────────────┤        ├──────────────────────────┤        │
│  │ • showLoginForm │        │ • AdminDashboard         │        │
│  │ • login         │        │ • ManagerDashboard       │        │
│  │ • logout        │        │ • SalesDashboard         │        │
│  └─────────────────┘        └──────────────────────────┘        │
│                                                                   │
│  ┌─────────────────┐                                            │
│  │PasswordControl..│                                            │
│  ├─────────────────┤                                            │
│  │ • showChangeForm│                                            │
│  │ • update        │                                            │
│  └─────────────────┘                                            │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    MODEL LAYER                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────┐       │
│  │ User Model                                           │       │
│  ├──────────────────────────────────────────────────────┤       │
│  │ • Properties: name, email, password, phone,         │       │
│  │              photo, wilayah_id, is_active           │       │
│  │ • Relations: belongsTo(Wilayah)                      │       │
│  │ • Methods: hasRoles(), isSales(), isManager(),       │       │
│  │           isAdmin(), isSuperAdmin()                  │       │
│  │ • Traits: HasRoles (Spatie), SoftDeletes            │       │
│  └──────────────────────────────────────────────────────┘       │
│                              ↓                                    │
│  ┌──────────────────────────────────────────────────────┐       │
│  │ Wilayah Model                                        │       │
│  ├──────────────────────────────────────────────────────┤       │
│  │ • Properties: nama_wilayah, keterangan              │       │
│  │ • Relations: hasMany(User), hasMany(Klien)          │       │
│  └──────────────────────────────────────────────────────┘       │
│                              ↓                                    │
│  ┌──────────────────────────────────────────────────────┐       │
│  │ Role & Permission Models (Spatie)                   │       │
│  ├──────────────────────────────────────────────────────┤       │
│  │ • Role: super_admin, admin, manager, sales          │       │
│  │ • Permission: 17 permissions assigned to roles      │       │
│  │ • Relationships: Many-to-many with users            │       │
│  └──────────────────────────────────────────────────────┘       │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                  DATABASE LAYER                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌────────────┐  ┌──────────────┐  ┌────────────────┐           │
│  │ users      │  │ wilayah      │  │ roles          │           │
│  ├────────────┤  ├──────────────┤  ├────────────────┤           │
│  │ id (PK)    │  │ id (PK)      │  │ id (PK)        │           │
│  │ name       │  │ nama_wilayah │  │ name           │           │
│  │ email (UK) │  │ keterangan   │  │ guard_name     │           │
│  │ password   │  │ timestamps   │  │ description    │           │
│  │ phone      │  └──────────────┘  │ timestamps     │           │
│  │ photo      │                     └────────────────┘           │
│  │ wilayah_id │  ┌──────────────┐  ┌─────────────────┐          │
│  │ (FK)       │  │ permissions  │  │ model_has_roles │          │
│  │ is_active  │  ├──────────────┤  ├─────────────────┤          │
│  │ deleted_at │  │ id (PK)      │  │ role_id (FK)    │          │
│  │ timestamps │  │ name         │  │ model_type      │          │
│  └────────────┘  │ guard_name   │  │ model_id        │          │
│                  │ description  │  └─────────────────┘          │
│                  │ timestamps   │                                │
│                  └──────────────┘                                │
│                                                                   │
│                  ┌─────────────────────┐                        │
│                  │ role_has_permissions│                        │
│                  ├─────────────────────┤                        │
│                  │ permission_id (FK)  │                        │
│                  │ role_id (FK)        │                        │
│                  └─────────────────────┘                        │
└─────────────────────────────────────────────────────────────────┘
```

---

## AUTH FLOW DIAGRAM

```
USER LOGIN REQUEST
       ↓
┌──────────────────────────┐
│ GET /login               │ → Show Login Form
└──────────────────────────┘
       ↓
   [USER ENTERS CREDENTIALS]
       ↓
┌──────────────────────────────────────┐
│ POST /login                          │
├──────────────────────────────────────┤
│ • Validate email format              │
│ • Validate password minimum length   │
└──────────────────────────────────────┘
       ↓
┌──────────────────────────────────────┐
│ Auth::attempt($credentials)          │
├──────────────────────────────────────┤
│ • Query user by email                │
│ • Verify password hash               │
└──────────────────────────────────────┘
       ↓
    [AUTHENTICATED?]
       ↙        ↖
    YES         NO
     ↓           ↓
  [SUCCESS]   [FAILED]
     ↓            ↓
  SESSION      ERROR
  REGEN        MESSAGE
     ↓            ↓
 GET ROLE    Back to
     ↓       Login Form
┌─────────────────────────────────┐
│ ROLE-BASED REDIRECT             │
├─────────────────────────────────┤
│ IF Sales → /sales/dashboard     │
│ IF Manager → /manager/dashboard │
│ IF Admin → /admin/dashboard     │
└─────────────────────────────────┘
     ↓
DASHBOARD LOADED ✓
```

---

## ROLE HIERARCHY & PERMISSIONS

```
PERMISSION STRUCTURE
├── super_admin (Full Access)
│   └── ✓ All permissions
│
├── admin
│   ├── ✓ manage_klien
│   ├── ✓ manage_wilayah
│   ├── ✓ create_pjp
│   ├── ✓ edit_pjp
│   ├── ✓ delete_pjp
│   ├── ✓ view_attendance
│   ├── ✓ view_kunjungan
│   └── ✓ manage_config
│
├── manager
│   ├── ✓ view_dashboard
│   ├── ✓ view_pjp
│   ├── ✓ view_kunjungan
│   ├── ✓ view_reports
│   └── ✓ export_reports
│
└── sales
    ├── ✓ checkin_attendance
    ├── ✓ create_kunjungan
    ├── ✓ upload_photo
    └── ✓ view_pjp
```

---

## MIDDLEWARE CHAIN

```
REQUEST
  ↓
┌────────────────────────────────────┐
│ web (session, cookies, csrf)        │
└────────────────────────────────────┘
  ↓
┌────────────────────────────────────┐
│ auth (if protected route)           │
│ • Check if user authenticated       │
│ • Redirect to login if not          │
└────────────────────────────────────┘
  ↓
┌────────────────────────────────────┐
│ role (if role protected)            │
│ • Check user has required role(s)   │
│ • Abort 403 if not authorized       │
└────────────────────────────────────┘
  ↓
CONTROLLER → RESPONSE
```

---

## SECURITY IMPLEMENTATION

```
├── Entry Point (Login)
│   ├── Input Validation (email format, password length)
│   ├── CSRF Token Verification (@csrf in form)
│   └── Rate Limiting (optional, can be added)
│
├── Password Security
│   ├── Bcrypt Hashing (config.hashing.driver = bcrypt)
│   ├── Salt Generation (automatic)
│   └── Verification (Hash::check)
│
├── Session Security
│   ├── Session Regeneration (after login)
│   ├── Session Invalidation (on logout)
│   ├── HTTPS Ready (config.secure_cookies)
│   └── HttpOnly Cookies
│
├── Authorization
│   ├── Role-Based Access (Spatie Permission)
│   ├── Middleware Protection (role middleware)
│   ├── Route Guards (@can, @role in Blade)
│   └── Model Policies (can be added in Phase 2)
│
├── Data Protection
│   ├── SQL Injection Prevention (Eloquent ORM)
│   ├── XSS Prevention (Blade auto-escape)
│   ├── Soft Delete (users.deleted_at)
│   └── Email Uniqueness (database constraint)
│
└── Audit Trail
    ├── Timestamps (created_at, updated_at)
    └── User Tracking (ready for Phase 2)
```

---

## DATABASE RELATIONSHIPS

```
User (n) ─── (1) Wilayah
  │
  └─(n) Roles (via model_has_roles)
        │
        └─(n) Permissions (via role_has_permissions)

Wilayah (1) ─── (n) User
   │
   └─ (1) ─── (n) Klien [Phase 2]

User (1) ─── (n) Absensi [Phase 3]
User (1) ─── (n) Kunjungan [Phase 4]
User (1) ─── (n) LokasiRealtime [Phase 5]
```

---

## PHASE 1 COMPLETION CHECKLIST

```
✅ Infrastructure
  ├── Laravel 12 project initialized
  ├── Environment configuration
  ├── Database migrations created
  └── Seeding capability established

✅ Authentication
  ├── Login system
  ├── Logout functionality
  ├── Password change feature
  ├── Session management
  └── Remember me option

✅ Authorization
  ├── 4 roles defined
  ├── 17 permissions assigned
  ├── Role middleware implemented
  ├── Route protection active
  └── Access denied handling

✅ User Interface
  ├── Desktop layout (admin/manager)
  ├── Mobile layout (sales)
  ├── Guest layout (login)
  ├── Responsive design
  ├── Mobile optimization
  └── Accessibility features

✅ Testing
  ├── Unit tests (6)
  ├── Feature tests (14)
  ├── Edge cases covered
  ├── All tests passing
  └── >80% code coverage

✅ Documentation
  ├── Setup guide
  ├── API endpoints
  ├── Database schema
  ├── Architecture diagrams
  └── Troubleshooting guide
```

---

## NEXT PHASE FOUNDATION

Phase 1 provides the foundation for Phase 2 with:

✅ Authenticated user context available  
✅ Role-based routing ready  
✅ Database connection tested  
✅ Admin dashboard scaffolding ready  
✅ Service layer pattern established  

→ **Ready to build Master Data Management (Phase 2)**

---

*Architecture & Flow Diagram - Phase 1 Complete*
