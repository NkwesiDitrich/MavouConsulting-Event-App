<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCommunication;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrganizerController extends Controller
{
    /**
     * Get organizer dashboard data
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get organizer's events
        $events = $user->organizedEvents()
            ->with(['category', 'attendees.user'])
            ->orderBy('start_time', 'desc')
            ->get();

        // Calculate statistics
        $totalEvents = $events->count();
        $upcomingEvents = $events->where('start_time', '>', now())->count();
        $totalAttendees = $events->sum(function ($event) {
            return $event->attendees->count();
        });
        $totalRevenue = $events->sum(function ($event) {
            return $event->attendees->count() * $event->ticket_price;
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'profile_picture' => $user->getProfilePicture()
            ],
            'events' => $events,
            'stats' => [
                'total_events' => $totalEvents,
                'upcoming_events' => $upcomingEvents,
                'total_attendees' => $totalAttendees,
                'total_revenue' => $totalRevenue
            ]
        ]);
    }

    /**
     * Update event registration settings
     */
    public function updateRegistrationSettings(Request $request, Event $event)
    {
        // Check authorization using policy
        $this->authorize('update', $event);

        $request->validate([
            'max_attendees' => 'nullable|integer|min:1',
            'ticket_price' => 'nullable|numeric|min:0',
            'is_free' => 'boolean',
            'registration_deadline' => 'nullable|date|after:now',
            'custom_questions' => 'nullable|array',
            'allow_waitlist' => 'boolean',
            'meeting_link' => 'nullable|url'
        ]);

        $event->update($request->only([
            'max_attendees',
            'ticket_price',
            'is_free',
            'registration_deadline',
            'custom_questions',
            'allow_waitlist',
            'meeting_link'
        ]));

        return response()->json([
            'message' => 'Registration settings updated successfully',
            'event' => $event->fresh()
        ]);
    }

    /**
     * Send communication to attendees
     */
    public function sendCommunication(Request $request, Event $event)
    {
        // Check authorization using policy
        $this->authorize('sendCommunications', $event);

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:all,checked_in,not_checked_in,waitlisted',
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'exists:users,id'
        ]);

        $communication = EventCommunication::create([
            'event_id' => $event->id,
            'sender_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'recipient_type' => $request->recipient_type,
            'recipient_ids' => $request->recipient_ids,
            'sent_at' => now()
        ]);

        // Here you would typically send emails/notifications to the recipients
        // For now, we'll just return success

        return response()->json([
            'message' => 'Communication sent successfully',
            'communication' => $communication
        ]);
    }

    /**
     * Get event analytics
     */
    public function getEventAnalytics(Event $event)
    {
        // Check authorization using policy
        $this->authorize('viewAnalytics', $event);

        $attendees = $event->attendees()->with('user')->get();
        
        $analytics = [
            'total_registrations' => $attendees->count(),
            'checked_in_count' => $attendees->where('checked_in', true)->count(),
            'revenue' => $attendees->count() * $event->ticket_price,
            'registration_timeline' => $this->getRegistrationTimeline($event),
            'attendee_demographics' => $this->getAttendeeDemographics($attendees),
            'check_in_rate' => $attendees->count() > 0 ? 
                ($attendees->where('checked_in', true)->count() / $attendees->count()) * 100 : 0
        ];

        return response()->json([
            'event' => $event,
            'analytics' => $analytics
        ]);
    }

    /**
     * Handle post-event actions
     */
    public function postEventActions(Request $request, Event $event)
    {
        // Check authorization using policy
        $this->authorize('update', $event);

        $request->validate([
            'action' => 'required|in:send_thank_you,share_materials,request_feedback',
            'content' => 'nullable|string',
            'materials' => 'nullable|array'
        ]);

        $action = $request->action;
        $result = [];

        switch ($action) {
            case 'send_thank_you':
                $result = $this->sendThankYouMessage($event, $request->content);
                break;
            case 'share_materials':
                $result = $this->shareMaterials($event, $request->materials);
                break;
            case 'request_feedback':
                $result = $this->requestFeedback($event);
                break;
        }

        return response()->json([
            'message' => 'Post-event action completed successfully',
            'result' => $result
        ]);
    }

    private function getRegistrationTimeline(Event $event)
    {
        return $event->attendees()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getAttendeeDemographics($attendees)
    {
        // Simple demographics based on available data
        return [
            'total_attendees' => $attendees->count(),
            'checked_in' => $attendees->where('checked_in', true)->count(),
            'not_checked_in' => $attendees->where('checked_in', false)->count()
        ];
    }

    private function sendThankYouMessage(Event $event, $content = null)
    {
        $defaultMessage = "Thank you for attending {$event->name}! We hope you enjoyed the event.";
        $message = $content ?: $defaultMessage;

        EventCommunication::create([
            'event_id' => $event->id,
            'sender_id' => Auth::id(),
            'subject' => "Thank you for attending {$event->name}",
            'message' => $message,
            'recipient_type' => 'checked_in',
            'sent_at' => now()
        ]);

        return ['message' => 'Thank you message sent to all checked-in attendees'];
    }

    private function shareMaterials(Event $event, $materials)
    {
        // Implementation for sharing materials
        return ['message' => 'Materials shared with attendees', 'materials' => $materials];
    }

    private function requestFeedback(Event $event)
    {
        EventCommunication::create([
            'event_id' => $event->id,
            'sender_id' => Auth::id(),
            'subject' => "Please share your feedback for {$event->name}",
            'message' => "We'd love to hear your thoughts about {$event->name}. Please take a moment to share your feedback.",
            'recipient_type' => 'checked_in',
            'sent_at' => now()
        ]);

        return ['message' => 'Feedback request sent to all checked-in attendees'];
    }
}

