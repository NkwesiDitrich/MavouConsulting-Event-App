<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Testing\Fluent\Concerns\Has;

class Attendee extends Model
{
    //
    use HasFactory;

    protected $fillable = ['user_id', 'event_id', 'checked_in'];

    protected $casts = [
        'checked_in' => 'boolean',  // Add this
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

     public static function registerForEvent(int $eventId, int $userId): Attendee
    {
        return static::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'checked_in' => false
        ]);
    }

    /**
     * Cancel attendance
     */
    public function cancelAttendance(): bool
    {
        return $this->delete();
    }

    /**
     * Check in the attendee
     */
    public function checkIn(): bool
    {
        return $this->update(['checked_in' => true]);
    }

    /**
     * Check if attendee has checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->checked_in;
    }

    /**
     * Get the event details
     */
    public function getEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Get the user details with enhanced information
     */
    public function getUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get attendee information with user profile details
     */
    public function getAttendeeInfo(): array
    {
        $user = $this->user;
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_picture' => $user->getProfilePicture(),
            'events_attended' => $user->getEventsAttendedCount(),
            'attended_events' => $user->getAttendances()->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'title' => $event->title,
                    'date' => $event->start_time,
                ];
            }),
            'registered_at' => $this->created_at,
            'checked_in' => $this->checked_in,
            'checked_in_at' => $this->checked_in ? $this->updated_at : null,
        ];
    }
}

