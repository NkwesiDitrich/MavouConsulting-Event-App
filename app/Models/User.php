<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture',
        'events_attended',
        'interests',
        'bio',
        'linkedin_url',
        'twitter_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'interests' => 'array'
        ];
    }

    /**
     * Update user profile
     */
    public function updateProfile(array $data): bool
    {
        // Remove password confirmation if present
        unset($data['password_confirmation']);
        unset($data['current_password']);
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->update($data);
    }

    /**
     * Delete user account with all related data
     */
    public function deleteAccount(): bool
    {
        // Delete user's events first to avoid foreign key constraints
        $this->organizedEvents()->delete();
        
        // Delete from event attendees
        DB::table('attendees')->where('user_id', $this->id)->delete();
        
        // Delete user's API tokens
        $this->tokens()->delete();
        
        return $this->delete();
    }

    /**
     * Get events organized by this user
     */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get events that the user is attending
     */
    public function attendingEvents()
    {
        return $this->belongsToMany(Event::class, 'attendees', 'user_id', 'event_id')
                    ->withTimestamps()
                    ->withPivot('checked_in');
    }

    /**
     * Alias for attendingEvents
     */
    public function getAttendances()
    {
        return $this->attendingEvents();
    }

    /**
     * Get the count of events attended by this user
     */
    public function getEventsAttendedCount(): int
    {
        return $this->events_attended ?? $this->attendingEvents()->count();
    }

    /**
     * Update the events attended count
     */
    public function updateEventsAttendedCount(): void
    {
        $this->events_attended = $this->attendingEvents()->count();
        $this->save();
    }

    /**
     * Get profile picture URL or default avatar
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/profile_pictures/' . $this->profile_picture);
        }
        
        // Generate unique avatar based on user ID to prevent duplicates
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . 
               '&background=' . substr(md5($this->id), 0, 6) . '&color=fff&size=200';
    }

    /**
     * Check if user is globally an organizer (has created events)
     */
    public function isOrganizer(): bool
    {
        return $this->organizedEvents()->exists();
    }

    /**
     * Check if user is organizer for a specific event
     */
    public function isEventOrganizer(Event $event): bool
    {
        return $event->organizer_id === $this->id;
    }

    /**
     * Check if user is attendee for a specific event
     */
    public function isEventAttendee(Event $event): bool
    {
        return $this->attendingEvents()->where('event_id', $event->id)->exists();
    }

    /**
     * Get user's role for a specific event
     */
    public function getEventRole(Event $event): string
    {
        if ($this->isEventOrganizer($event)) {
            return 'organizer';
        }
        
        if ($this->isEventAttendee($event)) {
            return 'attendee';
        }
        
        return 'member'; // Default global role
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a member (global role)
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Check if user can view attendee filters (admin or event organizer only)
     */
    public function canViewAttendeeFilters(): bool
    {
        return $this->role === 'admin' || $this->organizedEvents()->exists();
    }

    /**
     * Boot method to set default role
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if (!$user->role) {
                $user->role = 'member'; // Default role is always member
            }
        });
    }
}
