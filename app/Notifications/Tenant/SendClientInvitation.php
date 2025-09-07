<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\User;
use App\Services\Tenant\InvitationTokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendClientInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    private User $coach;
    
    /**
     * Create a new notification instance.
     * This sends an invite to a client to use the platform. The coach can choose to send invite when they add a client. 
     * They can also manually send/resend invites to clients.
     */
    public function __construct(public User $user)
    {
        $this->user = $user;
        $this->coach = $user->assignedCoach;
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
        $coachName = $this->coach->name;
        $clientName = $this->user->name;
        
        // Generate secure token for pre-filling registration
        $tokenService = new InvitationTokenService();
        $token = $tokenService->generateToken($this->user->name, $this->user->email);
        
        // Create registration URL with token
        $registrationUrl = url("/register?token={$token}");
        
        return (new MailMessage)
            ->subject("You're invited to join {$appName}")
            ->greeting("Hello {$clientName}!")
            ->line("Great news! {$coachName} has invited you to join {$appName}.")
            ->line("As your coach, {$coachName} is already using {$appName} to manage your coaching journey, track your progress, and schedule sessions.")
            ->line("**What you can do on the platform:**")
            ->line("• View your coaching sessions and progress")
            ->line("• Access your personalized goals and objectives") 
            ->line("• Communicate with your coach")
            ->line("• Track your development journey")
            ->line("")
            ->line("**Getting started is optional** - your coach can continue working with you whether you join the platform or not. However, if you'd like to take advantage of these features, simply click the button below to create your account.")
            ->action('Join the Platform', $registrationUrl)
            ->line("Your registration details will be pre-filled for your convenience - you'll just need to set your password.")
            ->line("If you have any questions, feel free to reach out to {$coachName} directly.")
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
