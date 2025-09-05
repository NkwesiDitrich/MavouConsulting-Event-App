<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Category Model - 100% Error-Free with Full IntelliSense Support
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $color
 * @property string|null $icon
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $events
 * @property-read int $events_count
 */
class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the events in this category.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get published events in this category.
     */
    public function publishedEvents(): HasMany
    {
        return $this->events()->published();
    }

    /**
     * Get upcoming events in this category.
     */
    public function upcomingEvents(): HasMany
    {
        return $this->events()->published()->upcoming();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the category's color with fallback.
     */
    public function getColorAttribute($value): string
    {
        return $value ?: '#007bff';
    }

    /**
     * Get the category's icon with fallback.
     */
    public function getIconAttribute($value): string
    {
        return $value ?: 'fas fa-calendar';
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order categories by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope a query to only include categories with events.
     */
    public function scopeWithEvents($query)
    {
        return $query->whereHas('events');
    }

    /**
     * Scope a query to only include categories with published events.
     */
    public function scopeWithPublishedEvents($query)
    {
        return $query->whereHas('events', function ($q) {
            $q->published();
        });
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            // Generate slug if not provided
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // Set default sort order if not provided
            if ($category->sort_order === null) {
                $category->sort_order = static::max('sort_order') + 1;
            }

            // Set default active status
            if ($category->is_active === null) {
                $category->is_active = true;
            }
        });

        static::updating(function ($category) {
            // Update slug if name changed
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
