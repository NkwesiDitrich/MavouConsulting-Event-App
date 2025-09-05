<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * EventRegistration Model
 * 
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property string $status
 * @property Carbon|null $registered_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Event $event
 */
class EventRegistration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'attendees';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'registered_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that owns the registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope a query to only include active registrations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'registered');
    }

    /**
     * Check if the registration is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'registered';
    }

    /**
     * Cancel the registration.
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Activate the registration.
     */
    public function activate(): bool
    {
        $this->status = 'registered';
        $this->registered_at = now();
        return $this->save();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            if (empty($registration->status)) {
                $registration->status = 'registered';
            }
            if (empty($registration->registered_at)) {
                $registration->registered_at = now();
            }
        });
    }
}
