<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
     use CanLoadRelationships;

    private array $relations = ['user'];

     public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'update']);
         $this->middleware('throttle:api')
            ->only(['store', 'destroy']); 
        $this->authorizeResource(Attendee::class, 'attendee');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
         $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection(
            $attendees->paginate()
         );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
         // Check if user is already registered
        $existingAttendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingAttendee) {
            return response()->json([
                'message' => 'You are already registered for this event'
            ], 409);
        }
        // adding new attendee
         $attendee = $this->loadRelationships(
            $event->attendees()->create([
                'user_id' => $request->user()->id
            ])
        );

        // Update user's events_attended count
        $user = $request->user();
        $user->updateEventsAttendedCount();

        return new AttendeeResource($attendee);

    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        return new AttendeeResource(
            $this->loadRelationships($attendee)
        );
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, Event $event, Attendee $attendee)
    {
        // Authorization - only event organizer or admin can check in attendees
        $user = $request->user();
        if ($user->id !== $event->organizer_id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Only the event organizer or admin can check in attendees'
            ], 403);
        }

        // Check if already checked in
        if ($attendee->checked_in) {
            return response()->json([
                'message' => 'Attendee is already checked in'
            ], 409);
        }

        // Perform check-in
        $attendee->update(['checked_in' => true]);
        $attendee->refresh(); // Refresh to get updated attributes

        return new AttendeeResource(
            $this->loadRelationships($attendee)
        );
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        // Authorization - user can only cancel their own attendance
        if (request()->user()->id !== $attendee->user_id) {
            return response()->json([
                'message' => 'You can only cancel your own attendance'
            ], 403);
        }

        $attendee->delete();

        return response()->json([
            'message' => 'Attendance cancelled successfully'
        ], 204);
    }

    /**
     * Check in an attendee (additional method)
     */
    public function checkIn(Request $request, Event $event, Attendee $attendee)
    {
        // Authorization - only event organizer or admin can check in attendees
        $user = $request->user();
        if ($user->id !== $event->organizer_id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Only the event organizer or admin can check in attendees'
            ], 403);
        }

        // Check if already checked in
        if ($attendee->checked_in) {
            return response()->json([
                'message' => 'Attendee is already checked in'
            ], 409);
        }

        // Perform check-in
        $attendee->update(['checked_in' => true]);
        $attendee->refresh();

        return response()->json([
            'message' => 'Attendee checked in successfully',
            'attendee' => new AttendeeResource($this->loadRelationships($attendee))
        ]);
    }

    /**
     * Get enhanced attendee information
     */
    public function getAttendeeInfo(Request $request, Event $event, Attendee $attendee)
    {
        // Authorization - only event organizer or admin can view detailed attendee info
        $user = $request->user();
        if ($user->id !== $event->organizer_id && !$user->canViewAttendeeFilters()) {
            return response()->json([
                'message' => 'You are not authorized to view detailed attendee information'
            ], 403);
        }

        return response()->json([
            'attendee_info' => $attendee->getAttendeeInfo()
        ]);
    }
}
