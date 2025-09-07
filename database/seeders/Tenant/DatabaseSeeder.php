<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use App\Models\Tenant\UserProfile;
use App\Models\Tenant\CoachingNote;
use App\Models\Tenant\CoachingSession;
use App\Enums\Tenant\UserRegistrationStatus;
use App\Enums\Tenant\CoachingSessionType;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\Tenant\UserRole;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       $owner=  User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123Password!123'),
            'status' => UserRegistrationStatus::ACCEPTED,
        ]);

        // Main user is both admin (system management) AND coach (takes clients)
        $owner->assignRole([UserRole::ADMIN, UserRole::COACH]);


        //Coach
        $coach=  User::factory()->create([
            'name' => 'Coach',
            'email' => 'coach@example.com',
            'password' => Hash::make('123Password!123'),
            'status' => UserRegistrationStatus::ACCEPTED,
        ]);

        $coach->assignRole([UserRole::COACH]);

        // Create some companies
        $companies = Company::factory(5)->create();

        // Get all coaches for distribution
        $allCoaches = [$coach, $owner]; // Both coach and owner can take clients
        
        // Create clients for each company with different coaching focuses
        foreach ($companies as $company) {
            // Create 2-4 clients per company
            $clientCount = fake()->numberBetween(20, 40);
            
            for ($i = 0; $i < $clientCount; $i++) {
                // Distribute clients more evenly among coaches
                $assignedCoach = $allCoaches[$i % count($allCoaches)];
                
                $client = User::factory()
                    ->client()
                    ->unverified()
                    ->create([
                        'company_id' => $company->id,
                        'assigned_coach_id' => $assignedCoach->id,
                    ]);

                $client->assignRole(UserRole::CLIENT);

                // Create different types of client profiles
                $profileType = fake()->randomElement(['weightLoss', 'fitness', 'wellness']);
                
                // Delete the auto-created profile and replace with a detailed one
                $client->profile()->delete();
                
                $profile = UserProfile::factory()->{$profileType}()->make();
                $client->profile()->create($profile->toArray());

                // Create coaching notes for this client
                $this->createNotesForClient($client);
            }
        }

        // Create a few individual clients (not associated with companies)
        for ($i = 0; $i < 10; $i++) {
            // Distribute individual clients evenly among coaches too
            $assignedCoach = $allCoaches[$i % count($allCoaches)];
            
            $client = User::factory()
                ->client()
                ->unverified()
                ->create([
                    'assigned_coach_id' => $assignedCoach->id,
                ]);

            $client->assignRole(UserRole::CLIENT);

            // Create different types of client profiles
            $profileType = fake()->randomElement(['weightLoss', 'fitness', 'wellness']);
            
            // Delete the auto-created profile and replace with a detailed one
            $client->profile()->delete();
            
            $profile = UserProfile::factory()->{$profileType}()->make();
            $client->profile()->create($profile->toArray());

         
            // Create coaching notes for this client
            $this->createNotesForClient($client);
        }

        // Create coaching sessions for the next 7 days (4 sessions per day)
        $this->createCoachingSessions();
    }

    /**
     * Create coaching notes for a client
     */
    private function createNotesForClient(User $client): void
    {
        $coach = User::find($client->assigned_coach_id);
        
        if (!$coach) {
            return;
        }

        // Create 3-8 notes per client
        $noteCount = fake()->numberBetween(3, 8);
        
        for ($i = 0; $i < $noteCount; $i++) {
            // 70% chance of general note, 30% chance of session note
            $isSessionNote = fake()->boolean(30);
            
            if ($isSessionNote) {
                // Create a session note (we'll create without actual sessions for now)
                CoachingNote::factory()
                    ->sessionNote()
                    ->create([
                        'coach_id' => $coach->id,
                        'client_id' => $client->id,
                        'session_id' => null, // We don't have sessions seeded yet
                        'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                        'updated_at' => function (array $attributes) {
                            // Sometimes notes are updated after creation
                            return fake()->boolean(30) 
                                ? fake()->dateTimeBetween($attributes['created_at'], 'now')
                                : $attributes['created_at'];
                        },
                    ]);
            } else {
                // Create a general client note
                CoachingNote::factory()
                    ->generalNote()
                    ->create([
                        'coach_id' => $coach->id,
                        'client_id' => $client->id,
                        'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                        'updated_at' => function (array $attributes) {
                            // Sometimes notes are updated after creation
                            return fake()->boolean(30) 
                                ? fake()->dateTimeBetween($attributes['created_at'], 'now')
                                : $attributes['created_at'];
                        },
                    ]);
            }
        }
    }

    /**
     * Create coaching sessions for 7 days forward and 7 days backward
     */
    private function createCoachingSessions(): void
    {
        // Get all coaches (users with coach role)
        $coaches = User::role(UserRole::COACH)->get();
        
        if ($coaches->isEmpty()) {
            return;
        }

        // Create sessions for each coach with their assigned clients only
        foreach ($coaches as $coach) {
            $assignedClients = $coach->assignedClients()->get();
            
            if ($assignedClients->isEmpty()) {
                continue; // Skip coaches with no assigned clients
            }

            // Create sessions for the past 7 days
            $this->createSessionsForDateRange($coach, $assignedClients, -7, -1, true);
            
            // Create sessions for the next 7 days
            $this->createSessionsForDateRange($coach, $assignedClients, 1, 7, false);

            //Create a session for the current day/hour
            $this->createSingleSessionForCoach($coach, $assignedClients, now(), false);
        }
    }

    /**
     * Create coaching sessions for a specific date range
     */
    private function createSessionsForDateRange($coach, $assignedClients, int $startDay, int $endDay, bool $isPastSession): void
    {
        for ($day = $startDay; $day <= $endDay; $day++) {
            $date = now()->addDays($day);
            
            // Create 1-2 sessions per day for this coach (reduced to be more realistic)
            $sessionsPerDay = fake()->numberBetween(1, 2);
            for ($session = 0; $session < $sessionsPerDay; $session++) {
                $this->createSingleSessionForCoach($coach, $assignedClients, $date, $isPastSession);
            }
        }
    }

    /**
     * Create a single coaching session for a specific coach
     */
    private function createSingleSessionForCoach($coach, $assignedClients, $date, bool $isPastSession): void
    {
        // Random time between 8 AM and 6 PM
        $hour = fake()->numberBetween(8, 17);
        $minute = fake()->randomElement([0, 15, 30, 45]);
        
        $scheduledAt = $date->copy()->setTime($hour, $minute);
        
        // Pick a random client from this coach's assigned clients
        $client = $assignedClients->random();
        
        // Random session duration (30, 45, 60, or 90 minutes)
        $duration = fake()->randomElement([30, 45, 60, 90]);
        
        // Random session type
        $sessionType = fake()->randomElement(CoachingSessionType::cases());
        
        if ($isPastSession) {
            // Past sessions: started a few minutes after scheduled time
            $startAt = $scheduledAt->copy()->addMinutes(fake()->numberBetween(0, 10));
            
            // Session ended based on actual duration (might be different from planned)
            $actualDuration = fake()->numberBetween($duration - 15, $duration + 15);
            $endAt = $startAt->copy()->addMinutes($actualDuration);
            
            $clientAttended = true;
        } else {
            // Future sessions: use scheduled time as start time and planned duration
            $startAt = $scheduledAt->copy();
            $endAt = $scheduledAt->copy()->addMinutes($duration);
            
            // Future sessions default to  attended
            $clientAttended = true;
        }
        
        CoachingSession::create([
            'client_id' => $client->id,
            'coach_id' => $coach->id,
            'scheduled_at' => $scheduledAt,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'session_type' => $sessionType,
            'duration' => $duration,
            'client_attended' => $clientAttended,
        ]);
    }
}
