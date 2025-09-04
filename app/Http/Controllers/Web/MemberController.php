<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    /**
     * Get member dashboard data
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get upcoming events the user might be interested in
        $upcomingEvents = Event::where('start_time', '>', now())
            ->with(['organizer', 'category'])
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        // Get user's registered events
        $registeredEventIds = \App\Models\Attendee::where('user_id', $user->id)->pluck('event_id');
        $registeredEvents = Event::whereIn('id', $registeredEventIds)
            ->where('start_time', '>', now())
            ->with(['organizer', 'category'])
            ->orderBy('start_time')
            ->limit(3)
            ->get();

        // Get recommended events based on user interests
        $recommendedEvents = $this->getRecommendedEvents($user);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture ? asset('storage/profile_pictures/' . $user->profile_picture) : asset('images/default-avatar.png'),
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
    }

    /**
     * Update user interests
     */
    public function updateInterests(Request $request)
    {
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
    }

    /**
     * Get recommended events based on user interests
     */
    private function getRecommendedEvents(User $user)
    {
        if (!$user->interests || empty($user->interests)) {
            return Event::where('start_time', '>', now())
                ->with(['organizer', 'category'])
                ->orderBy('start_time')
                ->limit(3)
                ->get();
        }

        // Simple recommendation based on event type and category
        return Event::where('start_time', '>', now())
            ->where(function ($query) use ($user) {
                foreach ($user->interests as $interest) {
                    $query->orWhere('event_type', 'like', "%{$interest}%")
                          ->orWhere('description', 'like', "%{$interest}%");
                }
            })
            ->with(['organizer', 'category'])
            ->orderBy('start_time')
            ->limit(3)
            ->get();
    }

    /**
     * Get recommended events endpoint
     */
    public function getRecommendedEvents()
    {
        $user = Auth::user();
        $recommendedEvents = $this->getRecommendedEvents($user);

        return response()->json([
            'recommended_events' => $recommendedEvents
        ]);
    }
}
