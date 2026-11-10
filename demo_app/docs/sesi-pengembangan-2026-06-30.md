# Catatan Sesi — Modul Keuangan (SPP), Dashboard Kustom, dan Penyelarasan Sarpras

**Tanggal:** 30 Juni 2026
**Branch:** `feat/keuangan-spp`
**Aplikasi:** SIMS MW (Laravel 12, SQLite, Tailwind CDN + Alpine)

Dokumen ini merekam seluruh pekerjaan pengembangan terbaru di aplikasi SIMS-NET (fokus pada Keuangan, Dashboard, dan Penyelarasan Sarpras) beserta hasil pengetesan fungsionalnya (backtest).

---

## Ringkasan Fitur & Perubahan Terbaru (Kronologis)

1. **Modul Keuangan & SPP (28 Juni 2026)**
   - **Bendahara SPP Grid:** Menyediakan visualisasi grid 12 bulan (Juli - Juni) per kelas untuk mempermudah monitoring status pembayaran siswa.
   - **Tahun Ajaran:** Mengimplementasikan helper `TahunAjaran` untuk standarisasi siklus pembayaran Juli s.d Juni tahun berikutnya.
   - **Upload Bukti & Verifikasi:** Alur lengkap untuk Siswa/Orang Tua mengunggah bukti pembayaran (bisa sekaligus multi-bulan) dan Bendahara memverifikasi atau menolak secara satuan/batch.
   - **Virtual Account & Nominal Custom:** Bendahara dapat mengatur nomor VA manual per siswa beserta nilai SPP bulanan yang fleksibel.
   - **KeuanganBank:** Penambahan helper list bank pendukung pembayaran.

2. **Dashboard Kustom Drag-and-Drop (29 Juni 2026)**
   - **Blok Dinamis:** Dashboard dipecah menjadi beberapa bagian mandiri (`stats`, `ringkasan`, `sarpras`, `recent`).
   - **Drag & Drop:** Pengguna dapat menyusun tata letak blok tersebut sesuka hati. Urutan tata letak disimpan ke tabel `user_preferences` kolom `dashboard_layout` (JSON) via endpoint POST `/dashboard/tata-letak`.

3. **Ticker Statistik & Booking Hari Ini (30 Juni 2026)**
   - **TickerStats Helper:** Kelas sentral `App\Support\TickerStats` digunakan untuk mengumpulkan data statistik (jumlah siswa, guru, kelas, dsb.) dengan caching selama 60 detik demi meminimalisir beban query agregat saat halaman dimuat.
   - **Endpoint Polling:** GET `/dashboard/ticker-stats` untuk pembaruan data real-time pada layout.
   - **Dashboard Sarpras:** Ditambahkan visualisasi list booking ruangan hari ini (status: diajukan/disetujui) pada dashboard sarpras.

4. **Penyelarasan Modul Sarpras (30 Juni 2026)**
   - **Penyusutan Nilai Buku:** Ditambahkan properti `masa_manfaat_tahun` di tabel aset dan logika penyusutan garis lurus pada Model `Aset` (`penyusutanTahunan()`, `akumulasiPenyusutan()`, `nilaiBuku()`).
   - **Informasi Aset Lengkap:** Halaman Aset menampilkan kartu statistik total aset, nilai perolehan, nilai buku saat ini, donut chart kondisi, dan neraca per kategori.
   - **Alur Booking & Ruangan:** Bidang denah ruangan ditambahkan metadata detail (gedung, lantai, fasilitas, status). Form booking ruangan dilengkapi validasi anti-bentrok jadwal di sisi backend.
   - **Peminjaman, Perbaikan, & Supplier:** Pemisahan panel transaksi peminjaman aset dan reservasi ruangan, log pemeliharaan/perbaikan dengan status selesai & teknisi, serta pengelolaan data supplier.

