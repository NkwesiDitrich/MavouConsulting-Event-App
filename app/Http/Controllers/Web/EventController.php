<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Category;
use App\Models\Attendee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Browse events with filtering and search
     */
    public function browseEvents(Request $request)
    {
        try {
            $query = Event::with(['category', 'organizer'])
                ->where('start_time', '>', Carbon::now());

            // Apply filters
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }

            if ($request->filled('location')) {
                $query->where('location', 'like', '%' . $request->location . '%');
            }

            if ($request->filled('date_from')) {
                $query->where('start_time', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('start_time', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            }

            // Sort events
            $sortBy = $request->get('sort', 'start_time');
            $sortOrder = $request->get('order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->get('per_page', 12);
            $events = $query->paginate($perPage);

            // Add additional data for each event
            $events->getCollection()->transform(function ($event) {
                $event->image_url = $event->image 
                    ? asset('storage/' . $event->image) 
                    : asset('images/default-event.jpg');
                
                $event->attendee_count = $event->attendees()->count();
                $event->is_full = $event->attendee_count >= $event->max_attendees;
                
                // Check if current user is registered
                if (Auth::check()) {
                    $event->is_registered = $event->attendees()
                        ->where('user_id', Auth::id())
                        ->exists();
                } else {
                    $event->is_registered = false;
                }

                return $event;
            });

            // Get categories for filter dropdown
            $categories = Category::withCount('events')->get();

            return response()->json([
                'success' => true,
                'events' => $events,
                'categories' => $categories,
                'filters' => [
                    'category' => $request->category,
                    'location' => $request->location,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'search' => $request->search,
                    'sort' => $sortBy,
                    'order' => $sortOrder
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error browsing events: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load events'
            ], 500);
        }
    }

    /**
     * Search events (for AJAX search)
     */
    public function searchEvents(Request $request)
    {
        try {
            $searchTerm = $request->get('q', '');
            
            if (empty($searchTerm)) {
                return response()->json([
                    'success' => true,
                    'events' => []
                ]);
            }

            $events = Event::with(['category', 'organizer'])
                ->where('start_time', '>', Carbon::now())
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                          ->orWhere('description', 'like', '%' . $searchTerm . '%')
                          ->orWhere('location', 'like', '%' . $searchTerm . '%');
                })
                ->orderBy('start_time', 'asc')
                ->limit(10)
                ->get();

            // Add additional data
            $events->transform(function ($event) {
                $event->image_url = $event->image 
                    ? asset('storage/' . $event->image) 
                    : asset('images/default-event.jpg');
                
                $event->attendee_count = $event->attendees()->count();
                
                return $event;
            });

            return response()->json([
                'success' => true,
                'events' => $events
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching events: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }

    /**
     * Get event details
     */
    public function getEventDetails($id)
    {
        try {
            $event = Event::with(['category', 'organizer', 'attendees.user'])
                ->findOrFail($id);

            // Add additional data
            $event->image_url = $event->image 
                ? asset('storage/' . $event->image) 
                : asset('images/default-event.jpg');
            
            $event->attendee_count = $event->attendees()->count();
            $event->is_full = $event->attendee_count >= $event->max_attendees;
            $event->spots_remaining = $event->max_attendees - $event->attendee_count;
            
            // Check if registration is still open
            $event->registration_open = Carbon::now()->lt(Carbon::parse($event->registration_deadline ?? $event->start_time));
            
            // Check if current user is registered
            if (Auth::check()) {
                $event->is_registered = $event->attendees()
                    ->where('user_id', Auth::id())
                    ->exists();
                
                $event->can_register = !$event->is_registered && 
                                     !$event->is_full && 
                                     $event->registration_open;
            } else {
                $event->is_registered = false;
                $event->can_register = !$event->is_full && $event->registration_open;
            }

            // Add organizer profile picture
            if ($event->organizer && $event->organizer->profile_picture) {
                $event->organizer->profile_picture_url = asset('storage/' . $event->organizer->profile_picture);
            } else {
                $event->organizer->profile_picture_url = asset('images/default-avatar.png');
            }

            return response()->json([
                'success' => true,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting event details: ' . $e->getMessage(), [
                'event_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }
    }

    /**
     * Register for an event
     */
    public function registerForEvent(Request $request, $id)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to register for events'
                ], 401);
            }

            $user = Auth::user();
            $event = Event::findOrFail($id);

            // Check if event exists and is in the future
            if (Carbon::parse($event->start_time)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot register for past events'
                ], 400);
            }

            // Check if registration is still open
            $registrationDeadline = $event->registration_deadline ?? $event->start_time;
            if (Carbon::now()->gt(Carbon::parse($registrationDeadline))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration deadline has passed'
                ], 400);
            }

            // Check if user is already registered
            $existingRegistration = Attendee::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already registered for this event'
                ], 400);
            }

            // Check if event is full
            $currentAttendees = Attendee::where('event_id', $event->id)->count();
            if ($currentAttendees >= $event->max_attendees) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event is full'
                ], 400);
            }

            // Register user for event
            $attendee = new Attendee();
            $attendee->user_id = $user->id;
            $attendee->event_id = $event->id;
            $attendee->registration_date = Carbon::now();
            $attendee->status = 'registered';
            $attendee->save();

            Log::info('User registered for event', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'event_name' => $event->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully registered for ' . $event->name . '!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error registering for event: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'event_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get featured events
     */
    public function getFeaturedEvents()
    {
        try {
            $events = Event::with(['category', 'organizer'])
                ->where('start_time', '>', Carbon::now())
                ->where('is_featured', true)
                ->orderBy('start_time', 'asc')
                ->limit(6)
                ->get();

            // Add additional data
            $events->transform(function ($event) {
                $event->image_url = $event->image 
                    ? asset('storage/' . $event->image) 
                    : asset('images/default-event.jpg');
                
                $event->attendee_count = $event->attendees()->count();
                
                return $event;
            });

            return response()->json([
                'success' => true,
                'events' => $events
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting featured events: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load featured events'
            ], 500);
        }
    }

    /**
     * Unregister from an event
     */
    public function unregisterFromEvent($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login first'
                ], 401);
            }

            $user = Auth::user();
            $event = Event::findOrFail($id);

            $attendee = Attendee::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if (!$attendee) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not registered for this event'
                ], 400);
            }

            // Check if event has already started
            if (Carbon::parse($event->start_time)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot unregister from events that have already started'
                ], 400);
            }

            $attendee->delete();

            Log::info('User unregistered from event', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'event_name' => $event->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully unregistered from ' . $event->name
            ]);

        } catch (\Exception $e) {
            Log::error('Error unregistering from event: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'event_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister. Please try again.'
            ], 500);
        }
    }
}
