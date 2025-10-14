<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ResourceLibraryItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResourceLibraryController extends Controller
{
    public function index(Request $request)
    {
        $typeFilter = $request->query('type');
        
        // Query resources from database
        $query = ResourceLibraryItem::query();
        
        // Filter by type if provided
        if ($typeFilter) {
            $query->where('type', (int) $typeFilter);
        }
        
        $resources = $query->get()->map(function ($resource) {
            return [
                'id' => $resource->id,
                'title' => $resource->title,
                'description' => $resource->description,
                'link' => $resource->link,
                'type' => $resource->type->value,
                'type_label' => $resource->type->label(),
                'type_icon' => $resource->type->icon(),
                'image_path' => $resource->image_path,
            ];
        });

        return Inertia::render('Tenant/coach/resource-library/ListResources', [
            'resources' => $resources,
            'typeFilter' => $typeFilter,
        ]);
    }
}
