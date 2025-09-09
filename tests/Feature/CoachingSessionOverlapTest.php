<?php

use App\Models\Tenant\User;
use App\Models\Tenant\CoachingSession;
use Carbon\Carbon;

beforeEach(function () {
    // Create a coach and client for testing
    $this->coach = User::factory()->create();
    $this->coach->assignRole('coach');
    
    $this->client = User::factory()->create();
    $this->client->assignRole('client');
    $this->client->update(['assigned_coach_id' => $this->coach->id]);
    
    $this->actingAs($this->coach);
});

it('prevents overlapping sessions at exact same time', function () {
    // Create an existing session from 2:00 PM to 3:00 PM
    CoachingSession::factory()->create([
        'coach_id' => $this->coach->id,
        'client_id' => $this->client->id,
        'scheduled_at' => Carbon::parse('2024-01-15 14:00:00'),
        'start_at' => Carbon::parse('2024-01-15 14:00:00'),
        'end_at' => Carbon::parse('2024-01-15 15:00:00'),
        'duration' => 60,
    ]);

    // Try to create another session at the exact same time
    $response = $this->post(route('tenant.coach.coaching-sessions.store'), [
        'client_id' => $this->client->id,
        'scheduled_date' => '2024-01-15',
        'scheduled_time' => '14:00',
        'duration' => 60,
        'session_type' => 'online',
        'timezone' => 'UTC',
    ]);

    $response->assertSessionHasErrors(['scheduled_time']);
    expect(session('errors')->first('scheduled_time'))->toContain('conflicts with existing session');
});

it('prevents overlapping sessions with partial overlap', function () {
    // Create an existing session from 2:00 PM to 3:00 PM
    CoachingSession::factory()->create([
        'coach_id' => $this->coach->id,
        'client_id' => $this->client->id,
        'scheduled_at' => Carbon::parse('2024-01-15 14:00:00'),
        'start_at' => Carbon::parse('2024-01-15 14:00:00'),
        'end_at' => Carbon::parse('2024-01-15 15:00:00'),
        'duration' => 60,
    ]);

    // Try to create another session from 2:30 PM to 3:30 PM (overlaps by 30 minutes)
    $response = $this->post(route('tenant.coach.coaching-sessions.store'), [
        'client_id' => $this->client->id,
        'scheduled_date' => '2024-01-15',
        'scheduled_time' => '14:30',
        'duration' => 60,
        'session_type' => 'online',
        'timezone' => 'UTC',
    ]);

    $response->assertSessionHasErrors(['scheduled_time']);
});

it('allows back to back sessions', function () {
    // Create an existing session from 2:00 PM to 3:00 PM
    CoachingSession::factory()->create([
        'coach_id' => $this->coach->id,
        'client_id' => $this->client->id,
        'scheduled_at' => Carbon::parse('2024-01-15 14:00:00'),
        'start_at' => Carbon::parse('2024-01-15 14:00:00'),
        'end_at' => Carbon::parse('2024-01-15 15:00:00'),
        'duration' => 60,
    ]);

    // Try to create another session from 3:00 PM to 4:00 PM (back-to-back)
    $response = $this->post(route('tenant.coach.coaching-sessions.store'), [
        'client_id' => $this->client->id,
        'scheduled_date' => '2024-01-15',
        'scheduled_time' => '15:00',
        'duration' => 60,
        'session_type' => 'online',
        'timezone' => 'UTC',
    ]);

    $response->assertSessionDoesntHaveErrors(['scheduled_time']);
});

it('allows different coaches to have sessions at same time', function () {
    // Create another coach
    $otherCoach = User::factory()->create();
    $otherCoach->assignRole('coach');
    
    $otherClient = User::factory()->create();
    $otherClient->assignRole('client');
    $otherClient->update(['assigned_coach_id' => $otherCoach->id]);

    // Create a session for the first coach
    CoachingSession::factory()->create([
        'coach_id' => $this->coach->id,
        'client_id' => $this->client->id,
        'scheduled_at' => Carbon::parse('2024-01-15 14:00:00'),
        'start_at' => Carbon::parse('2024-01-15 14:00:00'),
        'end_at' => Carbon::parse('2024-01-15 15:00:00'),
        'duration' => 60,
    ]);

    // Act as the other coach and create a session at the same time
    $this->actingAs($otherCoach);
    
    $response = $this->post(route('tenant.coach.coaching-sessions.store'), [
        'client_id' => $otherClient->id,
        'scheduled_date' => '2024-01-15',
        'scheduled_time' => '14:00',
        'duration' => 60,
        'session_type' => 'online',
        'timezone' => 'UTC',
    ]);

    $response->assertSessionDoesntHaveErrors(['scheduled_time']);
});

it('excludes current session when updating', function () {
    // Create an existing session
    $session = CoachingSession::factory()->create([
        'coach_id' => $this->coach->id,
        'client_id' => $this->client->id,
        'scheduled_at' => Carbon::parse('2024-01-15 14:00:00'),
        'start_at' => Carbon::parse('2024-01-15 14:00:00'),
        'end_at' => Carbon::parse('2024-01-15 15:00:00'),
        'duration' => 60,
    ]);

    // Try to update the session to the same time (should be allowed)
    $response = $this->put(route('tenant.coach.coaching-sessions.update', $session), [
        'scheduled_date' => '2024-01-15',
        'scheduled_time' => '14:00',
        'duration' => 60,
        'session_type' => 'online',
        'timezone' => 'UTC',
    ]);

    $response->assertSessionDoesntHaveErrors(['scheduled_time']);
});
