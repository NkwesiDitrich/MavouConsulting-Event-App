<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Event $event): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only organizers and admins can create events
        return in_array($user->role, ['organizer', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        // Only the event organizer or admin can update
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        // Only the event organizer or admin can delete
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }
    
    /**
     * Determine whether the user can check-in attendees.
     * Add this method if you need check-in authorization
     */
    public function checkin(User $user, Event $event): bool
    {
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can manage attendees.
     */
    public function manageAttendees(User $user, Event $event): bool
    {
        // Only the event organizer or admin can manage attendees
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can view analytics.
     */
    public function viewAnalytics(User $user, Event $event): bool
    {
        // Only the event organizer or admin can view analytics
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can send communications.
     */
    public function sendCommunications(User $user, Event $event): bool
    {
        // Only the event organizer or admin can send communications
        return $user->id === $event->organizer_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(User $user, Event $event): bool
    {
        // Users cannot register for their own events
        if ($user->id === $event->organizer_id) {
            return false;
        }

        // Check if registration is still open
        if ($event->registration_deadline && now() > $event->registration_deadline) {
            return false;
        }

        // Check if event is full
        if ($event->max_attendees && $event->attendees()->count() >= $event->max_attendees) {
            return $event->allow_waitlist;
        }

        return true;
    }
}