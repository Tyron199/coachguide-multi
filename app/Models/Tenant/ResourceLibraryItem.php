<?php

namespace App\Models\Tenant;

use App\Enums\Tenant\ResourceLibraryItemType;
use Illuminate\Database\Eloquent\Model;

class ResourceLibraryItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'link',
        'type',
        'image_path',
    ];

    protected $casts = [
        'type' => ResourceLibraryItemType::class,
    ];
}
