# Catatan Struktur Folder & Keterangan Aplikasi
## Sistem Absensi Pegawai (PHP & MySQL)

---

## 1. Tentang Aplikasi

Aplikasi web untuk mencatat dan mengelola absensi pegawai (kehadiran, izin,
sakit, cuti), dilengkapi dashboard ringkasan, data pegawai, sistem login
dengan hak akses bertingkat, dan laporan yang bisa difilter serta diexport.
Tampilan dibuat responsif sehingga bisa diakses dan dipakai langsung dari HP
(browser HP), tanpa perlu install aplikasi tambahan.

**Teknologi yang dipakai:**
- **Backend**: PHP native (tanpa framework), koneksi database pakai PDO (prepared statement, aman dari SQL Injection)
- **Database**: MySQL / MariaDB
- **Frontend**: Bootstrap 5 + Bootstrap Icons (CDN), responsif untuk HP/tablet/desktop
- **Autentikasi**: session PHP, password di-hash dengan `password_hash()` (bcrypt)

**Tiga level hak akses (role):**
| Role | Hak Akses |
|---|---|
| **admin** | Akses penuh: data pegawai, absensi, laporan, kelola akun & hak akses, pengaturan sistem |
| **hrd** | Kelola data pegawai, pantau/koreksi absensi semua pegawai, lihat & export laporan |
| **pegawai** | Absen masuk/pulang sendiri, ajukan izin/sakit/cuti, lihat riwayat & laporan absensi diri sendiri |

---

## 2. Struktur Folder

```
absensi_app/
│
├── config/                     Konfigurasi inti aplikasi
│   ├── config.php               Session, timezone, BASE_URL, nama aplikasi
│   └── database.php             Koneksi PDO ke MySQL (host, user, password, nama DB)
│
├── database/
│   └── absensi_db.sql           Skema database lengkap + data awal (seed)
│
├── includes/                   Komponen yang dipakai berulang di banyak halaman
│   ├── auth.php                 Fungsi login check & hak akses (requireLogin, requireRole, dll)
│   ├── header.php                Bagian atas layout: navbar, meta, link CSS
│   ├── sidebar.php               Menu navigasi, tampil beda sesuai role yang login
│   ├── footer.php                Penutup layout: script JS, toggle sidebar mobile
│   └── status_badge.php          Komponen kecil: label warna status absensi (hadir/izin/dst)
│
├── pegawai/                    Modul Data Pegawai (khusus admin & hrd)
│   ├── index.php                 Daftar pegawai, pencarian, hapus
│   ├── tambah.php                Form tambah pegawai baru
│   └── edit.php                  Form edit data pegawai
│
├── absensi/                    Modul Absensi
│   ├── absen.php                 Halaman absen mandiri untuk pegawai (masuk/pulang/izin), termasuk deteksi lokasi HP
│   └── index.php                 Monitoring & koreksi absensi seluruh pegawai (admin/hrd)
│
├── laporan/                    Modul Laporan
│   ├── index.php                 Laporan absensi dengan filter tanggal/pegawai/status + rekap ringkas
│   └── export.php                Export hasil laporan ke file CSV (bisa dibuka Excel)
│
├── users/                      Modul Kelola Akun & Hak Akses (khusus admin)
│   ├── index.php                 Daftar akun, tambah akun baru
│   └── edit.php                  Edit role, status, reset password akun
│
├── assets/
│   └── css/style.css            Styling tambahan (sidebar, kartu statistik, responsif mobile)
│
├── uploads/
│   └── foto_pegawai/             Folder kosong, disiapkan untuk pengembangan fitur foto profil/selfie absen
│
├── login.php                    Halaman login
├── logout.php                   Proses logout (hapus session)
├── index.php                    Entry point, redirect otomatis ke login/dashboard
├── dashboard.php                Ringkasan statistik (beda tampilan untuk admin/hrd vs pegawai)
├── profil.php                   Info akun pribadi & form ganti password
├── pengaturan.php               Pengaturan jam kerja & toleransi telat (khusus admin)
├── akses_ditolak.php            Halaman yang muncul jika user membuka fitur di luar hak aksesnya
├── setup.php                    Dijalankan SEKALI setelah import database, untuk membuat password default
└── README.md                    Panduan instalasi & penggunaan
```

---

## 3. Alur Kerja Singkat

1. **Pengguna membuka aplikasi** → `index.php` mengecek session, arahkan ke
   `login.php` (belum login) atau `dashboard.php` (sudah login).
2. **Login** → `login.php` mencocokkan username/password ke tabel `users`,
   lalu menyimpan `role` dan `id_pegawai` ke session.
3. **Setiap halaman modul** (pegawai, absensi, laporan, users) memanggil
   `requireRole([...])` dari `includes/auth.php` di baris paling atas, untuk
   memastikan hanya role yang berhak yang bisa membuka halaman tersebut.
4. **Menu sidebar** (`includes/sidebar.php`) otomatis menyembunyikan/menampilkan
   item menu berdasarkan role yang sedang login.
5. **Pegawai absen** lewat `absensi/absen.php` → data masuk ke tabel `absensi`,
   status "Hadir"/"Terlambat" dihitung otomatis berdasarkan jam yang diatur
   di `pengaturan.php`.
6. **Laporan** (`laporan/index.php`) menggabungkan tabel `absensi`, `pegawai`,
   dan `departemen`, bisa difilter lalu diexport lewat `laporan/export.php`.

---

## 4. Struktur Tabel Database (Ringkas)

| Tabel | Fungsi |
|---|---|
| `users` | Akun login: username, password (hash), role, terhubung ke `id_pegawai` |
| `pegawai` | Data induk pegawai: NIP, nama, jabatan, departemen, kontak, status |
| `jabatan` | Master data jabatan |
| `departemen` | Master data departemen/divisi |
| `absensi` | Catatan harian: jam masuk/keluar, status, keterangan, lokasi |
| `pengaturan` | Jam kerja, toleransi keterlambatan, nama instansi (data tunggal, 1 baris) |

---

## 5. Catatan Keamanan & Perawatan

- File `setup.php` **wajib dihapus** dari server setelah dijalankan sekali (dipakai untuk generate password awal).
- Password disimpan ter-enkripsi (bcrypt), tidak pernah dalam bentuk teks biasa.
- Semua query ke database memakai *prepared statement* (PDO) untuk mencegah SQL Injection.
- Ganti password akun contoh (`admin`, `hrd`, `budi`, `siti`) setelah login pertama kali.
- Untuk dipakai secara online (bukan cuma jaringan lokal), folder ini perlu di-hosting di server dengan PHP & MySQL, dan `config/database.php` disesuaikan dengan kredensial hosting.
