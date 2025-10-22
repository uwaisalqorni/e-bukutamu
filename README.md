# 📖 Buku Tamu Web App

Aplikasi **Buku Tamu berbasis web** dengan fitur **Check-In & Check-Out**, **Manajemen Lokasi & QR Code**, 
dan **integrasi API SIMRS Khanza** untuk otomatis mengambil data pegawai berdasarkan NIK.

---

## 🚀 Fitur Utama

✅ **Scan QR Code untuk akses form buku tamu**  
✅ **Form Check-In**: User isi NIK → Nama otomatis terisi dari API SIMRS  
✅ **Form Check-Out**: User masukkan NIK untuk keluar  
✅ **Manajemen Lokasi**: Admin dapat membuat QR Code unik per lokasi  
✅ **Manajemen Acara**: Admin mengatur daftar acara yang akan dipilih user  
✅ **Dashboard Admin**: Melihat daftar tamu yang hadir dan export data ke Excel  
✅ **Mobile-Friendly**: Desain responsif untuk digunakan di HP & tablet

---

## 🗂️ Struktur Folder

bukutamu/
├── Api/
│ ├── get_pegawai.php
│ ├── search_pegawai.php
│ └── validate_checkout.php
├── admin/
│ ├── dashboard.php
│ ├── lokasi.php
│ ├── acara.php
├── qrcode/
│ └── [QR Code images]
├── db.php
├── index.php
├── checkin.php
├── checkout.php
├── database.sql
└── README.md

---

## ⚙️ Cara Install

1. **Clone Repo**
   ```bash
   git clone https://github.com/username/bukutamu.git
Import Database

Buat database baru di MySQL (contoh: bukutamu_db)

Import file database.sql ke database tersebut

update sql : 
1. ALTER TABLE buku_tamu
  ADD COLUMN ttd_path VARCHAR(255) NULL AFTER waktu_keluar,
  ADD COLUMN waktu_masuk DATETIME NULL;
2. ALTER TABLE lokasi ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE lokasi ADD COLUMN deleted_at DATETIME NULL;
3. ALTER TABLE acara ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE acara ADD COLUMN deleted_at DATETIME NULL;


Edit Koneksi Database
Ubah db.php sesuai setting XAMPP/MySQL kamu:
$conn = new mysqli("localhost", "root", "", "bukutamu_db");
Jalankan di Browser
Buka:
http://localhost/bukutamu/index.php

📡 Integrasi API SIMRS
Pastikan API SIMRS sudah berjalan di:
http://(sesuaikan)/ci3-api-bot/index.php/api/pegawai
API digunakan untuk:
Mengambil data pegawai (NIK → Nama)

Validasi saat Check-Out

🔒 Login Admin
Admin bisa mengakses: 
username : admin
pass : 879879


http://localhost/bukutamu/admin/dashboard.php
📦 Fitur Tambahan yang Akan Datang

 Export Excel
 Export PDF

 Login Multi-Level (Admin & Operator)

🧑‍💻 Kontributor
👨‍💻 Developer: [IT ABAL ABAL]
🏥 Integrasi SIMRS: [Team IT RS]

📜 Lisensi
MIT License - Silakan digunakan & dimodifikasi
