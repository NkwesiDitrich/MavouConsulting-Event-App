<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's registered events
        $registeredEvents = EventRegistration::with(['event.category', 'event.organizer'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get upcoming events the user might be interested in
        $upcomingEvents = Event::with(['category', 'organizer'])
            ->where('start_time', '>', now())
            ->where('status', 'published')
            ->whereNotIn('id', $registeredEvents->pluck('event_id'))
            ->orderBy('start_time', 'asc')
            ->limit(6)
            ->get();

        // Get user's event statistics
        $stats = [
            'total_registered' => $registeredEvents->count(),
            'upcoming_events' => $registeredEvents->filter(function($registration) {
                return $registration->event && $registration->event->start_time > now();
            })->count(),
            'past_events' => $registeredEvents->filter(function($registration) {
                return $registration->event && $registration->event->start_time <= now();
            })->count(),
        ];

        return view('member.dashboard', compact('registeredEvents', 'upcomingEvents', 'stats'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify current password if changing password
            if ($request->filled('current_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                $user->profile_picture = $path;
            }

            // Update user data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->bio = $request->bio;

            // Update password if provided
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'bio' => $user->bio,
                    'profile_picture_url' => $user->profile_picture ? Storage::url($user->profile_picture) : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myEvents()
    {
        $user = Auth::user();
        
        $registeredEvents = EventRegistration::with(['event.category', 'event.organizer'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('member.my-events', compact('registeredEvents'));
    }

    // NEW METHOD: Get event details for member dashboard
    public function getEventDetails($eventId)
    {
        try {
            $user = Auth::user();
            
            $event = Event::with(['category', 'organizer', 'registrations'])
                ->findOrFail($eventId);

            // Check if user is registered for this event
            $isRegistered = EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->exists();

            // Get registration details if registered
            $registration = null;
            if ($isRegistered) {
                $registration = EventRegistration::where('user_id', $user->id)
                    ->where('event_id', $eventId)
                    ->first();
            }

            // Calculate attendee count
            $attendeeCount = $event->registrations()->count();

            // Check if registration is still open
            $registrationOpen = $event->start_time > now() && 
                               $event->status === 'published' &&
                               (!$event->max_attendees || $attendeeCount < $event->max_attendees);

            return response()->json([
                'success' => true,
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'location' => $event->location,
                    'is_free' => $event->is_free,
                    'ticket_price' => $event->ticket_price,
                    'max_attendees' => $event->max_attendees,
                    'status' => $event->status,
                    'image_url' => $event->image_url,
                    'category' => $event->category ? [
                        'id' => $event->category->id,
                        'name' => $event->category->name
                    ] : null,
                    'organizer' => [
                        'id' => $event->organizer->id,
                        'name' => $event->organizer->name,
                        'email' => $event->organizer->email
                    ]
                ],
                'stats' => [
                    'attendee_count' => $attendeeCount,
                    'registration_open' => $registrationOpen,
                    'is_registered' => $isRegistered,
                    'registration_date' => $registration ? $registration->created_at : null
                ],
                'registration' => $registration ? [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'registered_at' => $registration->created_at
                ] : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found or access denied'
            ], 404);
        }
    }

    // Register for an event
    public function registerForEvent(Request $request, $eventId)
    {
        try {
            $user = Auth::user();
            
            $event = Event::findOrFail($eventId);

            // Check if event is still open for registration
            if ($event->start_time <= now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is closed for this event'
                ], 400);
            }

            if ($event->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'This event is not available for registration'
                ], 400);
            }

            // Check if user is already registered
            $existingRegistration = EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already registered for this event'
                ], 400);
            }

            // Check if event has reached max capacity
            if ($event->max_attendees) {
                $currentAttendees = EventRegistration::where('event_id', $eventId)->count();
                if ($currentAttendees >= $event->max_attendees) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This event has reached maximum capacity'
                    ], 400);
                }
            }

            // Create registration
            $registration = EventRegistration::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'status' => 'confirmed',
                'registered_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully registered for the event!',
                'registration' => [
                    'id' => $registration->id,
                    'status' => $registration->status,
                    'registered_at' => $registration->created_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register for event: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cancel event registration
    public function cancelRegistration($eventId)
    {
        try {
            $user = Auth::user();
            
            $registration = EventRegistration::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found'
                ], 404);
            }

            $event = Event::findOrFail($eventId);

            // Check if cancellation is allowed (e.g., not too close to event start)
            if ($event->start_time <= now()->addHours(24)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel registration less than 24 hours before the event'
                ], 400);
            }

            $registration->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registration cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel registration: ' . $e->getMessage()
            ], 500);
        }
    }
}
