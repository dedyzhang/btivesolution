<?php

namespace App\Notifications;

use App\Models\PresensiGuru;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Notifikasi ke Kepala Sekolah & Admin saat guru mengajukan izin pulang awal (verifikasi kamera sendiri). */
class GuruIzinPulangNotification extends Notification
{
    use Queueable;

    public function __construct(public PresensiGuru $presensi, public string $alasan)
    {
    }

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User || ! in_array($notifiable->access, ['kepala', 'admin', 'superadmin'], true)) {
            return [];
        }

        return ['database', FcmChannel::class];
    }

    public function toFcm(object $notifiable): array
    {
        $data = $this->toArray($notifiable);

        return [
            'title'   => $data['judul'],
            'message' => $data['message'],
            'url'     => $data['url'],
            'type'    => $data['type'],
            'sound'   => 'notif_sims',
        ];
    }

    public function toArray(object $notifiable): array
    {
        $guru = $this->presensi->guru;

        return [
            'type'        => 'presensi_izin_pulang',
            'presensi_id' => $this->presensi->uuid,
            'judul'       => 'Izin Pulang Awal',
            'message'     => ($guru->nama ?? 'Seorang guru').' izin pulang awal pukul '.substr((string) $this->presensi->jam_pulang, 0, 5).'. Alasan: '.Str::limit($this->alasan, 100),
            'url'         => '/presensi-guru/rekap',
        ];
    }
}
