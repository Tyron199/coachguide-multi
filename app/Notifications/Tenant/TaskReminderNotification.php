<?php

namespace App\Notifications\Tenant;

use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\CoachingTaskReminder;
use App\Notifications\Concerns\HasTenantBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable, HasTenantBranding;

    /**
     * Create a new notification instance.
     * This sends a reminder to a client about an upcoming task deadline.
     */
    public function __construct(
        public CoachingTask $task,
        public CoachingTaskReminder $reminder
    ) {
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
        $companyName = $this->getCompanyName();
        $clientName = $this->task->client->name;
        $coachName = $this->task->coach->name;
        $taskTitle = $this->task->title;
        $deadline = $this->task->deadline;
        
        // Format deadline
        $deadlineFormatted = $deadline->format('F j, Y \a\t g:i A');
        
        // Create task view URL
        $taskUrl = tenant()->getRoute('tenant.coach.coaching-tasks.show', ['task' => $this->task->id]);
        
        return (new MailMessage)
            ->subject("Reminder: Task Due {$this->reminder->label}")
            ->greeting("Hello {$clientName}!")
            ->line("This is a friendly reminder about an upcoming task deadline.")
            ->line("**Task:** {$taskTitle}")
            ->line("**Due:** {$deadlineFormatted}")
            ->line("**Reminder:** {$this->reminder->label}")
            ->line("")
            ->line("**Task Description:**")
            ->line($this->task->description)
            ->line("")
            ->action('View Task Details', $taskUrl)
            ->line("If you've already completed this task, please mark it as complete or submit your evidence on the platform.")
            ->line("If you have any questions or need assistance, feel free to reach out to {$coachName}.")
            ->salutation("Best regards,\nThe {$companyName} Team");
    }

    /**
     * Get the array representation of the notification.
     * Prepared for future in-app notification support.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'deadline' => $this->task->deadline,
            'reminder_label' => $this->reminder->label,
            'coach_name' => $this->task->coach->name,
        ];
    }
}

