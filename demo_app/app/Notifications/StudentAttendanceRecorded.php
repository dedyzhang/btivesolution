<?php

namespace App\Notifications;

use App\Models\Absensi;
use App\Models\Orangtua;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi untuk orang tua saat status absensi anak berubah
 * (hadir via wajah/QR/manual, atau izin/sakit/alpa dari form).
 */
class StudentAttendanceRecorded extends Notification
{
    use Queueable;

    public function __construct(public Siswa $siswa, public Absensi $absensi) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User || $notifiable->access !== 'orangtua') {
            return [];
        }

        // Hanya orang tua yang terhubung ke siswa ini.
        $isParent = Orangtua::query()
            ->where('id_login', $notifiable->getKey())
            ->where('id_siswa', $this->siswa->uuid)
            ->exists();

        if (! $isParent) {
            return [];
        }

        return ['database', FcmChannel::class];
    }

    public function toFcm(object $notifiable): array
    {
        $data = $this->toArray($notifiable);

        return [
            'title' => $data['title'],
            'message' => $data['message'],
            'url' => '/dashboard',
            'type' => 'absensi_siswa',
            'sound' => 'notif_sims',
        ];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->absensi->status ?: 'hadir';
        $statusLabel = Absensi::STATUS[$status] ?? ucfirst($status);
        $jam = substr((string) $this->absensi->jam_masuk, 0, 5);
        $kelas = $this->siswa->kelas
            ? trim($this->siswa->kelas->tingkat.' '.$this->siswa->kelas->kelas)
            : null;
        $tanggal = $this->absensi->tanggal?->format('Y-m-d');

        [$judul, $message] = $this->copyForStatus($status, $statusLabel, $jam, $tanggal);

        return [
            'type' => 'absensi_siswa',
            'judul' => $judul,
            'title' => $judul,
            'message' => $message,
            'url' => '/dashboard',
            'siswa_id' => $this->siswa->uuid,
            'siswa_nama' => $this->siswa->nama,
            'kelas' => $kelas,
            'tanggal' => $tanggal,
            'jam_masuk' => $jam !== '' ? $jam : null,
            'status' => $status,
        ];
    }

    /** @return array{0: string, 1: string} */
    private function copyForStatus(string $status, string $statusLabel, string $jam, ?string $tanggal): array
    {
        $nama = $this->siswa->nama;

        return match ($status) {
            'hadir' => [
                'Anak sudah masuk sekolah',
                $jam !== ''
                    ? 'Ananda '.$nama.' sudah tercatat hadir pukul '.$jam.'.'
                    : 'Ananda '.$nama.' sudah tercatat hadir.',
            ],
            'izin' => [
                'Anak tercatat izin',
                'Ananda '.$nama.' tercatat izin'.($tanggal ? ' pada '.$tanggal : '').'.',
            ],
            'sakit' => [
                'Anak tercatat sakit',
                'Ananda '.$nama.' tercatat sakit'.($tanggal ? ' pada '.$tanggal : '').'.',
            ],
            'alpa' => [
                'Anak tercatat alpa',
                'Ananda '.$nama.' tercatat alpa'.($tanggal ? ' pada '.$tanggal : '').'.',
            ],
            default => [
                'Update absensi anak',
                'Ananda '.$nama.' tercatat '.$statusLabel.($tanggal ? ' pada '.$tanggal : '').'.',
            ],
        };
    }
}
