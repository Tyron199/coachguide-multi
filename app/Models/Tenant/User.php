<?php

namespace App\Models\Tenant;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Tenant\UserRegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Services\OAuth\OauthProviderType;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The guard name for Spatie Permission.
     */
    //protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'status',
        'avatar',
        'phone',
        'assigned_coach_id',
        'archived',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserRegistrationStatus::class,
            'provider' => OauthProviderType::class,
            'archived' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function calendarIntegrations()
    {
        return $this->hasMany(CalendarIntegration::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function hasMicrosoftCalendar(): bool
    {
        return $this->calendarIntegrations()->where('provider', OauthProviderType::MICROSOFT)->exists();
    }

    public function hasGoogleCalendar(): bool
    {
        return $this->calendarIntegrations()->where('provider', OauthProviderType::GOOGLE)->exists();
    }

    public function hasSocialAccount(OauthProviderType $provider): bool
    {
        return $this->socialAccounts()->where('provider', $provider)->exists();
    }

    public function getSocialAccount(OauthProviderType $provider): ?SocialAccount
    {
        return $this->socialAccounts()->where('provider', $provider)->first();
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the coach assigned to this client
     */
    public function assignedCoach()
    {
        return $this->belongsTo(User::class, 'assigned_coach_id')->withTrashed();
    }

    /**
     * Get all clients assigned to this coach
     */
    public function assignedClients()
    {
        return $this->hasMany(User::class, 'assigned_coach_id');
    }

    /**
     * Get all clients assigned to this coach including soft deleted ones
     */
    public function assignedClientsWithTrashed()
    {
        return $this->hasMany(User::class, 'assigned_coach_id')->withTrashed();
    }

    /**
     * Get coaching sessions where this user is the client
     */
    public function clientSessions()
    {
        return $this->hasMany(CoachingSession::class, 'client_id');
    }

    /**
     * Get coaching sessions where this user is the coach
     */
    public function coachSessions()
    {
        return $this->hasMany(CoachingSession::class, 'coach_id');
    }

    /**
     * Get all coaching sessions for this user (both as client and coach)
     */
    public function allSessions()
    {
        return CoachingSession::where('client_id', $this->id)
            ->orWhere('coach_id', $this->id);
    }

    /**
     * Get all coaching notes where this user is the coach
     */
    public function coachingNotes()
    {
        return $this->hasMany(CoachingNote::class, 'coach_id');
    }

    /**
     * Get all coaching notes about this user (when they are the client)
     */
    public function clientNotes()
    {
        return $this->hasMany(CoachingNote::class, 'client_id');
    }

    /**
     * Get all coaching notes between this coach and a specific client
     * @param int $clientId
     */
    public function notesForClient($clientId)
    {
        return $this->coachingNotes()->where('client_id', $clientId);
    }

    /**
     * Get all coaching tasks where this user is the coach
     */
    public function coachingTasks()
    {
        return $this->hasMany(CoachingTask::class, 'coach_id');
    }

    /**
     * Get all coaching tasks assigned to this user (when they are the client)
     */
    public function assignedCoachingTasks()
    {
        return $this->hasMany(CoachingTask::class, 'client_id');
    }

    /**
     * Get all coaching tasks between this coach and a specific client
     * @param int $clientId
     */
    public function tasksForClient($clientId)
    {
        return $this->coachingTasks()->where('client_id', $clientId);
    }

    //Automatically create a profile for the user
    protected static function boot()
    {
        parent::boot();
        static::created(function ($user) {
            $user->profile()->create();
        });
    }
    /**
     * Normalize email to lowercase and trim whitespace
     */
    protected function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    /**
     * Get the avatar URL
     */
    protected function getAvatarAttribute($value)
    {
        return $value ? tenant_asset($value) : null;
    }


    //Return model of self formatted for inertia middleware
    public function toInertia()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar, // Only adding avatar since it's needed for layout
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getPermissionsViaRoles()->pluck('name'),
        ];
    }
}
