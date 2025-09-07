<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\CoachingContract;
use App\Models\Tenant\CoachingContractSignature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractFullyExecuted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CoachingContract $contract,
        public CoachingContractSignature $signature
    ) {
        // This is sent to both coaches and clients when the contract
        // has been fully executed by both parties
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
        $clientName = $this->contract->client->name;
        
        // Determine the appropriate URL based on the recipient's role
        if ($notifiable->hasRole('coach')) {
            $contractUrl = route('tenant.coach.contracts.sign', ['token' => $this->signature->token]);
            $otherParty = $clientName;
            $relationship = 'client';
        } else {
            $contractUrl = route('tenant.contracts.sign', ['token' => $this->signature->token]);
            $otherParty = $coachName;
            $relationship = 'coach';
        }

        return (new MailMessage)
            ->subject('Contract Fully Executed - Download Available')
            ->greeting("Hello {$notifiable->name},")
            ->line("Great news! The coaching contract between you and {$otherParty} has been fully executed.")
            ->line('Both parties have now signed the contract, making it legally binding.')
            ->line('You can now download your signed copy of the contract for your records.')
            ->action('View and Download Contract', $contractUrl)
            ->line('We recommend keeping a copy of this signed contract in a safe place.')
            ->line('Thank you for completing the contract signing process!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'coach_name' => $this->contract->coach->name,
            'client_name' => $this->contract->client->name,
            'message' => 'Contract has been fully executed by both parties',
        ];
    }
}
