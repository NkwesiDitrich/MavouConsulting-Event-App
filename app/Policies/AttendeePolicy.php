<?php

namespace App\Policies;

use App\Models\Attendee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttendeePolicy
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
    public function view(User $user, Attendee $attendee): bool
    {
        // Allow event organizer to view any attendee of their event
        if ($user->id === $attendee->event->organizer_id) { // FIXED: user_id → organizer_id
            return true;
        }

        // Allow users to view their own attendance
        return $user->id === $attendee->user_id;
    }


    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendee $attendee): bool
    {
        return $user->id === $attendee->event->user_id ||
             $user->id === $attendee->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Attendee $attendee): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Attendee $attendee): bool
    {
        return false;
    }
}
