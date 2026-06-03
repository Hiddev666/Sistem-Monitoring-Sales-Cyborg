# Planning: Scrolling Sidebar for Admin, Super Admin, and Manager

## Masalah
- Sidebar pada layout utama untuk role admin, super admin, dan manager memuat banyak menu.
- Saat daftar menu terlalu panjang, sebagian item tertutup dan tidak bisa diakses dengan nyaman.
- Layout saat ini sudah memakai sidebar tetap, tetapi belum dipastikan area menu-nya benar-benar menjadi area scroll yang stabil di desktop dan mobile.

## Tujuan
- Sidebar tetap terlihat rapi meskipun menu sangat panjang.
- Pengguna bisa scroll daftar menu di dalam sidebar tanpa mengganggu konten utama halaman.
- Perilaku sidebar tetap konsisten untuk role admin, super admin, dan manager.
- Mode mobile tetap aman dan tidak merusak toggle sidebar yang sudah ada.

## File Target
- `resources/views/layouts/app.blade.php`

## Rencana Implementasi
1. Audit struktur sidebar saat ini
   - Identifikasi pembungkus sidebar, area brand/header, dan area daftar menu.
   - Pastikan area yang panjang benar-benar hanya menu, bukan seluruh sidebar.

2. Ubah container sidebar menjadi layout kolom
   - Jadikan sidebar sebagai container vertikal.
   - Sisakan bagian brand/header di atas.
   - Jadikan area navigasi menu sebagai bagian yang bisa tumbuh dan scroll.

3. Tambahkan scroll khusus pada area menu
   - Gunakan `overflow-y: auto` pada bagian menu.
   - Tambahkan `min-height: 0` jika sidebar memakai flex container, supaya scroll bekerja benar.
   - Batasi tinggi sidebar ke tinggi viewport, misalnya `height: 100vh` atau `max-height: 100vh`.

4. Pertahankan bagian brand tetap statis
   - Brand/logo dan judul tetap terlihat di atas.
   - Hanya daftar menu yang bergerak saat di-scroll.

5. Sesuaikan perilaku desktop dan mobile
   - Desktop: sidebar tetap fixed dan menu di dalamnya bisa di-scroll.
   - Mobile: pastikan aturan `show/hide` sidebar tetap berjalan.
   - Jangan sampai scroll sidebar mengganggu scroll halaman utama atau topbar.

6. Rapikan CSS agar tidak bentrok
   - Hindari menaruh overflow di container yang salah.
   - Pastikan `main-content` tetap punya alur layout yang sama.
   - Cek apakah ada elemen sticky/fixed lain yang perlu penyesuaian.

7. Uji dengan menu panjang
   - Cek akun admin.
   - Cek akun super admin.
   - Cek akun manager.
   - Verifikasi semua item menu tetap bisa dijangkau sampai bagian paling bawah.

## Kriteria Selesai
- Sidebar bisa di-scroll saat isi menu melebihi tinggi layar.
- Tidak ada item menu yang tertutup permanen.
- Desktop dan mobile tetap berfungsi normal.
- Tidak ada perubahan perilaku negatif pada konten utama.

## Risiko yang Perlu Dicek
- Jika tinggi sidebar tidak dibatasi dengan benar, scroll tidak akan muncul.
- Jika container yang diberi `overflow-y: auto` salah, layout bisa memotong brand atau topbar.
- Pada mobile, sidebar yang terlalu tinggi bisa menutupi konten bila `show/hide` tidak disesuaikan.

