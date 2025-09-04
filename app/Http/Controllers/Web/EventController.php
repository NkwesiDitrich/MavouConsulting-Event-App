<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Browse public events without authentication
     */
    public function browseEvents(Request $request)
    {
        $query = Event::with(['organizer:id,name', 'category:id,name'])
            ->where('start_time', '>', now())
            ->orderBy('start_time');

        // Apply filters if provided
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('date_from')) {
            $query->where('start_time', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('start_time', '<=', $request->date_to);
        }

        $events = $query->paginate(12);

        return response()->json([
            'events' => $events,
            'filters' => [
                'categories' => Category::select('id', 'name')->get(),
                'event_types' => Event::distinct()->pluck('event_type')->filter(),
                'locations' => Event::distinct()->pluck('location')->filter()
            ]
        ]);
    }

    /**
     * Search events without authentication
     */
    public function searchEvents(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|max:50'
        ]);

        $searchTerm = $request->q;
        $limit = $request->limit ?? 10;

        $events = Event::with(['organizer:id,name', 'category:id,name'])
            ->where('start_time', '>', now())
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('location', 'like', "%{$searchTerm}%")
                      ->orWhere('event_type', 'like', "%{$searchTerm}%");
            })
            ->orderBy('start_time')
            ->limit($limit)
            ->get();

        return response()->json([
            'events' => $events,
            'search_term' => $searchTerm,
            'total_results' => $events->count()
        ]);
    }

    /**
     * Get event details without authentication
     */
    public function getEventDetails($id)
    {
        $event = Event::with(['organizer:id,name,profile_picture', 'category:id,name'])->findOrFail($id);
        
        // Add attendee count and basic stats
        $attendeeCount = \App\Models\Attendee::where('event_id', $event->id)->count();
        $checkedInCount = \App\Models\Attendee::where('event_id', $event->id)->where('checked_in', true)->count();

        return response()->json([
            'event' => $event,
            'stats' => [
                'attendee_count' => $attendeeCount,
                'checked_in_count' => $checkedInCount,
                'is_full' => $event->max_attendees ? $attendeeCount >= $event->max_attendees : false,
                'registration_open' => !$event->registration_deadline || now() <= $event->registration_deadline
            ],
            'organizer' => [
                'id' => $event->organizer->id,
                'name' => $event->organizer->name,
                'profile_picture' => $event->organizer->profile_picture ? asset('storage/profile_pictures/' . $event->organizer->profile_picture) : asset('images/default-avatar.png')
            ]
        ]);
    }

    /**
     * Get featured events
     */
    public function getFeaturedEvents()
    {
        $events = Event::with(['organizer:id,name', 'category:id,name'])
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        return response()->json([
            'featured_events' => $events
        ]);
    }

    /**
     * Register for an event
     */
    public function registerForEvent(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Please login to register for an event'
            ], 401);
        }

        $event = Event::findOrFail($id);
        $user = Auth::user();

        // Check if user is already registered
        $existingAttendee = \App\Models\Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingAttendee) {
            return response()->json([
                'message' => 'You are already registered for this event'
            ], 400);
        }

        // Check if event is full
        if ($event->max_attendees) {
            $currentAttendees = \App\Models\Attendee::where('event_id', $event->id)->count();
            if ($currentAttendees >= $event->max_attendees) {
                return response()->json([
                    'message' => 'This event is full'
                ], 400);
            }
        }

        // Check if registration is still open
        if ($event->registration_deadline && now() > $event->registration_deadline) {
            return response()->json([
                'message' => 'Registration for this event has closed'
            ], 400);
        }

        // Create attendee record
        $attendee = \App\Models\Attendee::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'checked_in' => false
        ]);

        // Update user role to attendee if not already
        if ($user->role === 'member') {
            $user->role = 'attendee';
            $user->save();
        }

        return response()->json([
            'message' => 'Successfully registered for the event!',
            'attendee' => $attendee
        ]);
    }

    /**
     * Get event categories
     */
    public function getCategories()
    {
        $categories = Category::withCount('events')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }
}