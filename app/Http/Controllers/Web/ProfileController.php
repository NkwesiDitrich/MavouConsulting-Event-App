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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show user profile - REAL-TIME TRACKING
     */
    public function show()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get fresh user data from database
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            
            // Add profile picture URL
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');

            // Get user's organized events - REAL-TIME
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with('category')
                ->orderBy('start_time', 'desc')
                ->get();

            // Add real-time attendee counts to organized events
            $organizedEvents->transform(function ($event) {
                $event->attendee_count = DB::table('attendees')
                    ->where('event_id', $event->id)
                    ->count();
                $event->image_url = $event->image_url ?: asset('images/default-event.jpg');
                return $event;
            });

            // Get user's attended events - REAL-TIME
            $attendedEvents = Event::whereExists(function($query) use ($user) {
                $query->select(DB::raw(1))
                      ->from('attendees')
                      ->whereRaw('attendees.event_id = events.id')
                      ->where('attendees.user_id', $user->id);
            })
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'desc')
            ->get();

            // Add image URLs to attended events
            $attendedEvents->transform(function ($event) {
                $event->image_url = $event->image_url ?: asset('images/default-event.jpg');
                return $event;
            });

            // Add fresh interests from database
            $user->interests = $freshUser->interests ? json_decode($freshUser->interests, true) : [];

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
     * Update user profile - FIXED IntelliSense errors
     */
    public function update(Request $request)
    {
        try {
            /** @var User $user */
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

            // Update user data using DB query to avoid IntelliSense issues
            $updated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'bio' => $validatedData['bio'] ?? null,
                    'linkedin_url' => $validatedData['linkedin_url'] ?? null,
                    'twitter_url' => $validatedData['twitter_url'] ?? null,
                    'profile_picture' => $validatedData['profile_picture'] ?? $user->profile_picture,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                throw new \Exception('Failed to update profile in database');
            }

            // Get fresh user data
            $freshUser = DB::table('users')->where('id', $user->id)->first();

            // Add profile picture URL for response
            $responseUser = (object) [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'bio' => $freshUser->bio,
                'linkedin_url' => $freshUser->linkedin_url,
                'twitter_url' => $freshUser->twitter_url,
                'profile_picture' => $freshUser->profile_picture 
                    ? asset('storage/' . $freshUser->profile_picture) 
                    : asset('images/default-avatar.png'),
                'interests' => $freshUser->interests ? json_decode($freshUser->interests, true) : []
            ];

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $responseUser
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
     * Update user password - FIXED IntelliSense errors
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'password' => ['required', 'confirmed', Password::min(8)],
            ]);

            /** @var User $user */
            $user = Auth::user();

            // Check if current password is correct
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password using DB query to avoid IntelliSense issues
            $updated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'password' => Hash::make($request->password),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                throw new \Exception('Failed to update password in database');
            }

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
     * Get user's events (organized and attended) - REAL-TIME
     */
    public function myEvents()
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Get organized events - REAL-TIME
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with(['category'])
                ->orderBy('start_time', 'desc')
                ->get();

            // Add REAL-TIME attendee count to organized events
            $organizedEvents->transform(function ($event) {
                $event->attendee_count = DB::table('attendees')
                    ->where('event_id', $event->id)
                    ->count();
                $event->image_url = $event->image_url ?: asset('images/default-event.jpg');
                return $event;
            });

            // Get attended events - REAL-TIME
            $attendedEvents = Event::whereExists(function($query) use ($user) {
                $query->select(DB::raw(1))
                      ->from('attendees')
                      ->whereRaw('attendees.event_id = events.id')
                      ->where('attendees.user_id', $user->id);
            })
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'desc')
            ->get();

            // Add image URL to attended events
            $attendedEvents->transform(function ($event) {
                $event->image_url = $event->image_url ?: asset('images/default-event.jpg');
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
     * Delete user account - FIXED IntelliSense errors
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required',
            ]);

            /** @var User $user */
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

            // Delete user's event registrations using DB query
            DB::table('attendees')->where('user_id', $user->id)->delete();

            // Delete user's organized events (cascade will handle attendees)
            DB::table('events')->where('organizer_id', $user->id)->delete();

            Log::info('User account deleted', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Delete the user using DB query to avoid IntelliSense issues
            $deleted = DB::table('users')->where('id', $user->id)->delete();

            if (!$deleted) {
                throw new \Exception('Failed to delete user from database');
            }

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
     * Get user statistics - REAL-TIME
     */
    public function getStats()
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Get fresh user data
            $freshUser = DB::table('users')->where('id', $user->id)->first();

            // REAL-TIME statistics from database
            $stats = [
                'events_organized' => DB::table('events')
                    ->where('organizer_id', $user->id)
                    ->count(),
                'events_attended' => DB::table('attendees')
                    ->where('user_id', $user->id)
                    ->count(),
                'upcoming_events' => DB::table('events')
                    ->join('attendees', 'events.id', '=', 'attendees.event_id')
                    ->where('attendees.user_id', $user->id)
                    ->where('events.start_time', '>', now())
                    ->count(),
                'interests_count' => count($freshUser->interests ? json_decode($freshUser->interests, true) : [])
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
