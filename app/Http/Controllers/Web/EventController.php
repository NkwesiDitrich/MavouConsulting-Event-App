<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Browse public events without authentication
     */
    public function browseEvents(Request $request)
    {
        try {
            $query = Event::with(['organizer:id,name', 'category:id,name'])
                ->where('start_time', '>', now())
                ->orderBy('start_time');

            // Apply filters if provided
            if ($request->has('category') && $request->category) {
                $query->where('category_id', $request->category);
            }

            if ($request->has('event_type') && $request->event_type) {
                $query->where('event_type', $request->event_type);
            }

            if ($request->has('location') && $request->location) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->where('start_time', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->where('start_time', '<=', $request->date_to);
            }

            $events = $query->paginate(12);

            // Add attendee counts to events
            foreach ($events as $event) {
                $event->attendees_count = Attendee::where('event_id', $event->id)->count();
            }

            return response()->json([
                'events' => $events,
                'filters' => [
                    'categories' => Category::select('id', 'name')->get(),
                    'event_types' => Event::distinct()->pluck('event_type')->filter()->values(),
                    'locations' => Event::distinct()->pluck('location')->filter()->values()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Browse events error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search events without authentication
     */
    public function searchEvents(Request $request)
    {
        try {
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

            // Add attendee counts
            foreach ($events as $event) {
                $event->attendees_count = Attendee::where('event_id', $event->id)->count();
            }

            return response()->json([
                'events' => $events,
                'search_term' => $searchTerm,
                'total_results' => $events->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Search events error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to search events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event details without authentication
     */
    public function getEventDetails($id)
    {
        try {
            $event = Event::with(['organizer:id,name,profile_picture', 'category:id,name'])
                ->findOrFail($id);

            // Add attendee count and basic stats
            $attendeeCount = Attendee::where('event_id', $event->id)->count();
            $checkedInCount = Attendee::where('event_id', $event->id)
                ->where('checked_in', true)->count();

            // Get organizer profile picture URL
            $organizerProfilePicture = $event->organizer->profile_picture ? 
                asset('storage/profile_pictures/' . $event->organizer->profile_picture) : 
                'https://ui-avatars.com/api/?name=' . urlencode($event->organizer->name) . 
                '&background=' . substr(md5($event->organizer->id), 0, 6) . '&color=fff&size=200';

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
                    'profile_picture' => $organizerProfilePicture
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Get event details error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load event details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured events
     */
    public function getFeaturedEvents()
    {
        try {
            $events = Event::with(['organizer:id,name', 'category:id,name'])
                ->where('start_time', '>', now())
                ->orderBy('start_time')
                ->limit(6)
                ->get();

            // Add attendee counts
            foreach ($events as $event) {
                $event->attendees_count = Attendee::where('event_id', $event->id)->count();
            }

            return response()->json([
                'featured_events' => $events
            ]);

        } catch (\Exception $e) {
            \Log::error('Get featured events error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load featured events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register for an event - REQUIRES AUTHENTICATION
     */
    public function registerForEvent(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Please login to register for an event'
                ], 401);
            }

            $event = Event::findOrFail($id);
            $user = Auth::user();

            // Check if user is already registered
            $existingAttendee = Attendee::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingAttendee) {
                return response()->json([
                    'message' => 'You are already registered for this event'
                ], 400);
            }

            // Check if event is full
            if ($event->max_attendees) {
                $currentAttendees = Attendee::where('event_id', $event->id)->count();
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
            $attendee = Attendee::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'checked_in' => false
            ]);

            // User remains 'member' globally - they become 'attendee' only for this specific event
            // No need to change global role as per your requirements

            return response()->json([
                'message' => 'Successfully registered for the event!',
                'attendee' => $attendee
            ]);

        } catch (\Exception $e) {
            \Log::error('Register for event error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to register for event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event categories
     */
    public function getCategories()
    {
        try {
            $categories = Category::withCount('events')->get();

            return response()->json([
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Get categories error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
