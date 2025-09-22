<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\User;
use App\Services\Tenant\InvitationTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCoachInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     * This sends an invite to a coach to use the platform. The admin can choose to send invite when they add a coach.
     * They can also manually send/resend invites to coaches.
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
        $coachName = $this->user->name;
        
        // Generate secure token for pre-filling registration
        $tokenService = new InvitationTokenService();
        $token = $tokenService->generateToken($this->user->name, $this->user->email);
        
        // Create registration URL with token
        $registrationUrl = tenant()->getRoute('tenant.register', ['token' => $token]);
        
        return (new MailMessage)
            ->subject("You're invited to join {$appName} as a Coach")
            ->greeting("Hello {$coachName}!")
            ->line("Congratulations! You've been invited to join {$appName} as a coach.")
            ->line("As a coach on our platform, you'll have access to powerful tools designed to enhance your coaching practice and help you better serve your clients.")
            ->line("**What you can do as a coach:**")
            ->line("• Manage your client roster and track their progress")
            ->line("• Schedule and organize coaching sessions")
            ->line("• Create and assign coaching notes and tasks")
            ->line("• Use coaching frameworks and assessment tools")
            ->line("• Track client objectives and development goals")
            ->line("• Integrate with your calendar (Google Calendar, Outlook)")
            ->line("• Generate contracts and manage client agreements")
            ->line("")
            ->line("**Getting started:**")
            ->line("Click the button below to create your coach account and start exploring the platform.")
            ->action('Join as Coach', $registrationUrl)
            ->line("Your registration details will be pre-filled for your convenience - you'll just need to set your password and complete your profile.")
            ->line("If you have any questions about the platform or need assistance getting started, please don't hesitate to reach out to our support team.")
            ->salutation("Welcome to the team!\nThe {$appName} Team");
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
