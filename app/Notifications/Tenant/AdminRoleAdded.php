<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRoleAdded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * This notifies an existing user that they've been given administrator privileges.
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
        $userName = $this->user->name;
        
        return (new MailMessage)
            ->subject("You've been granted Administrator privileges on {$appName}")
            ->greeting("Hello {$userName}!")
            ->line("Congratulations! You've been granted administrator privileges on {$appName}.")
            ->line("In addition to your existing access, you now have administrative capabilities that will allow you to:")
            ->line("**New administrative features available to you:**")
            ->line("• Complete user management (coaches, clients, and other administrators)")
            ->line("• Platform configuration and customization")
            ->line("• Subscription and billing management")
            ->line("• Company and organizational structure management")
            ->line("• Full access to all coaching sessions, notes, and tasks")
            ->line("• System-wide reporting and analytics")
            ->line("• Theme and branding customization")
            ->line("")
            ->line("**Your expanded responsibilities:**")
            ->line("As an administrator, you'll help ensure the smooth operation of the coaching platform and support both coaches and clients in achieving their goals.")
            ->line("")
            ->line("**Getting started with administration:**")
            ->line("You can access your new administrative features immediately by logging into your existing account. Look for the admin sections in your navigation menu.")
            ->line("If you have any questions about your new administrative role or need assistance with platform management, please contact our support team.")
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
