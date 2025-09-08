<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\CoachingContractSignature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendContractSigningRequestClient extends Notification  implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CoachingContract $contract,public CoachingContractSignature $signature)
    {
        //This is what we send to clients when a coach created a contract and hit the "send" button on it.
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
        $coachName = $this->contract->coach->name;
        $signingUrl = tenant()->getRoute('tenant.client.contracts.sign', ['token' => $this->signature->token]);

        return (new MailMessage)
            ->subject('Contract Ready for Your Signature')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your coach, {$coachName}, has prepared a coaching contract for your review and signature.")
            ->line('Please take a moment to review the terms and conditions, then provide your digital signature to proceed.')
            ->action('Review and Sign Contract', $signingUrl)
            ->line('This link is secure and unique to you. Please do not share it with others.')
            ->line('If you have any questions about the contract terms, please contact your coach directly.')
            ->line('Thank you for choosing our coaching services!');
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
