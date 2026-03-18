<div align="center">

# ⚡ MediaTools

**All-in-One Media & Productivity Suite**

Platform produktivitas digital serba bisa — konversi file, buat invoice, generate QR code,
kelola link bio, dan banyak lagi. Gratis, cepat, tanpa instalasi.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=flat&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

🌐 **[mediatools.cloud](https://mediatools.cloud)**

</div>

---

## ✨ Tools yang Tersedia

### 📄 Dokumen & Bisnis
| Tool | Deskripsi |
|------|-----------|
| **Invoice Generator** | Buat invoice profesional dalam hitungan detik |
| **PDF Utilities** | Merge, split, dan compress file PDF |
| **File Converter** | Konversi PDF ↔ Word, Excel, PPT, JPG, PNG |

### 🖼️ Gambar & Media
| Tool | Deskripsi |
|------|-----------|
| **Image Converter** | Compress, resize, convert format gambar |
| **Background Remover** | Hapus background foto secara otomatis |
| **Media Downloader** | Download video YouTube, TikTok, Instagram |

### 🔗 Sosial & Link
| Tool | Deskripsi |
|------|-----------|
| **Bio-Link** | Halaman link-in-bio seperti Linktree |
| **QR Code Generator** | Buat QR Code untuk bisnis dan personal |

### 🔒 Keamanan & Branding
| Tool | Deskripsi |
|------|-----------|
| **Password Generator** | Buat password kuat dengan entropy tinggi |
| **Email Signature** | Desain tanda tangan email profesional |

---

## 🚀 Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: Tailwind CSS, Alpine.js, Vanilla JS
- **Database**: MySQL 8.4
- **Cache/Queue**: Redis
- **Server**: Nginx, Ubuntu 24/25
- **Konversi Dokumen**: LibreOffice Headless
- **Image Processing**: ImageMagick, GD

---

## ⚙️ Instalasi Lokal

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL
- Redis

### Langkah-langkah
```bash
# 1. Clone repository
git clone https://github.com/username/mediatools.git
cd mediatools

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# DB_DATABASE=mediatools_db
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Migrasi database
php artisan migrate

# 6. Build assets
npm run dev

# 7. Jalankan server
php artisan serve
```

Buka `http://localhost:8000`

---

## 🌐 Deploy ke VPS

Lihat panduan lengkap di [DEPLOYMENT.md](DEPLOYMENT.md)

---

## 📁 Struktur Project
```
mediatools/
├── app/
│   ├── Http/Controllers/Tools/    # Controller setiap tools
│   └── Services/                  # Business logic
├── resources/
│   └── views/
│       ├── layouts/               # Layout utama
│       ├── components/            # Navbar, footer
│       └── tools/                 # View setiap tools
├── public/
│   ├── css/                       # Stylesheet per tools
│   └── js/                        # JavaScript per tools
└── routes/
    └── web.php                    # Semua route
```

---

## 📝 Lisensi

Project ini dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">

Dibuat dengan ❤️ untuk produktivitas digital Indonesia

**[mediatools.cloud](https://mediatools.cloud)**

</div>
```

---

### 3. `LICENSE`

Buat file `LICENSE` (tanpa ekstensi) di root project:
```
MIT License

Copyright (c) 2026 MediaTools

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.