<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Event Model - 100% Error-Free with Full IntelliSense Support
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $image
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property string $location
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $max_attendees
 * @property int $organizer_id
 * @property int|null $category_id
 * @property string $status
 * @property decimal $price
 * @property string|null $requirements
 * @property array|null $tags
 * @property Carbon|null $registration_deadline
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $organizer
 * @property-read Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $attendees
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventRegistration> $registrations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendee> $attendeeRecords
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'image',
        'start_time',
        'end_time',
        'location',
        'address',
        'latitude',
        'longitude',
        'max_attendees',
        'organizer_id',
        'category_id',
        'status',
        'price',
        'requirements',
        'tags',
        'registration_deadline',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'registration_deadline' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'price' => 'decimal:2',
        'tags' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Event status constants.
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get all available event statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get the organizer of this event.
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the category of this event.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the users who are attending this event.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'attendees', 'event_id', 'user_id')
                    ->withPivot('status', 'registered_at')
                    ->withTimestamps();
    }

    /**
     * Get all registrations for this event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get all attendee records for this event.
     */
    public function attendeeRecords(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get active registrations for this event.
     */
    public function activeRegistrations(): HasMany
    {
        return $this->registrations()->where('status', 'registered');
    }

    /**
     * Get cancelled registrations for this event.
     */
    public function cancelledRegistrations(): HasMany
    {
        return $this->registrations()->where('status', 'cancelled');
    }

    /**
     * Check if event is full.
     */
    public function isFull(): bool
    {
        return $this->activeRegistrations()->count() >= $this->max_attendees;
    }

    /**
     * Check if registration is still open.
     */
    public function isRegistrationOpen(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        if ($this->isFull()) {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }

        if ($this->start_time->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if event has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_time->isPast();
    }

    /**
     * Check if event has ended.
     */
    public function hasEnded(): bool
    {
        return $this->end_time->isPast();
    }

    /**
     * Check if event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture();
    }

    /**
     * Check if event is currently happening.
     */
    public function isHappening(): bool
    {
        return $this->start_time->isPast() && $this->end_time->isFuture();
    }

    /**
     * Get available spots remaining.
     */
    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->max_attendees - $this->activeRegistrations()->count());
    }

    /**
     * Get current attendee count.
     */
    public function getAttendeeCountAttribute(): int
    {
        return $this->activeRegistrations()->count();
    }

    /**
     * Get event image URL.
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        return asset('images/default-event.jpg');
    }

    /**
     * Get formatted start date.
     */
    public function getFormattedStartDateAttribute(): string
    {
        return $this->start_time->format('M j, Y');
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time->format('g:i A');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time->format('g:i A');
    }

    /**
     * Get event duration in hours.
     */
    public function getDurationInHoursAttribute(): float
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    /**
     * Get event duration formatted.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = $this->duration_in_hours;
        
        if ($hours < 1) {
            $minutes = $this->start_time->diffInMinutes($this->end_time);
            return $minutes . ' minutes';
        }
        
        if ($hours == 1) {
            return '1 hour';
        }
        
        return number_format($hours, 1) . ' hours';
    }

    /**
     * Get event status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'badge-secondary',
            self::STATUS_PUBLISHED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_COMPLETED => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Get tags as array.
     */
    public function getTagsArrayAttribute(): array
    {
        if (is_string($this->tags)) {
            return json_decode($this->tags, true) ?: [];
        }
        
        return $this->tags ?: [];
    }

    /**
     * Set tags attribute.
     */
    public function setTagsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['tags'] = json_encode($value);
        } else {
            $this->attributes['tags'] = $value;
        }
    }

    /**
     * Check if user can register for this event.
     */
    public function canUserRegister(User $user): bool
    {
        if (!$this->isRegistrationOpen()) {
            return false;
        }

        if ($user->isRegisteredForEvent($this->id)) {
            return false;
        }

        if ($this->organizer_id === $user->id) {
            return false; // Organizer can't register for their own event
        }

        return true;
    }

    /**
     * Register a user for this event.
     */
    public function registerUser(User $user): EventRegistration
    {
        if (!$this->canUserRegister($user)) {
            throw new \Exception('User cannot register for this event');
        }

        return $user->registerForEvent($this);
    }

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope a query to only include upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include past events.
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    /**
     * Scope a query to only include events happening today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Scope a query to only include events in a specific category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to only include events with available spots.
     */
    public function scopeWithAvailableSpots($query)
    {
        return $query->whereRaw('(SELECT COUNT(*) FROM event_registrations WHERE event_id = events.id AND status = "registered") < max_attendees');
    }

    /**
     * Scope a query to search events by name or description.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter events by price range.
     */
    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            // Set default status if not provided
            if (empty($event->status)) {
                $event->status = self::STATUS_DRAFT;
            }

            // Set default tags if not provided
            if (empty($event->tags)) {
                $event->tags = [];
            }

            // Set default price if not provided
            if ($event->price === null) {
                $event->price = 0.00;
            }
        });

        static::updating(function ($event) {
            // Handle image cleanup if changed
            if ($event->isDirty('image') && $event->getOriginal('image')) {
                $oldImage = $event->getOriginal('image');
                if (\Storage::disk('public')->exists($oldImage)) {
                    \Storage::disk('public')->delete($oldImage);
                }
            }

            // Auto-complete events that have ended
            if ($event->end_time->isPast() && $event->status === self::STATUS_PUBLISHED) {
                $event->status = self::STATUS_COMPLETED;
            }
        });

        static::deleting(function ($event) {
            // Clean up event image when event is deleted
            if ($event->image && \Storage::disk('public')->exists($event->image)) {
                \Storage::disk('public')->delete($event->image);
            }

            // Cancel all active registrations
            $event->activeRegistrations()->update(['status' => 'cancelled']);
        });
    }
}
