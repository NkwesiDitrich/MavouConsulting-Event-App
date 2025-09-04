<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use Illuminate\Http\Request;

class PublicController extends Controller
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
    public function getEventDetails(Event $event)
    {
        $event->load(['organizer:id,name,profile_picture', 'category:id,name']);
        
        // Add attendee count and basic stats
        $attendeeCount = $event->attendees()->count();
        $checkedInCount = $event->attendees()->where('checked_in', true)->count();

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
                'profile_picture' => $event->organizer->getProfilePicture()
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
     * Get event categories
     */
    public function getCategories()
    {
        $categories = Category::withCount('events')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Get events by category
     */
    public function getEventsByCategory(Category $category)
    {
        $events = $category->events()
            ->with(['organizer:id,name'])
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->paginate(12);

        return response()->json([
            'category' => $category,
            'events' => $events
        ]);
    }
}

