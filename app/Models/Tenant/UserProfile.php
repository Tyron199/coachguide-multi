<?php

namespace App\Models\Tenant;

use App\Enums\Tenant\CommunicationMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'address',
        'birthdate',
        'medical_conditions',
        'emergency_contact_name',
        'emergency_contact_phone',
        'preferred_method_of_communication',
        'goal_summary',
        'objectives',
        'focus_areas',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'preferred_method_of_communication' => CommunicationMethod::class,
            'medical_conditions' => 'array',
            'focus_areas' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
