# Keamanan SIMS — Hardening & Checklist Produksi

Catatan hasil audit keamanan (Juli 2026). Konteks penting: kode PHP **tidak pernah
dikirim ke browser** — yang bisa "dicopy" pengunjung hanya HTML/CSS/JS hasil render,
dan itu tidak mungkin disembunyikan (DevTools/`curl` selalu bisa). Perlindungan
codebase yang nyata = amankan server & tutup celah, bukan disable klik kanan.

## Yang sudah dikerjakan

1. **Kebocoran data akun guru (KRITIS — perlu tindak lanjut manual, lihat bawah).**
   `public/scratch/akun_guru_revisi.xlsx` dan `insert_guru_revisi.sql` (nama, username,
   bcrypt hash password guru) bisa diunduh publik lewat URL. Sudah dipindah ke
   `storage/app/scratch/` (di-gitignore, tak bisa diakses via web).
2. **Upload rename `.php` (celah RCE).** Validasi `mimes:` Laravel memeriksa *isi*
   file, bukan nama — file gambar/PDF valid yang dinamai `shell.php` lolos validasi
   dan tersimpan dengan ekstensi `.php` di folder publik → bisa dieksekusi server.
   Diperbaiki dengan `App\Support\Uploads::safeExtension()` (ekstensi klien hanya
   dipakai bila masuk allowlist) di ChatbotController, ChatbotAdminController, dan
   SettingController (logo & kop).
3. **Lapisan kedua anti-eksekusi di folder upload.** `.htaccess` di `public/uploads/`
   dan `storage/app/public/` menolak akses file `.php/.phtml/.phar/...` dan mematikan
   engine PHP (Apache). **Catatan nginx:** `.htaccess` tidak berlaku — tambahkan
   `location ~* ^/(uploads|storage)/.*\.php$ { deny all; }` di server block.
4. **Header keamanan** via `App\Http\Middleware\SecurityHeaders` (grup `web`):
   `X-Frame-Options: SAMEORIGIN` (anti-clickjacking), `X-Content-Type-Options:
   nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`
   (kamera hanya untuk halaman sendiri — dipakai absensi wajah & scan QR), dan HSTS
   saat HTTPS.

## Yang sudah baik (tidak diubah)

- Rate limit login per (kredensial+IP) dan per IP — `AppServiceProvider`.
- Tidak ada raw SQL berisi input user; semua query lewat binding Eloquent.
- Tidak ada output Blade `{!! !!}` yang berisiko XSS.
- File APK/installer & bukti SPP disimpan di disk privat `local`, diunduh lewat
  route ber-auth.
- CSRF aktif (default grup `web`), session `http_only` + `same_site=lax`.

## Tindak lanjut manual (PENTING)

- [ ] **Reset password semua akun guru** yang ada di `insert_guru_revisi.sql`.
      File itu sudah terlanjur masuk git history dan terpush ke GitHub
      (`dedyzhang/sims_app` commit `e9cb499`, juga di fork). Hash bcrypt bisa
      di-crack offline kalau passwordnya lemah/default.
- [ ] Idealnya hapus file dari history GitHub: `git filter-repo` + force push +
      minta GitHub support membersihkan cache, ATAU jadikan repo private.

## Checklist sebelum production

- [ ] `APP_ENV=production`, `APP_DEBUG=false` (debug=true membocorkan source & env di halaman error!)
- [ ] `SESSION_SECURE_COOKIE=true` (situs sudah HTTPS)
- [ ] HTTPS wajib; HSTS otomatis aktif dari middleware
- [ ] `php artisan config:cache && route:cache && view:cache`
- [ ] Pastikan document root mengarah ke `public/` (bukan root project — kalau salah, `.env` bisa diunduh orang)
- [ ] Jangan taruh file kerja/dump apa pun di `public/` — pakai `storage/app/`
- [ ] Backup database rutin + simpan di luar server
- [ ] Update dependensi berkala: `composer update` + cek `composer audit`
