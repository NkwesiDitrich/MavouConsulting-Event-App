<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use App\Services\FilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage; // ADD THIS IMPORT

class EventController extends Controller
{
    use CanLoadRelationships;

    private  array $relations = ['organizer', 'category', 'attendees', 'attendees.user'];
    private FilterService $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        // Removed the redundant throttle middleware - it's already applied globally via API middleware group
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Display a listing of the resource with optional filtering.
     */
    public function index(Request $request)
    { 
        $query = $this->loadRelationships(Event::query());
        
        // Apply attendee count
        $query = $this->filterService->withAttendeeCount($query);

        // Apply filters if provided
        $filters = $request->only(['category', 'place', 'search', 'attendee_id', 'organizer_id', 'start_date', 'end_date']);
        
        // Check authorization for attendee filtering
        if (isset($filters['attendee_id']) && !empty($filters['attendee_id'])) {
            $user = $request->user();
            if (!$user || !$user->canViewAttendeeFilters()) {
                return response()->json([
                    'message' => 'You are not authorized to filter by attendee'
                ], 403);
            }
        }

        $query = $this->filterService->applyFilters($query, $filters);

        // Apply organizer filter if provided
        if (isset($filters['organizer_id']) && !empty($filters['organizer_id'])) {
            $query = $this->filterService->byOrganizer($query, $filters['organizer_id']);
        }

        // Apply date range filter if provided
        $query = $this->filterService->byDateRange($query, $filters['start_date'] ?? null, $filters['end_date'] ?? null);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations);
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'slogan' => 'nullable|string|max:255',
        'description' => 'required|string',
        'location' => 'required|string|max:255',
        'category_id' => 'nullable|exists:categories,id',
        'start_time' => 'required|date|after:now',
        'end_time' => 'required|date|after:start_time',
        'image_url' => 'nullable|url',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'event_type' => 'required|string|max:100',
        'audience' => 'nullable|string|max:100',
        'max_attendees' => 'nullable|integer|min:1',
        'ticket_price' => 'nullable|numeric|min:0',
        'is_free' => 'boolean',
        'registration_deadline' => 'nullable|date|before:start_time',
        'allow_waitlist' => 'boolean',
        'meeting_link' => 'nullable|url'
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/event_images', $imageName);
        $validatedData['image_path'] = $imageName;
    }

    // Set organizer_id to current user
    $validatedData['organizer_id'] = $request->user()->id;
    
    // If user is currently a member, upgrade them to organizer
    $user = $request->user();
    if ($user->role === 'member') {
        $user->role = 'organizer';
        $user->save();
    }

    $event = Event::create($validatedData);

    return new EventResource(
        $this->loadRelationships($event)
    );
}

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        
        return new EventResource(
            $this->loadRelationships($event)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
{
    // Authorization is handled automatically by authorizeResource in constructor

    $validatedData = $request->validate([
        'name' => 'sometimes|string|max:255',
        'slogan' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'location' => 'sometimes|string|max:255',
        'category_id' => 'sometimes|nullable|exists:categories,id',
        'start_time' => 'sometimes|date',
        'end_time' => 'sometimes|date|after:start_time',
        'image_url' => 'sometimes|nullable|url',
        'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        'event_type' => 'sometimes|in:online,in-person,hybrid',
        'audience' => 'sometimes|in:public,private'
    ]);

        // Handle image upload
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/event_images', $imageName);
        $validatedData['image_path'] = $imageName;
        
        // Remove old image if exists
        if ($event->image_path) {
            Storage::delete('public/event_images/' . $event->image_path);
        }
    }

    // Update the existing event
    $event->update($validatedData);

    return new EventResource(
        $this->loadRelationships($event)
    );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(status: 204);
    }

    /**
     * Search events by various criteria
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1'
        ]);

        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query = $this->filterService->bySearch($query, $request->q);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get events by category
     */
    public function byCategory(Request $request, string $category)
    {
        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query = $this->filterService->byCategory($query, $category);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get events by place
     */
    public function byPlace(Request $request, string $place)
    {
        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query = $this->filterService->byPlace($query, $place);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get events attended by a specific user (admin/organizer only)
     */
    public function attendedByUser(Request $request, int $userId)
    {
        $user = $request->user();
        if (!$user || !$user->canViewAttendeeFilters()) {
            return response()->json([
                'message' => 'You are not authorized to view this information'
            ], 403);
        }

        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query = $this->filterService->byAttendee($query, $userId);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get events organized by current user
     */
    public function myEvents(Request $request)
    {
        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query = $this->filterService->byOrganizer($query, $request->user()->id);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get upcoming events for the authenticated user
     */
    public function upcomingEvents(Request $request)
    {
        $user = $request->user();
        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        
        // Get events the user is attending that haven't occurred yet
        $query->whereHas('attendees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('start_time', '>', now());

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Get past events for the authenticated user
     */
    public function pastEvents(Request $request)
    {
        $user = $request->user();
        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        
        // Get events the user attended that have already occurred
        $query->whereHas('attendees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('end_time', '<', now());

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Filter events by event type
     */
    public function byEventType(Request $request, string $eventType)
    {
        $request->validate([
            'event_type' => 'in:online,in-person,hybrid'
        ]);

        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query->where('event_type', $eventType);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }

    /**
     * Filter events by audience
     */
    public function byAudience(Request $request, string $audience)
    {
        $request->validate([
            'audience' => 'in:public,private'
        ]);

        $query = $this->loadRelationships(Event::query());
        $query = $this->filterService->withAttendeeCount($query);
        $query->where('audience', $audience);

        return EventResource::collection(
            $query->latest()->paginate()
        );
    }
}