<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the member dashboard
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();
            
            // Get user's registered events (upcoming only)
            $registeredEvents = Event::whereHas('attendees', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('start_time', '>', Carbon::now())
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'asc')
            ->get();

            // Get user statistics
            $stats = [
                'total_events_attended' => Attendee::where('user_id', $user->id)->count(),
                'upcoming_registrations' => $registeredEvents->count(),
                'events_organized' => Event::where('organizer_id', $user->id)->count(),
            ];

            // Get recommended events based on user interests
            $recommendedEvents = $this->getRecommendedEvents($user);

            // Add profile picture URL
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');

            return response()->json([
                'success' => true,
                'user' => $user,
                'registered_events' => $registeredEvents,
                'recommended_events' => $recommendedEvents,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard loading error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data'
            ], 500);
        }
    }

    /**
     * Get recommended events for user based on interests
     */
    private function getRecommendedEvents($user)
    {
        try {
            $userInterests = $user->interests ?? [];
            
            if (empty($userInterests)) {
                // If no interests, return popular upcoming events
                return Event::where('start_time', '>', Carbon::now())
                    ->whereDoesntHave('attendees', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->with(['category', 'organizer'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }

            // Get events matching user interests
            return Event::where('start_time', '>', Carbon::now())
                ->whereHas('category', function($query) use ($userInterests) {
                    $query->whereIn('name', $userInterests);
                })
                ->whereDoesntHave('attendees', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['category', 'organizer'])
                ->orderBy('start_time', 'asc')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting recommended events: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Update user interests
     */
    public function updateInterests(Request $request)
    {
        try {
            $request->validate([
                'interests' => 'required|array',
                'interests.*' => 'string|max:50'
            ]);

            $user = Auth::user();
            $user->interests = $request->interests;
            $user->save();

            Log::info('User interests updated', [
                'user_id' => $user->id,
                'interests' => $request->interests
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Interests updated successfully!',
                'interests' => $user->interests
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating interests: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update interests'
            ], 500);
        }
    }

    /**
     * Get member profile data
     */
    public function profile()
    {
        try {
            $user = Auth::user();
            
            // Add profile picture URL
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');

            // Get user's events
            $organizedEvents = Event::where('organizer_id', $user->id)
                ->with('category')
                ->orderBy('start_time', 'desc')
                ->get();

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
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load profile data'
            ], 500);
        }
    }
}
