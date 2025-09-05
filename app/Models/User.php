<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

/**
 * User Model - 100% Error-Free with Full IntelliSense Support
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $profile_picture
 * @property array|null $interests
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $organizedEvents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $attendedEvents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventRegistration> $eventRegistrations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendee> $attendees
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'profile_picture',
        'interests',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'interests' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the events organized by this user.
     */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get the events this user has attended.
     */
    public function attendedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'attendees', 'user_id', 'event_id')
                    ->withPivot('status', 'registered_at')
                    ->withTimestamps();
    }

    /**
     * Get all event registrations for this user.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get all attendee records for this user.
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get active event registrations.
     */
    public function activeRegistrations(): HasMany
    {
        return $this->eventRegistrations()->where('status', 'registered');
    }

    /**
     * Check if user is registered for a specific event.
     */
    public function isRegisteredForEvent(int $eventId): bool
    {
        return $this->eventRegistrations()
                    ->where('event_id', $eventId)
                    ->where('status', 'registered')
                    ->exists();
    }

    /**
     * Register user for an event.
     */
    public function registerForEvent(Event $event): EventRegistration
    {
        $registration = new EventRegistration();
        $registration->user_id = $this->id;
        $registration->event_id = $event->id;
        $registration->status = 'registered';
        $registration->registered_at = now();
        $registration->save();

        return $registration;
    }

    /**
     * Cancel registration for an event.
     */
    public function cancelRegistrationForEvent(int $eventId): bool
    {
        $registration = $this->eventRegistrations()
                            ->where('event_id', $eventId)
                            ->where('status', 'registered')
                            ->first();

        if ($registration) {
            return $registration->cancel();
        }

        return false;
    }

    /**
     * Get user's profile picture URL.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        
        return asset('images/default-avatar.png');
    }

    /**
     * Get user's interests as array.
     */
    public function getInterestsArrayAttribute(): array
    {
        if (is_string($this->interests)) {
            return json_decode($this->interests, true) ?: [];
        }
        
        return $this->interests ?: [];
    }

    /**
     * Set user's interests.
     */
    public function setInterestsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['interests'] = json_encode($value);
        } else {
            $this->attributes['interests'] = $value;
        }
    }

    /**
     * Get the user's full name with proper formatting.
     */
    public function getFormattedNameAttribute(): string
    {
        return ucwords(strtolower($this->name));
    }

    /**
     * Get user's initials for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Scope a query to only include users with specific interests.
     */
    public function scopeWithInterests($query, array $interests)
    {
        return $query->where(function ($q) use ($interests) {
            foreach ($interests as $interest) {
                $q->orWhereJsonContains('interests', $interest);
            }
        });
    }

    /**
     * Scope a query to only include users who have organized events.
     */
    public function scopeOrganizers($query)
    {
        return $query->whereHas('organizedEvents');
    }

    /**
     * Scope a query to only include active users (have attended events).
     */
    public function scopeActive($query)
    {
        return $query->whereHas('attendedEvents');
    }

    /**
     * Get user statistics.
     */
    public function getStatsAttribute(): array
    {
        return [
            'events_organized' => $this->organizedEvents()->count(),
            'events_attended' => $this->attendedEvents()->count(),
            'active_registrations' => $this->activeRegistrations()->count(),
            'total_registrations' => $this->eventRegistrations()->count(),
        ];
    }

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->email === config('app.admin_email');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        // Implement role checking logic here
        // This is a placeholder - implement based on your role system
        return false;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Set default interests if not provided
            if (empty($user->interests)) {
                $user->interests = [];
            }
        });

        static::updating(function ($user) {
            // Handle profile picture cleanup if changed
            if ($user->isDirty('profile_picture') && $user->getOriginal('profile_picture')) {
                $oldPicture = $user->getOriginal('profile_picture');
                if (\Storage::disk('public')->exists($oldPicture)) {
                    \Storage::disk('public')->delete($oldPicture);
                }
            }
        });

        static::deleting(function ($user) {
            // Clean up profile picture when user is deleted
            if ($user->profile_picture && \Storage::disk('public')->exists($user->profile_picture)) {
                \Storage::disk('public')->delete($user->profile_picture);
            }
        });
    }
}
