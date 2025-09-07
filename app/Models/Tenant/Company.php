<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
      protected $fillable = [
        'name',
        'address',
        'industry_sector',
        'contact_person_name',
        'contact_person_position',
        'contact_person_email',
        'contact_person_phone',
        'billing_contact_name',
        'billing_contact_email',
        'billing_contact_phone',
        'invoicing_notes'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
