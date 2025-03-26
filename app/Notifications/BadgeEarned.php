<?php

namespace App\Notifications;

use App\Models\Badge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BadgeEarned extends Notification implements ShouldQueue
{
    use Queueable;

    protected Badge $badge;

    /**
     * Create a new notification instance.
     *
     * @param Badge $badge The badge that was earned
     */
    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Congratulations! You\'ve earned a new badge')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Congratulations! You\'ve earned a new badge: '.$this->badge->name)
            ->line($this->badge->description)
            ->action('View Your Badges', url('/profile/badges'))
            ->line('Keep up the great work!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_image' => $this->badge->image_path,
            'message' => 'Congratulations! You\'ve earned the ' . $this->badge->name . ' badge.'
        ];
    }
}
