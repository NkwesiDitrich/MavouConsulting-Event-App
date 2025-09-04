<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png'),
                'bio' => $user->bio,
                'linkedin_url' => $user->linkedin_url,
                'twitter_url' => $user->twitter_url,
                'interests' => $user->interests ?? [],
                'events_attended' => $user->events_attended ?? 0,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:1000',
            'linkedin_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:50',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::delete('public/profile_pictures/' . $user->profile_picture);
            }
            
            $image = $request->file('profile_picture');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/profile_pictures', $imageName);
            $validatedData['profile_picture'] = $imageName;
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png'),
                'bio' => $user->bio,
                'linkedin_url' => $user->linkedin_url,
                'twitter_url' => $user->twitter_url,
                'interests' => $user->interests ?? [],
                'events_attended' => $user->events_attended ?? 0
            ]
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Get user's events
     */
    public function myEvents()
    {
        $user = Auth::user();
        
        // Get events organized by user
        $organizedEvents = $user->organizedEvents()
            ->with(['category'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Get events attended by user
        $attendedEventIds = \App\Models\Attendee::where('user_id', $user->id)->pluck('event_id');
        $attendedEvents = Event::whereIn('id', $attendedEventIds)
            ->with(['organizer', 'category'])
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'organized_events' => $organizedEvents,
            'attended_events' => $attendedEvents
        ]);
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password is incorrect'
            ], 400);
        }

        // Delete profile picture if exists
        if ($user->profile_picture) {
            Storage::delete('public/profile_pictures/' . $user->profile_picture);
        }

        // Logout and delete user
        Auth::logout();
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
}