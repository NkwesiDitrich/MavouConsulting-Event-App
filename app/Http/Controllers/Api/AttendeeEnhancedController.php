<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\EventFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendeeEnhancedController extends Controller
{
    /**
     * Get attendee dashboard for a specific event
     */
    public function getEventDashboard(Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        $otherAttendees = [];
        if ($event->allow_attendee_list ?? true) {
            $otherAttendees = $event->attendees()
                ->with('user:id,name,profile_picture')
                ->where('user_id', '!=', $user->id)
                ->get()
                ->map(function ($attendee) {
                    return [
                        'id' => $attendee->user->id,
                        'name' => $attendee->user->name,
                        'profile_picture' => $attendee->user->getProfilePicture()
                    ];
                });
        }

        return response()->json([
            'event' => $event->load(['organizer', 'category']),
            'attendee_info' => [
                'registered_at' => $attendee->created_at,
                'checked_in' => $attendee->checked_in,
                'checked_in_at' => $attendee->checked_in ? $attendee->updated_at : null
            ],
            'other_attendees' => $otherAttendees,
            'networking_enabled' => $event->allow_networking ?? true
        ]);
    }

    /**
     * Get attendee ticket with QR code
     */
    public function getTicket(Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        // Generate QR code data
        $qrData = json_encode([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'attendee_id' => $attendee->id,
            'timestamp' => now()->timestamp
        ]);

        // For now, return QR data - you can integrate with QR code library later
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);

        return response()->json([
            'ticket' => [
                'event_name' => $event->name,
                'attendee_name' => $user->name,
                'event_date' => $event->start_time,
                'location' => $event->location,
                'ticket_type' => $event->is_free ? 'Free' : 'Paid',
                'qr_code_url' => $qrCodeUrl,
                'qr_data' => $qrData,
                'ticket_id' => $attendee->id,
                'registered_at' => $attendee->created_at
            ]
        ]);
    }

    /**
     * Get networking opportunities for an event
     */
    public function getNetworking(Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        $attendees = $event->attendees()
            ->with('user:id,name,profile_picture,bio,linkedin_url,twitter_url,interests')
            ->where('user_id', '!=', $user->id)
            ->get()
            ->map(function ($attendee) {
                return [
                    'id' => $attendee->user->id,
                    'name' => $attendee->user->name,
                    'profile_picture' => $attendee->user->getProfilePicture(),
                    'bio' => $attendee->user->bio,
                    'linkedin_url' => $attendee->user->linkedin_url,
                    'twitter_url' => $attendee->user->twitter_url,
                    'interests' => $attendee->user->interests,
                    'checked_in' => $attendee->checked_in
                ];
            });

        return response()->json([
            'attendees' => $attendees,
            'networking_tips' => [
                'Connect with attendees who share similar interests',
                'Check out the LinkedIn profiles of other participants',
                'Don\'t forget to follow up after the event'
            ]
        ]);
    }

    /**
     * Access event tools and content
     */
    public function getEventTools(Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        return response()->json([
            'event_tools' => [
                'meeting_link' => $event->meeting_link,
                'agenda' => $event->agenda ?? null,
                'materials' => $event->materials ?? [],
                'live_stream' => $event->live_stream_url ?? null,
                'chat_enabled' => $event->chat_enabled ?? false
            ],
            'event_status' => [
                'is_live' => $event->start_time <= now() && $event->end_time >= now(),
                'has_started' => $event->start_time <= now(),
                'has_ended' => $event->end_time < now()
            ]
        ]);
    }

    /**
     * Submit event feedback
     */
    public function submitFeedback(Request $request, Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
            'answers' => 'nullable|array'
        ]);

        $feedback = EventFeedback::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'answers' => $request->answers
            ]
        );

        return response()->json([
            'message' => 'Feedback submitted successfully',
            'feedback' => $feedback
        ]);
    }

    /**
     * Check in to event (self check-in for virtual events)
     */
    public function selfCheckIn(Event $event)
    {
        $user = Auth::user();
        $attendee = Attendee::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendee) {
            return response()->json(['error' => 'You are not registered for this event'], 404);
        }

        if ($attendee->checked_in) {
            return response()->json(['error' => 'You are already checked in'], 400);
        }

        $attendee->update(['checked_in' => true]);

        return response()->json([
            'message' => 'Successfully checked in to the event',
            'checked_in_at' => $attendee->updated_at
        ]);
    }
}

