<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\User;
use App\Services\Tenant\InvitationTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendAdminInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * This sends an invite to an admin to use the platform. The admin can choose to send invite when they add another admin.
     * They can also manually send/resend invites to administrators.
     */
    public function __construct(public User $user)
    {
        $this->user = $user;
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
        $adminName = $this->user->name;
        
        // Generate secure token for pre-filling registration
        $tokenService = new InvitationTokenService();
        $token = $tokenService->generateToken($this->user->name, $this->user->email);
        
        // Create registration URL with token
        $registrationUrl = tenant()->getRoute('tenant.register', ['token' => $token]);
        
        return (new MailMessage)
            ->subject("You're invited to join {$appName} as an Administrator")
            ->greeting("Hello {$adminName}!")
            ->line("You've been invited to join {$appName} as an administrator.")
            ->line("As an administrator, you'll have full access to manage the platform and oversee all coaching operations within your organization.")
            ->line("**Administrative privileges include:**")
            ->line("• Complete user management (coaches, clients, and other administrators)")
            ->line("• Platform configuration and customization")
            ->line("• Subscription and billing management")
            ->line("• Company and organizational structure management")
            ->line("• Full access to all coaching sessions, notes, and tasks")
            ->line("• System-wide reporting and analytics")
            ->line("• Theme and branding customization")
            ->line("")
            ->line("**Your responsibilities:**")
            ->line("As an administrator, you'll help ensure the smooth operation of the coaching platform and support both coaches and clients in achieving their goals.")
            ->line("")
            ->line("**Getting started:**")
            ->line("Click the button below to create your administrator account and access the full platform.")
            ->action('Join as Administrator', $registrationUrl)
            ->line("Your registration details will be pre-filled for your convenience - you'll just need to set your password and complete your profile.")
            ->line("If you have any questions about your administrative role or need assistance with platform management, please contact our support team.")
            ->salutation("Welcome to the administrative team!\nThe {$appName} Team");
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
