<?php

namespace App\Notifications\Central;

use App\Models\Central\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class RegistrationConfirm extends Notification
{
    use Queueable;

    private string $confirmationUrl;
    /**
     * Create a new notification instance.
     */
    public function __construct(public Registration $registration)
    {
        $this->registration = $registration;
        $this->confirmationUrl = route('central.registration.confirm.show', ['token' => $this->registration->confirmation_token]);
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
        $appName = config('app.name');
        $companyName = $this->registration->company_name;

        return (new MailMessage)
            ->subject("Confirm your {$appName} registration")
            ->greeting("Hello {$this->registration->name}!")
            ->line("Thank you for registering {$companyName} with {$appName}.")
            ->line('To complete your registration and create your coaching platform, please click the button below:')
            ->action('Complete Registration', $this->confirmationUrl)
            ->line('You will be able to choose your subdomain and set your password on the next page.')
            ->line('This confirmation link will expire in 7 days.')
            ->line('If you did not create this account, no further action is required.')
            ->salutation("Best regards,\nThe {$appName} Team");
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
