<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\User;
use App\Notifications\Concerns\HasTenantBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachRoleAdded extends Notification implements ShouldQueue
{
    use Queueable, HasTenantBranding;

    /**
     * Create a new notification instance.
     * This notifies an existing user that they've been given coach privileges.
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
        $companyName = $this->getCompanyName();
        $userName = $this->user->name;
        
        return (new MailMessage)
            ->subject("You've been granted Coach privileges on {$companyName}")
            ->greeting("Hello {$userName}!")
            ->line("Great news! You've been granted coach privileges on {$companyName}.")
            ->line("In addition to your existing access, you now have coaching capabilities that will allow you to:")
            ->line("**New coach features available to you:**")
            ->line("• Manage your own client roster and track their progress")
            ->line("• Schedule and organize coaching sessions")
            ->line("• Create and assign coaching notes and tasks")
            ->line("• Use coaching frameworks and assessment tools")
            ->line("• Track client objectives and development goals")
            ->line("• Integrate with your calendar for session management")
            ->line("• Generate contracts and manage client agreements")
            ->line("")
            ->line("**Getting started with coaching:**")
            ->line("You can access your new coaching features immediately by logging into your existing account. Look for the coaching sections in your navigation menu.")
            ->line("If you have any questions about your new coaching privileges or need assistance getting started with the coaching tools, please don't hesitate to reach out to our support team.")
            ->salutation("Welcome to the coaching team!\nThe {$companyName} Team");
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
