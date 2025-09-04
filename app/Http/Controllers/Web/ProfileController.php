<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendee;
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
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get profile picture URL
            $profilePictureUrl = $user->profile_picture ? 
                asset('storage/profile_pictures/' . $user->profile_picture) : 
                'https://ui-avatars.com/api/?name=' . urlencode($user->name) . 
                '&background=' . substr(md5($user->id), 0, 6) . '&color=fff&size=200';
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $profilePictureUrl,
                    'bio' => $user->bio,
                    'linkedin_url' => $user->linkedin_url,
                    'twitter_url' => $user->twitter_url,
                    'interests' => $user->interests ?? [],
                    'events_attended' => $user->events_attended ?? 0,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load profile data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        try {
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

            // Get updated profile picture URL
            $profilePictureUrl = $user->profile_picture ? 
                asset('storage/profile_pictures/' . $user->profile_picture) : 
                'https://ui-avatars.com/api/?name=' . urlencode($user->name) . 
                '&background=' . substr(md5($user->id), 0, 6) . '&color=fff&size=200';

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile_picture' => $profilePictureUrl,
                    'bio' => $user->bio,
                    'linkedin_url' => $user->linkedin_url,
                    'twitter_url' => $user->twitter_url,
                    'interests' => $user->interests ?? [],
                    'events_attended' => $user->events_attended ?? 0
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        try {
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

        } catch (\Exception $e) {
            \Log::error('Password update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's events
     */
    public function myEvents()
    {
        try {
            $user = Auth::user();
            
            // Get events organized by user
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with(['category:id,name'])
                ->orderBy('start_time', 'desc')
                ->get();

            // Get events attended by user
            $attendedEventIds = Attendee::where('user_id', $user->id)->pluck('event_id');
            $attendedEvents = Event::whereIn('id', $attendedEventIds)
                ->with(['organizer:id,name', 'category:id,name'])
                ->orderBy('start_time', 'desc')
                ->get();

            return response()->json([
                'organized_events' => $organizedEvents,
                'attended_events' => $attendedEvents
            ]);

        } catch (\Exception $e) {
            \Log::error('My events error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load user events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        try {
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

        } catch (\Exception $e) {
            \Log::error('Account deletion error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
