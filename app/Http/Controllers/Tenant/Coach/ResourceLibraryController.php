<?php

namespace App\Http\Controllers\Tenant\Coach;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ResourceLibraryItem;
use App\Enums\Tenant\ResourceLibraryItemType;
use Inertia\Inertia;

class ResourceLibraryController extends Controller
{
    public function all()
    {
        return $this->renderResources();
    }

    public function books()
    {
        return $this->renderResources(ResourceLibraryItemType::BOOK);
    }

    public function podcasts()
    {
        return $this->renderResources(ResourceLibraryItemType::PODCAST);
    }

    public function videos()
    {
        return $this->renderResources(ResourceLibraryItemType::VIDEO);
    }

    public function courses()
    {
        return $this->renderResources(ResourceLibraryItemType::COURSE);
    }

    public function articles()
    {
        return $this->renderResources(ResourceLibraryItemType::ARTICLE);
    }

    private function renderResources(?ResourceLibraryItemType $type = null)
    {
        $query = ResourceLibraryItem::query();
        
        if ($type) {
            $query->where('type', $type->value);
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
            'typeFilter' => $type?->value,
        ]);
    }
}
