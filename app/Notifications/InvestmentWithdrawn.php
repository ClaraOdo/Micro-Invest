<?php
namespace App\Notifications;

use App\Models\Investment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvestmentWithdrawn extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Investment $investment, protected float $withdrawalAmount)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Investment Withdrawal')
            ->greeting('Hello, ' . explode(' ', $notifiable->name)[1] . '! 👋')
            ->line('You have succesffully withdrawn ' . number_format($this->withdrawalAmount) . ' from your existing Investment for reference number: ' . $this->investment->reference_id)
            ->salutation('Best Regards, Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
