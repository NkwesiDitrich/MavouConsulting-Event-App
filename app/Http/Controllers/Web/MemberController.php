<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Category;
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
     * Display the member dashboard with real-time database tracking
     */
    public function dashboard()
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Get REAL-TIME user's registered events (upcoming only) - FIXED QUERY
            $registeredEvents = Event::whereHas('attendees', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('start_time', '>', Carbon::now())
            ->with(['category', 'organizer'])
            ->orderBy('start_time', 'asc')
            ->get();

            // Get REAL-TIME user statistics - FIXED QUERIES
            $totalEventsAttended = DB::table('attendees')
                ->where('user_id', $user->id)
                ->count();
                
            $eventsOrganized = DB::table('events')
                ->where('organizer_id', $user->id)
                ->count();

            $stats = [
                'total_events_attended' => $totalEventsAttended,
                'upcoming_registrations' => $registeredEvents->count(),
                'events_organized' => $eventsOrganized,
            ];

            // Get recommended events based on user interests - REAL-TIME
            $recommendedEvents = $this->getRecommendedEvents($user);

            // Get user with fresh data from database
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            $userInterests = $freshUser->interests ? json_decode($freshUser->interests, true) : [];

            // Add profile picture URL
            $user->profile_picture = $user->profile_picture 
                ? asset('storage/' . $user->profile_picture) 
                : asset('images/default-avatar.png');
            
            // Add interests to user object
            $user->interests = $userInterests;

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
     * Get recommended events for user based on interests - REAL-TIME
     */
    private function getRecommendedEvents($user)
    {
        try {
            // Get fresh user interests from database
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            $userInterests = $freshUser->interests ? json_decode($freshUser->interests, true) : [];
            
            if (empty($userInterests)) {
                // If no interests, return popular upcoming events (not registered by user)
                return Event::where('start_time', '>', Carbon::now())
                    ->whereNotExists(function($query) use ($user) {
                        $query->select(DB::raw(1))
                              ->from('attendees')
                              ->whereRaw('attendees.event_id = events.id')
                              ->where('attendees.user_id', $user->id);
                    })
                    ->with(['category', 'organizer'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }

            // Get events matching user interests (not registered by user)
            return Event::where('start_time', '>', Carbon::now())
                ->whereHas('category', function($query) use ($userInterests) {
                    $query->whereIn('name', $userInterests);
                })
                ->whereNotExists(function($query) use ($user) {
                    $query->select(DB::raw(1))
                          ->from('attendees')
                          ->whereRaw('attendees.event_id = events.id')
                          ->where('attendees.user_id', $user->id);
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
     * Update user interests - FIXED to handle all cases
     */
    public function updateInterests(Request $request)
    {
        try {
            $request->validate([
                'interests' => 'array', // Allow empty array
                'interests.*' => 'string|max:50'
            ]);

            /** @var User $user */
            $user = Auth::user();
            
            // Handle empty interests array (user unchecked all)
            $interests = $request->interests ?? [];
            
            // Update interests using DB query to avoid IntelliSense issues
            $updated = DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'interests' => json_encode($interests),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                throw new \Exception('Failed to update interests in database');
            }

            Log::info('User interests updated', [
                'user_id' => $user->id,
                'interests' => $interests,
                'interests_count' => count($interests)
            ]);

            return response()->json([
                'success' => true,
                'message' => count($interests) > 0 
                    ? 'Interests updated successfully!' 
                    : 'All interests cleared successfully!',
                'interests' => $interests
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
     * Get event details for modal popup
     */
    public function getEventDetails($eventId)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            $event = Event::with(['category', 'organizer'])
                ->findOrFail($eventId);
            
            // Check if user is registered for this event
            $isRegistered = DB::table('attendees')
                ->where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->exists();
            
            // Get attendee count
            $attendeeCount = DB::table('attendees')
                ->where('event_id', $eventId)
                ->count();

            return response()->json([
                'success' => true,
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'slogan' => $event->slogan,
                    'location' => $event->location,
                    'category' => $event->category ? $event->category->name : 'General',
                    'event_type' => $event->event_type,
                    'audience' => $event->audience,
                    'start_time' => $event->start_time->format('Y-m-d H:i:s'),
                    'end_time' => $event->end_time->format('Y-m-d H:i:s'),
                    'organizer_id' => $event->organizer_id,
                    'organizer_name' => $event->organizer ? $event->organizer->name : 'Unknown',
                    'image_url' => $event->image_url ?: asset('images/default-event.jpg'),
                    'attendee_count' => $attendeeCount,
                    'is_registered' => $isRegistered,
                    'is_organizer' => $event->organizer_id === $user->id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting event details: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load event details'
            ], 500);
        }
    }

    /**
     * Get member profile data - REAL-TIME
     */
    public function profile()
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
