<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Enums\Tenant\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Tenant\CoachingFramework;
use App\Models\Tenant\CoachingNote;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\CoachingTask;
use App\Models\Tenant\Company;
use App\Models\Tenant\ProfessionalDevelopment;
use App\Models\Tenant\Supervision;
use App\Models\Tenant\User;
use App\Models\Tenant\UserProfessionalCredential;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Maximum results per category
     */
    private const RESULTS_LIMIT = 5;

    /**
     * Navigation items for search
     */
    private const NAVIGATION_ITEMS = [
        ['title' => 'Dashboard', 'url' => 'tenant.dashboard'],
        ['title' => 'Clients', 'url' => 'tenant.coach.clients.index'],
        ['title' => 'Archived Clients', 'url' => 'tenant.coach.clients.archived'],
        ['title' => 'Companies', 'url' => 'tenant.coach.companies.index'],
        ['title' => 'Sessions', 'url' => 'tenant.coach.coaching-sessions.index'],
        ['title' => 'Past Sessions', 'url' => 'tenant.coach.coaching-sessions.past'],
        ['title' => 'Session Calendar', 'url' => 'tenant.coach.coaching-sessions.calendar'],
        ['title' => 'Notes', 'url' => 'tenant.coach.coaching-notes.index'],
        ['title' => 'Tools & Models', 'url' => 'tenant.coach.coaching-frameworks.models'],
        ['title' => 'Coaching Tools', 'url' => 'tenant.coach.coaching-frameworks.tools'],
        ['title' => 'Profiling Tools', 'url' => 'tenant.coach.coaching-frameworks.profiling'],
        ['title' => 'Custom Frameworks', 'url' => 'tenant.coach.custom-frameworks.index'],
        ['title' => 'Growth Tracker', 'url' => 'tenant.coach.coaching-log.index'],
        ['title' => 'Training Log', 'url' => 'tenant.coach.training-log.index'],
        ['title' => 'Resource Library', 'url' => 'tenant.coach.resource-library.all'],
        ['title' => 'Books', 'url' => 'tenant.coach.resource-library.books'],
        ['title' => 'Podcasts', 'url' => 'tenant.coach.resource-library.podcasts'],
        ['title' => 'Videos', 'url' => 'tenant.coach.resource-library.videos'],
        ['title' => 'Courses', 'url' => 'tenant.coach.resource-library.courses'],
        ['title' => 'Articles', 'url' => 'tenant.coach.resource-library.articles'],
        ['title' => 'Tasks', 'url' => 'tenant.coach.coaching-tasks.index'],
        ['title' => 'Overdue Tasks', 'url' => 'tenant.coach.coaching-tasks.overdue'],
        ['title' => 'Completed Tasks', 'url' => 'tenant.coach.coaching-tasks.completed'],
        ['title' => 'Professional Credentials', 'url' => 'tenant.coach.professional-credentials.index'],
        ['title' => 'Training & Development', 'url' => 'tenant.coach.growth-tracker.training-development.index'],
        ['title' => 'Supervision Log', 'url' => 'tenant.coach.growth-tracker.supervision-log.index'],
        // Settings & Account
        ['title' => 'Settings', 'url' => 'tenant.settings.profile.edit'],
        ['title' => 'Profile Settings', 'url' => 'tenant.settings.profile.edit'],
        ['title' => 'Password', 'url' => 'tenant.settings.password.edit'],
        ['title' => 'Avatar', 'url' => 'tenant.settings.avatar.index'],
        ['title' => 'Appearance', 'url' => 'tenant.settings.appearance'],
        ['title' => 'Calendar Integrations', 'url' => 'tenant.settings.calendar-integrations'],
        ['title' => 'Two-Factor Authentication', 'url' => 'tenant.settings.two-factor.show'],
        // Admin Navigation (only show if user has admin role)
        ['title' => 'Manage Coaches', 'url' => 'tenant.admin.coaches.index', 'admin_only' => true],
        ['title' => 'Manage Administrators', 'url' => 'tenant.admin.administrators.index', 'admin_only' => true],
        ['title' => 'Platform Branding', 'url' => 'tenant.admin.platform-settings.theme', 'admin_only' => true],
        ['title' => 'Subscription', 'url' => 'tenant.admin.subscriptions.manage', 'admin_only' => true],
    ];

    /**
     * Search across all accessible resources
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255',
        ]);

        $query = $request->input('query');
        $coachId = auth()->id();

        $results = [
            'navigation' => $this->searchNavigation($query),
            'clients' => $this->searchClients($query, $coachId),
            'companies' => $this->searchCompanies($query),
            'sessions' => $this->searchSessions($query, $coachId),
            'notes' => $this->searchNotes($query, $coachId),
            'tasks' => $this->searchTasks($query, $coachId),
            'frameworks' => $this->searchFrameworks($query),
            'credentials' => $this->searchCredentials($query, $coachId),
            'professional_development' => $this->searchProfessionalDevelopment($query, $coachId),
            'supervisions' => $this->searchSupervisions($query, $coachId),
        ];

        return response()->json(['results' => $results]);
    }

    /**
     * Search navigation items
     */
    private function searchNavigation(string $query): array
    {
        $results = [];
        $lowerQuery = strtolower($query);
        $isAdmin = auth()->user()->hasRole('admin');

        foreach (self::NAVIGATION_ITEMS as $item) {
            // Skip admin-only items if user is not admin
            if (isset($item['admin_only']) && $item['admin_only'] && !$isAdmin) {
                continue;
            }

            if (str_contains(strtolower($item['title']), $lowerQuery)) {
                $results[] = [
                    'type' => 'navigation',
                    'title' => $item['title'],
                    'url' => route($item['url']),
                ];
            }
        }

        return array_slice($results, 0, self::RESULTS_LIMIT);
    }

    /**
     * Search clients
     */
    private function searchClients(string $query, int $coachId): array
    {
        $clients = User::role(UserRole::CLIENT)
            ->where('archived', false)
            ->where(function ($q) use ($coachId) {
                // If user is coach (not admin), only show their assigned clients
                if (auth()->user()->hasRole('coach') && !auth()->user()->hasRole('admin')) {
                    $q->where('assigned_coach_id', $coachId);
                }
                // Admins see all clients
            })
            ->where('name', 'like', "%{$query}%")
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'name', 'email']);

        return $clients->map(function ($client) {
            return [
                'type' => 'client',
                'title' => $client->name,
                'subtitle' => $client->email,
                'url' => route('tenant.coach.clients.show', $client->id),
            ];
        })->toArray();
    }

    /**
     * Search companies
     */
    private function searchCompanies(string $query): array
    {
        $companies = Company::where('name', 'like', "%{$query}%")
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'name', 'contact_person_name']);

        return $companies->map(function ($company) {
            return [
                'type' => 'company',
                'title' => $company->name,
                'subtitle' => $company->contact_person_name ? "Contact: {$company->contact_person_name}" : null,
                'url' => route('tenant.coach.companies.show', $company->id),
            ];
        })->toArray();
    }

    /**
     * Search coaching sessions
     */
    private function searchSessions(string $query, int $coachId): array
    {
        $sessions = CoachingSession::where('coach_id', $coachId)
            ->with('client:id,name')
            ->whereHas('client', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('scheduled_at', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'client_id', 'scheduled_at', 'session_type']);

        return $sessions->map(function ($session) {
            return [
                'type' => 'session',
                'title' => "Session with {$session->client->name}",
                'subtitle' => $session->scheduled_at->format('M d, Y g:i A') . ' • ' . ucfirst($session->session_type),
                'url' => route('tenant.coach.coaching-sessions.show', $session->id),
            ];
        })->toArray();
    }

    /**
     * Search coaching notes
     */
    private function searchNotes(string $query, int $coachId): array
    {
        $notes = CoachingNote::where('coach_id', $coachId)
            ->where('title', 'like', "%{$query}%")
            ->with('client:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'client_id', 'title', 'created_at']);

        return $notes->map(function ($note) {
            return [
                'type' => 'note',
                'title' => $note->title,
                'subtitle' => "{$note->client->name} • {$note->created_at->format('M d, Y')}",
                'url' => route('tenant.coach.coaching-notes.show', $note->id),
            ];
        })->toArray();
    }

    /**
     * Search coaching tasks
     */
    private function searchTasks(string $query, int $coachId): array
    {
        $tasks = CoachingTask::where('coach_id', $coachId)
            ->where('title', 'like', "%{$query}%")
            ->with('client:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'client_id', 'title', 'status', 'deadline']);

        return $tasks->map(function ($task) {
            $subtitle = $task->client->name;
            if ($task->deadline) {
                $subtitle .= " • Due: {$task->deadline->format('M d, Y')}";
            }
            $subtitle .= " • " . ucfirst($task->status);

            return [
                'type' => 'task',
                'title' => $task->title,
                'subtitle' => $subtitle,
                'url' => route('tenant.coach.coaching-tasks.show', $task->id),
            ];
        })->toArray();
    }

    /**
     * Search coaching frameworks (Tools & Models)
     */
    private function searchFrameworks(string $query): array
    {
        $frameworks = CoachingFramework::active()
            ->where('name', 'like', "%{$query}%")
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'name', 'category', 'subcategory']);

        return $frameworks->map(function ($framework) {
            return [
                'type' => 'framework',
                'title' => $framework->name,
                'subtitle' => ucfirst($framework->category) . ($framework->subcategory ? " • {$framework->subcategory}" : ''),
                'url' => route('tenant.coach.coaching-frameworks.show', $framework->id),
            ];
        })->toArray();
    }

    /**
     * Search professional credentials
     */
    private function searchCredentials(string $query, int $coachId): array
    {
        $credentials = UserProfessionalCredential::where('user_id', $coachId)
            ->where(function ($q) use ($query) {
                $q->where('credential_name', 'like', "%{$query}%")
                    ->orWhereHas('provider', function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%");
                    });
            })
            ->with('provider:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'credential_name', 'professional_credential_provider_id', 'expiry_date']);

        return $credentials->map(function ($credential) {
            $subtitle = $credential->provider?->name ?? 'Unknown Provider';
            if ($credential->expiry_date) {
                $subtitle .= " • Expires: {$credential->expiry_date->format('M d, Y')}";
            }

            return [
                'type' => 'credential',
                'title' => $credential->credential_name,
                'subtitle' => $subtitle,
                'url' => route('tenant.coach.professional-credentials.index') . '#credential-' . $credential->id,
            ];
        })->toArray();
    }

    /**
     * Search professional development (Training & Development)
     */
    private function searchProfessionalDevelopment(string $query, int $coachId): array
    {
        $developments = ProfessionalDevelopment::where('user_id', $coachId)
            ->where(function ($q) use ($query) {
                $q->where('course_title', 'like', "%{$query}%")
                    ->orWhere('training_provider', 'like', "%{$query}%");
            })
            ->orderBy('date_from', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'course_title', 'training_provider', 'date_from']);

        return $developments->map(function ($development) {
            return [
                'type' => 'professional_development',
                'title' => $development->course_title,
                'subtitle' => "{$development->training_provider} • {$development->date_from->format('M d, Y')}",
                'url' => route('tenant.coach.growth-tracker.training-development.show', $development->id),
            ];
        })->toArray();
    }

    /**
     * Search supervisions
     */
    private function searchSupervisions(string $query, int $coachId): array
    {
        $supervisions = Supervision::where('user_id', $coachId)
            ->where(function ($q) use ($query) {
                $q->where('supervisor_name', 'like', "%{$query}%")
                    ->orWhere('themes_discussed', 'like', "%{$query}%");
            })
            ->orderBy('supervision_date', 'desc')
            ->limit(self::RESULTS_LIMIT)
            ->get(['id', 'supervisor_name', 'supervision_date', 'supervision_type']);

        return $supervisions->map(function ($supervision) {
            $subtitle = $supervision->supervision_date->format('M d, Y');
            if ($supervision->supervision_type) {
                $subtitle .= " • " . ucfirst($supervision->supervision_type);
            }

            return [
                'type' => 'supervision',
                'title' => "Supervision with {$supervision->supervisor_name}",
                'subtitle' => $subtitle,
                'url' => route('tenant.coach.growth-tracker.supervision-log.show', $supervision->id),
            ];
        })->toArray();
    }
}

