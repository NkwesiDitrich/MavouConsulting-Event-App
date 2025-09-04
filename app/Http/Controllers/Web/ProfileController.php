<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show user profile
     */
    public function show()
    {
        try {
            $user = Auth::user();
            
            // Add profile picture URL
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');

            // Get user's organized events
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with('category')
                ->orderBy('start_time', 'desc')
                ->get();

            // Get user's attended events
            $attendedEvents = Event::whereHas('attendees', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'user' => $user,
                'organized_events' => $organizedEvents,
                'attended_events' => $attendedEvents
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load profile data'
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
                'linkedin_url' => 'nullable|url|max:255',
                'twitter_url' => 'nullable|url|max:255',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                $validatedData['profile_picture'] = $path;
            }

            // Update user data using fill and save methods
            $user->fill($validatedData);
            $user->save();

            // Add profile picture URL for response
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['profile_picture']),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);

            $user = Auth::user();

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            Log::info('User password updated', [
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update password'
            ], 500);
        }
    }

    /**
     * Get user's events (organized and attended)
     */
    public function myEvents()
    {
        try {
            $user = Auth::user();

            // Get organized events
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with(['category', 'attendees'])
                ->orderBy('start_time', 'desc')
                ->get();

            // Add attendee count to organized events
            $organizedEvents->transform(function ($event) {
                $event->attendee_count = $event->attendees->count();
                $event->image_url = $event->image 
                    ? asset('storage/' . $event->image) 
                    : asset('images/default-event.jpg');
                return $event;
            });

            // Get attended events
            $attendedEvents = Event::whereHas('attendees', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'desc')
            ->get();

            // Add image URL to attended events
            $attendedEvents->transform(function ($event) {
                $event->image_url = $event->image 
                    ? asset('storage/' . $event->image) 
                    : asset('images/default-event.jpg');
                return $event;
            });

            return response()->json([
                'success' => true,
                'organized_events' => $organizedEvents,
                'attended_events' => $attendedEvents
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading user events: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load events'
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
                'password' => 'required',
            ]);

            $user = Auth::user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is incorrect'
                ], 400);
            }

            // Delete profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Delete user's event registrations
            Attendee::where('user_id', $user->id)->delete();

            // Note: We don't delete events organized by the user to maintain data integrity
            // Instead, we could set organizer_id to null or transfer to admin

            Log::info('User account deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Delete the user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account'
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        try {
            $user = Auth::user();

            $stats = [
                'events_organized' => Event::where('organizer_id', $user->id)->count(),
                'events_attended' => Attendee::where('user_id', $user->id)->count(),
                'upcoming_events' => Event::whereHas('attendees', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->where('start_time', '>', now())->count(),
                'interests_count' => count($user->interests ?? [])
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user stats: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }
}
