<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Models\UserFeedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class FeedbackSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public UserFeedback $feedback)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->feedback->loadMissing('user');

        $schoolName = trim((string) Setting::get('nama_sekolah', config('app.name'))) ?: (string) config('app.name');
        $instanceUrl = rtrim((string) config('app.url'), '/');
        $sender = $this->feedback->user?->displayName() ?? 'User dihapus';
        $role = $this->feedback->user?->roleLabel() ?? '-';
        $rating = $this->feedback->rating ? $this->feedback->rating.'/5' : '-';
        $contextUrl = $this->feedback->context_url ?: '-';
        $category = $this->feedback->categoryLabel();

        return (new MailMessage)
            ->subject('[Masukan] '.$schoolName.' · '.$category.' - '.$this->feedback->subject)
            ->greeting('Masukan baru diterima')
            ->line('Ada saran atau masukan baru yang masuk dari pengguna SIMS.')
            ->line('Sekolah: '.$schoolName)
            ->line('URL instance: '.$instanceUrl)
            ->line('Kategori: '.$category)
            ->line('Subjek: '.$this->feedback->subject)
            ->line('Pengirim: '.$sender.' ('.$role.')')
            ->line('Rating: '.$rating)
            ->line('Halaman asal: '.$contextUrl)
            ->line('Detail masukan:')
            ->line($this->feedback->message)
            ->action('Buka Detail Masukan', route('feedback.show', $this->feedback))
            ->line('Email ini hanya notifikasi. Status dan tindak lanjut tetap dikelola dari dashboard SIMS.');
    }
}
