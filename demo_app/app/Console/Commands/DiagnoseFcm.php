<?php

namespace App\Console\Commands;

use App\Models\UserFcmToken;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Throwable;

/**
 * Diagnosa kenapa FCM tidak jalan di satu hosting padahal .env dan
 * service-account.json "sama" dengan hosting lain. Semua kegagalan FCM
 * normalnya SILENT (lihat FcmChannel) — command ini sengaja MEMBOCORKAN
 * exception asli dari Google supaya penyebabnya ketahuan.
 */
class DiagnoseFcm extends Command
{
    protected $signature = 'fcm:diagnose';
    protected $description = 'Cek kredensial, token, queue, dan koneksi FCM — tampilkan penyebab kegagalan sebenarnya';

    public function handle(): int
    {
        $this->info('=== 1. Kredensial service account ===');
        $rawPath = config('services.firebase.credentials');
        $this->line("Path di .env (FIREBASE_CREDENTIALS): {$rawPath}");

        $isAbsolute = $rawPath && preg_match('/^([A-Za-z]:[\\\\\/]|\/)/', $rawPath) === 1;
        $resolved = $rawPath ? ($isAbsolute ? $rawPath : base_path($rawPath)) : null;
        $this->line("Path absolut yang dipakai app: " . ($resolved ?: '(kosong)'));

        if (!$resolved || !is_file($resolved)) {
            $this->error('❌ File TIDAK ditemukan di path di atas. Cek nama folder/file persis (huruf besar-kecil di Linux berpengaruh!), dan pastikan file benar-benar ada di server ini (bukan cuma di lokal/server lain).');
            return self::FAILURE;
        }
        $this->info('✔ File ditemukan.');

        if (!is_readable($resolved)) {
            $this->error('❌ File ADA tapi TIDAK BISA DIBACA oleh PHP (masalah permission/owner). Cek: ls -la ' . $resolved);
            return self::FAILURE;
        }
        $this->info('✔ File bisa dibaca.');

        $size = filesize($resolved);
        $this->line("Ukuran file: {$size} bytes");
        if ($size < 100) {
            $this->error('❌ File terlalu kecil / kemungkinan kosong atau corrupt.');
            return self::FAILURE;
        }

        $json = json_decode((string) file_get_contents($resolved), true);
        if (!is_array($json) || empty($json['project_id']) || empty($json['client_email'])) {
            $this->error('❌ Isi file bukan JSON service-account yang valid (project_id / client_email tidak ada).');
            return self::FAILURE;
        }
        $this->info('✔ JSON valid.');
        $this->line("  project_id   : {$json['project_id']}");
        $this->line("  client_email : {$json['client_email']}");
        $this->warn('  → COCOKKAN project_id di atas dengan project Firebase yang dipakai APK Android (google-services.json). Kalau beda, token dari APK itu TIDAK akan valid untuk kredensial ini walau .env & file JSON-nya persis sama dengan hosting lain.');

        $this->newLine();
        $this->info('=== 2. Token perangkat tersimpan ===');
        $total = UserFcmToken::count();
        $this->line("Total token FCM tersimpan di DB: {$total}");
        if ($total === 0) {
            $this->error('❌ Tidak ada token sama sekali → tidak ada perangkat yang dikirimi apa pun, walau kredensialnya benar. Kemungkinan APK di hosting ini gagal daftar token (cek endpoint /notifications/fcm-token dari sisi APK), atau memang belum ada user yang login dari APK di instance ini.');
        } else {
            $recent = UserFcmToken::latest('updated_at')->first();
            $this->info("✔ Ada token. Terbaru diperbarui: {$recent?->updated_at?->diffForHumans()}");
        }

        $this->newLine();
        $this->info('=== 3. Konfigurasi queue ===');
        $this->line('QUEUE_CONNECTION       : ' . config('queue.default'));
        $this->line('FCM_QUEUE_CONNECTION   : ' . config('services.firebase.queue_connection', 'sync (default)'));
        if (config('queue.default') !== 'sync' && env('FCM_QUEUE_CONNECTION') === null) {
            $this->warn('⚠ FCM_QUEUE_CONNECTION belum diset eksplisit di .env, dan QUEUE_CONNECTION bukan "sync" → job FCM akan MASUK ANTREAN, bukan langsung kirim. Pastikan ada worker aktif (php artisan queue:work) atau supervisor untuk connection tsb, kalau tidak notifikasi akan menumpuk diam-diam di tabel jobs.');
        } else {
            $this->info('✔ Konfigurasi queue tidak akan menahan pengiriman FCM (jalan sync).');
        }

        $this->newLine();
        $this->info('=== 4. Tes koneksi & autentikasi nyata ke Google (dry-run, tidak mengirim notif sungguhan) ===');
        try {
            $messaging = (new Factory())->withServiceAccount($resolved)->createMessaging();
            $message = CloudMessage::withTarget('token', 'diagnose-dummy-token-tidak-nyata')
                ->withData(['title' => 'diagnose', 'message' => 'diagnose']);
            $messaging->send($message, validateOnly: true);
            $this->info('✔ Autentikasi ke Google BERHASIL dan format pesan valid.');
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            // Token dummy pasti ditolak Google — itu WAJAR & justru bukti auth-nya jalan.
            if (str_contains($msg, 'not a valid FCM registration token') || str_contains($msg, 'is not a valid FCM') || str_contains($msg, 'Requested entity was not found') || str_contains($msg, 'InvalidArgument') || str_contains($msg, 'invalid-registration-token') || str_contains($msg, 'UNREGISTERED')) {
                $this->info('✔ Autentikasi ke Google BERHASIL (error di bawah ini soal "token tidak valid" itu WAJAR karena kita memang pakai token palsu, bukan error kredensial).');
                $this->line('  Detail: ' . $msg);
            } else {
                $this->error('❌ GAGAL autentikasi/koneksi ke Google — INI KEMUNGKINAN BESAR AKAR MASALAHNYA:');
                $this->error('  Class     : ' . get_class($e));
                $this->error('  Message   : ' . $msg);
                $this->newLine();
                $this->warn('  Penyebab umum error semacam ini:');
                $this->warn('  - "invalid_grant" / "JWT ... expired" → JAM SERVER tidak sinkron (cek: date, lalu bandingkan dgn waktu asli).');
                $this->warn('  - "cURL error 6/7/28" / connection timeout → firewall/hosting blokir outbound ke oauth2.googleapis.com / fcm.googleapis.com.');
                $this->warn('  - "invalid_client" / "Requested entity was not found" (di step auth, bukan di step token) → service account sudah dihapus/nonaktif di Firebase Console, atau JSON dari project yang salah/lama.');
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('=== Kesimpulan ===');
        $this->line('Kredensial & koneksi ke Google terlihat OK. Kalau notifikasi tetap tidak sampai ke HP, curigai:');
        $this->line('1. project_id service account TIDAK SAMA dengan project Firebase di APK Android hosting ini (poin 1 di atas).');
        $this->line('2. Token di poin 2 memang 0 / basi (APK di hosting ini belum pernah daftar token, atau usernya belum pernah login dari APK).');

        return self::SUCCESS;
    }
}
