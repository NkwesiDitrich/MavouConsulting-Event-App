<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slogan',
        'description',
        'event_type',
        'audience',
        'location', 
        'image_url',
        'start_time', 
        'end_time', 
        'organizer_id',
        'category_id',
        'max_attendees',
        'ticket_price',
        'is_free',
        'registration_deadline',
        'custom_questions',
        'allow_waitlist',
        'meeting_link'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_deadline' => 'datetime',
        'custom_questions' => 'array',
        'is_free' => 'boolean',
        'allow_waitlist' => 'boolean'
    ];

    // Relationships
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get the count of attendees for this event
     */
    public function getAttendeesCount(): int
    {
        return $this->attendees()->count();
    }

    /**
     * Get the image URL for display
     */
    public function getImageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/event_images/' . $this->image_path);
        }
        
        return $this->image_url ?: asset('images/default-event.jpg');
    }

    public function isSoldOut(): bool
    {
        // You'll implement ticket logic later
        return false; // Placeholder
    }

    // Alias for backward compatibility
    public function user(): BelongsTo
    {
        return $this->organizer();
    }

    // Accessor for slogan (if not provided, use description)
    public function getSloganAttribute($value)
    {
        return $value ?? $this->description;
    }
}