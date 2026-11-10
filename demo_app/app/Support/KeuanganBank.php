<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Pengelola daftar bank/metode pembayaran SPP.
 *
 * Disimpan di tabel settings (key `keuangan_banks`) sebagai JSON sehingga
 * bendahara dapat menamb/mengubah lewat UI. Bila belum diatur, dipakai
 * daftar bawaan (DEFAULT) berisi bank umum + langkah transfernya.
 *
 * Field `nomor` mendukung placeholder {va} yang akan diganti dengan nomor
 * Virtual Account siswa (kolom siswa.va) saat ditampilkan ke ortu/siswa.
 */
class KeuanganBank
{
    public const SETTING_KEY = 'keuangan_banks';

    /** @return array<int, array{nama:string,atas_nama:string,nomor:string,warna:string,langkah:string[],aktif:bool}> */
    public const DEFAULT = [
        [
            'nama' => 'BCA', 'atas_nama' => 'SMP Maitreyawira', 'nomor' => '1234567890', 'warna' => '#0066AE', 'aktif' => true,
            'langkah' => [
                'Buka aplikasi BCA mobile / m-BCA, pilih menu m-Transfer.',
                'Pilih Transfer ke rekening BCA, masukkan nomor rekening di atas.',
                'Masukkan nominal SPP sesuai tagihan (harus PERSIS).',
                'Periksa nama penerima, lalu konfirmasi dengan PIN.',
                'Simpan bukti transfer, lalu unggah di aplikasi ini.',
            ],
        ],
        [
            'nama' => 'Mandiri', 'atas_nama' => 'SMP Maitreyawira', 'nomor' => '1300012345678', 'warna' => '#003D79', 'aktif' => true,
            'langkah' => [
                'Buka Livin\' by Mandiri, pilih Transfer.',
                'Pilih Bank Mandiri, masukkan nomor rekening di atas.',
                'Masukkan nominal SPP sesuai tagihan (harus PERSIS).',
                'Konfirmasi dan selesaikan pembayaran.',
                'Simpan bukti transfer, lalu unggah di aplikasi ini.',
            ],
        ],
        [
            'nama' => 'BNI', 'atas_nama' => 'SMP Maitreyawira', 'nomor' => '0123456789', 'warna' => '#F15A23', 'aktif' => true,
            'langkah' => [
                'Buka BNI Mobile Banking, pilih Transfer.',
                'Pilih Antar Rekening BNI, masukkan nomor rekening di atas.',
                'Masukkan nominal SPP sesuai tagihan (harus PERSIS).',
                'Masukkan password transaksi untuk konfirmasi.',
                'Simpan bukti transfer, lalu unggah di aplikasi ini.',
            ],
        ],
        [
            'nama' => 'BRI', 'atas_nama' => 'SMP Maitreyawira', 'nomor' => '003401000123456', 'warna' => '#00529C', 'aktif' => true,
            'langkah' => [
                'Buka BRImo, pilih menu Transfer.',
                'Pilih Sesama BRI, masukkan nomor rekening di atas.',
                'Masukkan nominal SPP sesuai tagihan (harus PERSIS).',
                'Konfirmasi dengan PIN BRImo.',
                'Simpan bukti transfer, lalu unggah di aplikasi ini.',
            ],
        ],
    ];

    /**
     * Daftar bank tersimpan (semua, termasuk non-aktif) untuk pengaturan.
     *
     * @return array<int, array>
     */
    public static function all(): array
    {
        $raw = Setting::get(self::SETTING_KEY);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }
        if (!is_array($raw) || empty($raw)) {
            return self::DEFAULT;
        }
        return array_values(array_map(fn ($b) => self::normalize($b), $raw));
    }

    /**
     * Daftar bank aktif untuk ditampilkan ke ortu/siswa, dengan {va} diganti
     * nomor VA siswa.
     *
     * @return array<int, array>
     */
    public static function active(?string $va = null): array
    {
        $list = array_values(array_filter(self::all(), fn ($b) => !empty($b['aktif'])));
        if ($va !== null) {
            foreach ($list as &$b) {
                $b['nomor'] = str_replace('{va}', $va, (string) ($b['nomor'] ?? ''));
            }
            unset($b);
        }
        return $list;
    }

    public static function save(array $banks): void
    {
        $clean = array_values(array_map(fn ($b) => self::normalize($b), $banks));
        Setting::set(self::SETTING_KEY, json_encode($clean, JSON_UNESCAPED_UNICODE));
    }

    /** @return array{nama:string,atas_nama:string,nomor:string,warna:string,langkah:string[],aktif:bool} */
    private static function normalize(array $b): array
    {
        $langkah = $b['langkah'] ?? [];
        if (is_string($langkah)) {
            // Dari textarea: satu langkah per baris.
            $langkah = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $langkah))));
        }
        return [
            'nama'      => trim((string) ($b['nama'] ?? '')),
            'atas_nama' => trim((string) ($b['atas_nama'] ?? '')),
            'nomor'     => trim((string) ($b['nomor'] ?? '')),
            'warna'     => trim((string) ($b['warna'] ?? '#64748b')) ?: '#64748b',
            'langkah'   => array_values($langkah),
            'aktif'     => (bool) ($b['aktif'] ?? false),
        ];
    }
}
