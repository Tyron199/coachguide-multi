<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\CoachingContractSignature;
use App\Notifications\Concerns\HasTenantBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendContractSigningRequestCoach extends Notification implements ShouldQueue
{
    use Queueable, HasTenantBranding;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CoachingContract $contract, public CoachingContractSignature $signature)
    {
        // This is what we send to coaches when a client has signed and the coach needs to countersign.
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
        $clientName = $this->contract->client->name;
        $signingUrl = tenant()->getRoute('tenant.coach.contracts.sign', ['token' => $this->signature->token]);

        return (new MailMessage)
            ->subject('Client Signed - Contract Ready for Your Countersignature')
            ->greeting("Hello {$notifiable->name},")
            ->line("Great news! Your client, {$clientName}, has signed the coaching contract.")
            ->line('The contract is now ready for your countersignature to make it fully executed.')
            ->action('Review and Countersign Contract', $signingUrl)
            ->line('This link is secure and unique to you. Please do not share it with others.')
            ->line('Once you countersign, both you and your client will receive the final executed contract.')
            ->line('Thank you for using our coaching platform!');
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
