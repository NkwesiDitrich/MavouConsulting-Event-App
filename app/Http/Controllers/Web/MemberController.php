<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Get member dashboard data
     */
    public function dashboard()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get upcoming events the user might be interested in
            $upcomingEvents = Event::where('start_time', '>', now())
                ->with(['organizer:id,name', 'category:id,name'])
                ->orderBy('start_time')
                ->limit(6)
                ->get();

            // Get user's registered events
            $registeredEventIds = Attendee::where('user_id', $user->id)->pluck('event_id');
            $registeredEvents = Event::whereIn('id', $registeredEventIds)
                ->where('start_time', '>', now())
                ->with(['organizer:id,name', 'category:id,name'])
                ->orderBy('start_time')
                ->limit(3)
                ->get();

            // Get recommended events based on user interests
            $recommendedEvents = $this->getRecommendedEventsForUser($user);

            // Get user's profile picture URL
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
                    'events_attended' => $user->events_attended ?? 0,
                    'interests' => $user->interests ?? [],
                    'bio' => $user->bio
                ],
                'upcoming_events' => $upcomingEvents,
                'registered_events' => $registeredEvents,
                'recommended_events' => $recommendedEvents,
                'stats' => [
                    'total_events_attended' => $user->events_attended ?? 0,
                    'upcoming_registrations' => $registeredEvents->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard loading error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
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

            return response()->json([
                'message' => 'Interests updated successfully',
                'interests' => $user->interests
            ]);

        } catch (\Exception $e) {
            \Log::error('Update interests error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update interests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended events based on user interests
     */
    private function getRecommendedEventsForUser(User $user)
    {
        try {
            if (!$user->interests || empty($user->interests)) {
                return Event::where('start_time', '>', now())
                    ->with(['organizer:id,name', 'category:id,name'])
                    ->orderBy('start_time')
                    ->limit(3)
                    ->get();
            }

            // Simple recommendation based on event type and description
            return Event::where('start_time', '>', now())
                ->where(function ($query) use ($user) {
                    foreach ($user->interests as $interest) {
                        $query->orWhere('event_type', 'like', "%{$interest}%")
                              ->orWhere('description', 'like', "%{$interest}%")
                              ->orWhere('name', 'like', "%{$interest}%");
                    }
                })
                ->with(['organizer:id,name', 'category:id,name'])
                ->orderBy('start_time')
                ->limit(3)
                ->get();

        } catch (\Exception $e) {
            \Log::error('Get recommended events error: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }

    /**
     * Get recommended events endpoint
     */
    public function getRecommendedEvents()
    {
        try {
            $user = Auth::user();
            $recommendedEvents = $this->getRecommendedEventsForUser($user);

            return response()->json([
                'recommended_events' => $recommendedEvents
            ]);

        } catch (\Exception $e) {
            \Log::error('Get recommended events endpoint error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to get recommended events',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
