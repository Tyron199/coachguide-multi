<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AvatarController extends Controller
{
    public function index()
    {
        return Inertia::render('Tenant/settings/Avatar', [
            'user' => auth()->user()->only([
                'id',
                'name', 
                'email',
                'avatar',
                'phone',
                'email_verified_at',
                'created_at',
                'updated_at'
            ])
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|file|image|mimes:jpeg,jpg,png,gif,webp,svg|max:2048' // 2MB max, all common image formats
        ]);

        $user = auth()->user();
        
        // Delete old avatar if it exists
        if ($user->getRawOriginal('avatar')) {
            Storage::disk('public')->delete($user->getRawOriginal('avatar'));
        }

        // Store in the tenant's public disk
        $path = $request->file('avatar')->store('avatars', 'public');

        // Update user avatar
        $user->update([
            'avatar' => $path
        ]);

        return redirect()->back()->with('success', 'Profile photo updated successfully');
    }

    public function destroy()
    {
        $user = auth()->user();
        
        // Delete the avatar file if it exists
        if ($user->getRawOriginal('avatar')) {
            Storage::disk('public')->delete($user->getRawOriginal('avatar'));
        }
        
        // Update user to remove avatar
        $user->update([
            'avatar' => null
        ]);

        return redirect()->back()->with('success', 'Profile photo removed successfully');
    }
}