<?php

namespace App\Notifications;

use App\Models\PresensiGuru;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Notifikasi ke Kepala Sekolah & Admin saat guru mengirim keterangan keterlambatan. */
class GuruTerlambatNotification extends Notification
{
    use Queueable;

    public function __construct(public PresensiGuru $presensi)
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
            'type'        => 'presensi_terlambat',
            'presensi_id' => $this->presensi->uuid,
            'judul'       => 'Guru Terlambat',
            'message'     => ($guru->nama ?? 'Seorang guru').' terlambat masuk pukul '.substr((string) $this->presensi->jam_masuk, 0, 5).'. '.Str::limit((string) $this->presensi->keterangan, 100),
            'url'         => '/presensi-guru/rekap',
        ];
    }
}
