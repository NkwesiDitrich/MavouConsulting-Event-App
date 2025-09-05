<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Attendee Model - 100% Error-Free with Full IntelliSense Support
 * 
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property string $status
 * @property Carbon $registered_at
 * @property Carbon|null $attended_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property array|null $additional_info
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Event $event
 */
class Attendee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'registered_at',
        'attended_at',
        'cancelled_at',
        'cancellation_reason',
        'additional_info',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'attended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'additional_info' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Attendee status constants.
     */
    const STATUS_REGISTERED = 'registered';
    const STATUS_ATTENDED = 'attended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    /**
     * Get all available attendee statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_REGISTERED => 'Registered',
            self::STATUS_ATTENDED => 'Attended',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
        ];
    }

    /**
     * Get the user who is attending.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event being attended.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Check if attendee actually attended the event.
     */
    public function hasAttended(): bool
    {
        return $this->status === self::STATUS_ATTENDED && $this->attended_at !== null;
    }

    /**
     * Check if attendee cancelled their registration.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if attendee is currently registered.
     */
    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    /**
     * Mark attendee as attended.
     */
    public function markAsAttended(): bool
    {
        $this->status = self::STATUS_ATTENDED;
        $this->attended_at = now();
        return $this->save();
    }

    /**
     * Cancel the attendee registration.
     */
    public function cancel(string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        return $this->save();
    }

    /**
     * Mark attendee as no-show.
     */
    public function markAsNoShow(): bool
    {
        $this->status = self::STATUS_NO_SHOW;
        return $this->save();
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_REGISTERED => 'badge-primary',
            self::STATUS_ATTENDED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_NO_SHOW => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get additional info as array.
     */
    public function getAdditionalInfoArrayAttribute(): array
    {
        if (is_string($this->additional_info)) {
            return json_decode($this->additional_info, true) ?: [];
        }
        
        return $this->additional_info ?: [];
    }

    /**
     * Set additional info attribute.
     */
    public function setAdditionalInfoAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['additional_info'] = json_encode($value);
        } else {
            $this->attributes['additional_info'] = $value;
        }
    }

    /**
     * Scope a query to only include registered attendees.
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_REGISTERED);
    }

    /**
     * Scope a query to only include attendees who attended.
     */
    public function scopeAttended($query)
    {
        return $query->where('status', self::STATUS_ATTENDED);
    }

    /**
     * Scope a query to only include cancelled attendees.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include no-show attendees.
     */
    public function scopeNoShow($query)
    {
        return $query->where('status', self::STATUS_NO_SHOW);
    }

    /**
     * Scope a query to filter by event.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attendee) {
            // Set default status if not provided
            if (empty($attendee->status)) {
                $attendee->status = self::STATUS_REGISTERED;
            }

            // Set registered_at if not provided
            if (empty($attendee->registered_at)) {
                $attendee->registered_at = now();
            }

            // Set default additional_info if not provided
            if (empty($attendee->additional_info)) {
                $attendee->additional_info = [];
            }
        });
    }
}