5. **Penyatuan Navigasi Sarpras ke Sidebar (30 Juni 2026)**
   - **Pembersihan Layout:** Menghapus tab navigasi horizontal di bagian atas modul Sarpras ([app.blade.php](file:///d:/sims_app/resources/views/sarpras/layouts/app.blade.php)).
   - **Integrasi Sidebar:** Memindahkan dan menyusun ulang seluruh 11 rute navigasi Sarpras (termasuk "Ruangan & Booking" dan "Supplier") ke dalam sub-grup "Sarana & Prasarana" pada sidebar utama ([app.blade.php](file:///d:/sims_app/resources/views/layouts/app.blade.php)).
   - **Penyelerasan Otorisasi:** Menambahkan rute "Ruangan & Booking" (`sarpras.booking.index`) ke dalam filter `$bolehTerbatas` agar peran guru dan staf non-admin tetap dapat mengajukan reservasi ruangan dari sidebar secara aman.

6. **Pembersihan Profil Bawah & Penyesuaian Lebar Ticker (30 Juni 2026)**
   - **Hapus Redundansi:** Menghapus kartu informasi profil di bagian bawah sidebar ([app.blade.php](file:///d:/sims_app/resources/views/layouts/app.blade.php)) karena navigasi profil yang sama sudah tersedia di topbar.
   - **Perpanjang Area Navigasi:** Mengubah padding kontainer navigasi `<nav>` dengan menambahkan `pb-6` agar menu memanjang penuh ke bawah dengan batas scroll yang proporsional.
   - **Lebar Penuh Ticker:** Mengubah struktur tata letak pembungkus (wrapper) root menjadi vertikal flexbox dan memindahkan `SIMS-NET` Ticker Bar ke bagian terluar. Hal ini membuat Ticker Bar memanjang penuh (`100vw`) dari ujung kiri (di bawah sidebar) hingga ke ujung kanan layar.

7. **Penyelarasan & Interaktivitas Dashboard Sarpras (30 Juni 2026)**
   - **Tautan Navigasi Interaktif:** Menghubungkan seluruh kartu statistik utama (Total Aset, Nilai Aset, Kerusakan Terbuka, Peminjaman Aktif) dengan rute list data masing-masing menggunakan visualisasi hover premium.
   - **Visualisasi Donut Chart:** Mengubah representasi "Kondisi Fisik Barang" dari sekadar daftar statis menjadi Donut Chart dengan conic-gradient interaktif yang jika diklik akan langsung memfilter daftar Aset sesuai dengan kondisi yang dipilih.
   - **Widget Laporan Kerusakan Terbaru:** Menampilkan list laporan kerusakan terakhir yang masuk (dengan badge status dinamis) dan menautkannya langsung ke detail laporan kerusakan untuk penanganan cepat.
   - **Eager Loading Optimization:** Memperbarui query di `DashboardController` untuk memuat relasi pelapor dan aset guna mencegah masalah query N+1 pada data kerusakan terbaru.

8. **Modernisasi Tombol & Tampilan Laporan Sarpras (30 Juni 2026)**
   - **Tombol Ekspor & Aksi Capsule Kustom:** Mengganti tombol ekspor dan impor dengan desain pill/capsule bulat penuh (`rounded-full`) dan warna yang diselaraskan secara presisi sesuai mockup gambar (Soft Emerald dengan Teks/Ikon Emerald Gelap untuk Excel, Import Excel, Proses Import, Import Denah, & Tambah Lantai, Soft Rose dengan Teks/Ikon Rose Gelap untuk PDF, Cetak Label, & Berita Acara PDF, Soft Slate dengan Teks/Ikon Slate Gelap untuk Log Aktivitas, Dark Gray pill untuk Tambah Aset, Kategori, Gedung, Mutasi, Ajukan Penggunaan Ruang, Pengajuan Pengadaan, Tambah Supplier, Teknisi, Ajukan Penghapusan, & Jadwal Pemeliharaan, serta Red/Rose solid pill untuk Lapor Kerusakan). Perubahan ini diimplementasikan di:
     - Laporan Sarpras ([laporan.index](file:///d:/sims_app/resources/views/sarpras/laporan/index.blade.php))
     - Inventaris Barang ([aset.index](file:///d:/sims_app/resources/views/sarpras/aset/index.blade.php))
     - Detail Aset ([aset.show](file:///d:/sims_app/resources/views/sarpras/aset/show.blade.php))
     - Kategori Aset ([kategori.index](file:///d:/sims_app/resources/views/sarpras/kategori/index.blade.php))
     - Detail Gedung & Ruangan ([denah.show](file:///d:/sims_app/resources/views/sarpras/denah/show.blade.php))
     - Denah Gedung & Lantai ([denah.index](file:///d:/sims_app/resources/views/sarpras/denah/index.blade.php))
     - Detail Ruangan ([denah.ruangan](file:///d:/sims_app/resources/views/sarpras/denah/ruangan.blade.php))
     - Partials Import Denah ([denah.partials.import-button](file:///d:/sims_app/resources/views/sarpras/denah/partials/import-button.blade.php))
     - Mutasi Aset ([mutasi.index](file:///d:/sims_app/resources/views/sarpras/mutasi/index.blade.php))
     - Ruangan & Peminjaman ([booking.index](file:///d:/sims_app/resources/views/sarpras/booking/index.blade.php))
     - Pelaporan Kerusakan ([kerusakan.index](file:///d:/sims_app/resources/views/sarpras/kerusakan/index.blade.php))
     - Pengadaan Barang ([pengadaan.index](file:///d:/sims_app/resources/views/sarpras/pengadaan/index.blade.php))
     - Manajemen Supplier ([supplier.index](file:///d:/sims_app/resources/views/sarpras/supplier/index.blade.php))
     - Manajemen Teknisi ([teknisi.index](file:///d:/sims_app/resources/views/sarpras/teknisi/index.blade.php))
     - Penghapusan Aset ([penghapusan.index](file:///d:/sims_app/resources/views/sarpras/penghapusan/index.blade.php))
     - Detail Penghapusan Aset ([penghapusan.show](file:///d:/sims_app/resources/views/sarpras/penghapusan/show.blade.php))
     - Jadwal Pemeliharaan ([jadwal.index](file:///d:/sims_app/resources/views/sarpras/jadwal/index.blade.php))
   - **Penyelarasan Desain Card & Tabel:** Memodifikasi layout rekap kondisi dan statistik nilai aset menggunakan desain `.card` standar dan `.data-table` premium dengan indikator warna kondisi yang bulat minimalis.

9. **Fitur Cepat Manajemen Ruangan (30 Juni 2026)**
   - **Form Cepat Tambah Ruangan:** Menyediakan panel ekspansif ("Tambah Ruangan Baru") secara langsung di halaman detail denah ([denah.show](file:///d:/sims_app/resources/views/sarpras/denah/show.blade.php)) serta di halaman utama Ruangan & Peminjaman ([booking.index](file:///d:/sims_app/resources/views/sarpras/booking/index.blade.php)) dilengkapi dengan pilihan Denah/Lantai target. Koordinat diposisikan di tengah (50%, 50%) agar dapat ditata kemudian.
   - **Fallback Gedung & Lantai:** Memperbarui `RuanganController::store` agar jika kolom gedung/lantai tidak dikirim, otomatis mewarisi (inherit) nilai gedung dan lantai dari objek `Denah` induknya.
   - **Penghapusan Ruangan Langsung:** Menambahkan tombol hapus (ikon sampah merah) di samping masing-masing ruangan pada daftar ruangan di halaman detail denah ([denah.show](file:///d:/sims_app/resources/views/sarpras/denah/show.blade.php)) serta di bagian footer kartu ruangan pada halaman utama Ruangan & Peminjaman ([booking.index](file:///d:/sims_app/resources/views/sarpras/booking/index.blade.php)) dengan modal konfirmasi keselamatan (`confirmAction`).
   - **Form Edit Ruangan Instan (Pena):** Menambahkan tombol edit (ikon pena `edit-2` biru) di samping tombol hapus di kedua halaman tersebut. Mengklik tombol ini akan men-trigger modal pop-up interaktif berbasis Alpine.js untuk memodifikasi Kode Ruangan, Nama Ruangan, Kapasitas, dan Warna Blok tanpa berpindah halaman.
   - **Tata Letak Denah Gedung (Grid Horizontal):** Mengubah daftar kelompok gedung pada halaman index denah ([denah.index](file:///d:/sims_app/resources/views/sarpras/denah/index.blade.php)) menjadi layout grid responsif (`grid-cols-1 lg:grid-cols-2`) sehingga gedung-gedung ditampilkan menyamping (side-by-side) alih-alih menumpuk ke bawah, guna mengoptimalkan area kosong pada layar desktop.
   - **Resolusi Validasi Koordinat & Pemisahan Error Modal:** Menggunakan modifier `sometimes` untuk validasi `pos_x`/`pos_y` di `RuanganRequest`, mengirimkan koordinat aktual via hidden inputs pada modal edit ruangan di kedua halaman, dan memperbarui pendeteksi error Alpine (`$errors->has(...)`) agar error validasi input ruangan tidak salah memicu terbukanya modal booking ruangan.
   - **Akses Pintas Editor Layout Drag & Drop:** Menyediakan tombol shortcut aksi "Move/Pindah" (ikon `move` hijau) di samping tombol edit/hapus pada daftar ruangan di kedua halaman untuk langsung masuk to editor visual/hotspot denah terkait.
   - **Fitur Tata Letak Mandiri Per-Menu (Lokal):** Mengubah fungsi tombol "Atur Tata Letak" di header utama ([layouts.app](file:///d:/sims_app/resources/views/sarpras/layouts/app.blade.php)) menjadi pengendali Layout Edit Mode pada halaman aktif. Mode ini tidak bergantung pada denah interaktif, tetapi memungkinkan pengguna untuk menyeret (drag-and-drop) dan menyusun ulang kartu/widget di menu aktif (Dashboard, Ruangan, Aset, Gedung/Lantai) sesuai selera. Susunan layout disimpan secara instan dan persisten di browser pengguna menggunakan `localStorage`.
   - **Penyelarasan Desain Tombol Tata Letak:** Memperbarui visual tombol "Atur Tata Letak" pada header layout utama Sarpras ([layouts.app](file:///d:/sims_app/resources/views/sarpras/layouts/app.blade.php)) agar menggunakan styling kelas `btn-accent` yang dinamis mengikuti warna aksen preferensi pengguna, serta memiliki label teks "Tata Letak" / "Selesai" dengan ikon `layout-dashboard` / `check` yang sejalan dengan dashboard utama aplikasi SIMS.
   - **Desain Kartu Lantai Denah Horizontal:** Mengubah kartu lantai denah pada [denah.index](file:///d:/sims_app/resources/views/sarpras/denah/index.blade.php) menjadi horizontal (`flex-row` side-by-side) dengan gambar/placeholder di sebelah kiri dan informasi serta tombol aksi di sebelah kanan, sehingga mengoptimalkan ruang kosong dan mencegah link terputus/squished.
   - **Restriksi Halaman Tombol Tata Letak:** Membatasi visibilitas tombol "Tata Letak" pada header layout utama Sarpras ([layouts.app](file:///d:/sims_app/resources/views/sarpras/layouts/app.blade.php)) agar hanya muncul di halaman Dashboard (`sarpras.dashboard`), halaman utama Gedung & Lantai (`sarpras.denah.index`), dan halaman detail gedung/denah (`sarpras.denah.show`). Tombol disembunyikan secara otomatis pada sub-menu sidebar lainnya (seperti Pengadaan, Peminjaman Aset, Perbaikan, Mutasi, Supplier, Laporan, dll) karena tidak memerlukan tata letak kartu dinamis.
   - **Penyamaan Elemen Visual Dashboard (Global Theme):** Menghapus kondisional `$isMaitreyawira` pada bagian block statistik ([stats.blade.php](file:///d:/sims_app/resources/views/dashboard/blocks/stats.blade.php)) dan block komposisi siswa ([recent.blade.php](file:///d:/sims_app/resources/views/dashboard/blocks/recent.blade.php)), sehingga ikon statistik interaktif dan gauge donut chart premium yang sebelumnya hanya muncul pada tema "Awan Biru" (Maitreyawira) kini ditampilkan secara seragam di seluruh tema.

---

## Status & Hasil Pengetesan (Backtest)

Seluruh test suite Laravel telah dijalankan (menggunakan perintah `php artisan test`) untuk memverifikasi fungsionalitas dan mencegah regresi.

**Hasil:**
- **Total Tests:** 112 passed
- **Total Assertions:** 379 assertions
- **Waktu Eksekusi:** 8.48s
- **Status:** **100% LULUS / PASS**

### Rincian Uji Fitur Utama (Feature Tests):
- **`KeuanganSppTest` (19 tests):**
  - Menguji aksesibilitas bendahara & guard role lain.
  - Verifikasi pembuatan otomatis 12 bulan baris SPP per siswa ketika grid kelas dibuka pertama kali.
  - Menguji alur upload bukti pembayaran tunggal/batch oleh orang tua/siswa.
  - Menguji alur verifikasi dua tahap (menunggu -> terverifikasi lunas) dan penolakan batch oleh bendahara.
  - Menguji pencarian nama & kelas pada grid verifikasi.
- **`SarprasBookingTest` (3 tests):**
  - Validasi alur booking baru berstatus `diajukan`.
  - Menguji penolakan booking otomatis jika mendeteksi jadwal bentrok di rentang waktu yang sama.
  - Menguji persetujuan (setujui/tolak) booking ruangan oleh admin/sarpras.
- **`SarprasPerbaikanTest` (1 test):**
  - Menguji penandaan perbaikan aset selesai dan log pemeliharaan terkait.
- **`SmokeTest` (31 tests):**
  - Melakukan penelusuran (smoke-test) ke semua rute penting (sarpras aset, sarpras booking, keuangan verifikasi, data guru/kelas/siswa, kalender absensi, dll.) untuk memastikan tidak ada rute yang mengembalikan HTTP Error 500.

---

## Keputusan Arsitektur Penting

| Fitur | Pendekatan | Manfaat / Alasan |
|---|---|---|
| **Cache Ticker** | Cache data statistik di `TickerStats` selama 60s | Menghindari query agregat SQL berulang pada setiap reload halaman menu utama. |
| **Grid SPP 12 Bulan** | Pembuatan 12 baris records per tahun ajaran per siswa saat Grid dibuka | Data pembayaran lebih terstruktur secara baris database, mempermudah pelacakan per bulan secara eksplisit (Juli–Juni). |
| **Penyusutan Straight-Line** | Dihitung real-time via method model berdasarkan `masa_manfaat_tahun` | Memastikan data nilai buku selalu ter-update secara matematis tanpa perlu cronjob harian pengurang nilai. |
| **Validasi Bentrok Ruangan** | Query SQLite dengan logic overlaps rentang waktu Carbon | Menjamin tidak ada double booking ruang kelas/laboratorium untuk jam yang sama. |

---

## Lokasi File & Kode Utama

- **Keuangan & SPP:**
  - Controllers: [KeuanganController.php](file:///d:/sims_app/app/Http/Controllers/Keuangan/KeuanganController.php) , [TagihanController.php](file:///d:/sims_app/app/Http/Controllers/Keuangan/TagihanController.php)
  - Models & Services: [SppPembayaran.php](file:///d:/sims_app/app/Models/SppPembayaran.php), [SppService.php](file:///d:/sims_app/app/Services/Keuangan/SppService.php)
  - Helpers: [TahunAjaran.php](file:///d:/sims_app/app/Support/TahunAjaran.php), [KeuanganBank.php](file:///d:/sims_app/app/Support/KeuanganBank.php)
  - Views: `resources/views/keuangan/` (termasuk subfolder `tagihan`)
  - Tests: [KeuanganSppTest.php](file:///d:/sims_app/tests/Feature/KeuanganSppTest.php)

- **Dashboard & Ticker:**
  - Controller: [DashboardController.php](file:///d:/sims_app/app/Http/Controllers/DashboardController.php)
  - Helper & Cache: [TickerStats.php](file:///d:/sims_app/app/Support/TickerStats.php)
  - Views Blocks: `resources/views/dashboard/blocks/`
  - Drag & Drop UI: [dashboard.blade.php](file:///d:/sims_app/resources/views/dashboard.blade.php)

- **Sarpras (Penyusutan, Booking, Peminjaman, Perbaikan):**
  - Controllers: `app/Sarpras/Http/Controllers/` ([AsetController.php](file:///d:/sims_app/app/Sarpras/Http/Controllers/AsetController.php), [BookingController.php](file:///d:/sims_app/app/Sarpras/Http/Controllers/BookingController.php), [PerbaikanController.php](file:///d:/sims_app/app/Sarpras/Http/Controllers/PerbaikanController.php))
  - Models: [Aset.php](file:///d:/sims_app/app/Sarpras/Models/Aset.php), [DenahRuangan.php](file:///d:/sims_app/app/Sarpras/Models/DenahRuangan.php)
  - Views: `resources/views/sarpras/`
  - Tests: [SarprasBookingTest.php](file:///d:/sims_app/tests/Feature/SarprasBookingTest.php), [SarprasPerbaikanTest.php](file:///d:/sims_app/tests/Feature/SarprasPerbaikanTest.php)
