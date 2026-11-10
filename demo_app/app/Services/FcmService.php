<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/*
| Pengirim push Firebase Cloud Messaging.
|
| Kirim Notification + data + Android priority high:
| - background/killed → system tray menampilkan (butuh channel di app)
| - foreground → SimsMessagingService.onMessageReceived menampilkan
| - data (url/type/…) tetap ada untuk deep link
*/
class FcmService
{
    private ?Messaging $messaging = null;

    private ?string $credentialsPath;

    /** Channel ID harus sama dengan strings.xml fcm_channel_id di semua APK sekolah. */
    private const ANDROID_CHANNEL_ID = 'maitreyawira_default';

    public function __construct()
    {
        $this->credentialsPath = $this->resolvePath(config('services.firebase.credentials'));
    }

    /** FCM aktif hanya bila file service account tersedia. */
    public function enabled(): bool
    {
        return $this->credentialsPath !== null && is_file($this->credentialsPath);
    }

    /**
     * Kirim satu pesan ke satu token. Melempar exception Kreait bila
     * token invalid/expired — biarkan pemanggil (job) yang menangani penghapusan.
     */
    public function send(string $token, array $payload): void
    {
        // FCM data map wajib string => string.
        $data = [];
        foreach ($payload as $key => $value) {
            if ($value !== null) {
                $data[$key] = (string) $value;
            }
        }

        $title = $data['title'] ?? 'Notifikasi';
        $body = $data['message'] ?? $data['body'] ?? '';
        // Alias agar klien Android yang baca "body" tetap dapat isi.
        if ($body !== '' && ! isset($data['body'])) {
            $data['body'] = $body;
        }
        if (! isset($data['message']) && $body !== '') {
            $data['message'] = $body;
        }

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body !== '' ? $body : ' '))
            ->withData($data)
            ->withAndroidConfig(AndroidConfig::fromArray([
                'priority' => 'high',
                'notification' => [
                    'channel_id' => self::ANDROID_CHANNEL_ID,
                    'sound' => 'default',
                    'default_vibrate_timings' => true,
                ],
            ]));

        $this->messaging()->send($message);
    }

    private function messaging(): Messaging
    {
        if ($this->messaging === null) {
            $this->messaging = (new Factory)
                ->withServiceAccount($this->credentialsPath)
                ->createMessaging();
        }

        return $this->messaging;
    }

    /** Path relatif (dari .env) diselesaikan terhadap root project. */
    private function resolvePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $isAbsolute = preg_match('/^([A-Za-z]:[\\\\\/]|\/)/', $path) === 1;

        return $isAbsolute ? $path : base_path($path);
    }
}
