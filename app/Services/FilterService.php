<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    /**
     * Apply filters to the event query
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['category']) && !empty($filters['category'])) {
            $query = $this->byCategory($query, $filters['category']);
        }

        if (isset($filters['place']) && !empty($filters['place'])) {
            $query = $this->byPlace($query, $filters['place']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query = $this->bySearch($query, $filters['search']);
        }

        if (isset($filters['attendee_id']) && !empty($filters['attendee_id'])) {
            $query = $this->byAttendee($query, $filters['attendee_id']);
        }

        return $query;
    }

    /**
     * Filter events by category
     */
    public function byCategory(Builder $query, string $category): Builder
    {
        return $query->whereHas('category', function ($q) use ($category) {
            $q->where('name', 'like', '%' . $category . '%');
        });
    }

    /**
     * Filter events by place/location
     */
    public function byPlace(Builder $query, string $place): Builder
    {
        return $query->where(function ($q) use ($place) {
            $q->where('place', 'like', '%' . $place . '%')
              ->orWhere('location', 'like', '%' . $place . '%');
        });
    }

    /**
     * Search events by ID, title, name, or slogan
     */
    public function bySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('id', 'like', '%' . $search . '%')
              ->orWhere('name', 'like', '%' . $search . '%')
              ->orWhere('title', 'like', '%' . $search . '%')
              ->orWhere('slogan', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    /**
     * Filter events attended by a specific user
     * This should only be accessible to admins and organizers
     */
    public function byAttendee(Builder $query, int $attendeeId): Builder
    {
        return $query->whereHas('attendees', function ($q) use ($attendeeId) {
            $q->where('user_id', $attendeeId);
        });
    }

    /**
     * Get events with attendee count
     */
    public function withAttendeeCount(Builder $query): Builder
    {
        return $query->withCount('attendees');
    }

    /**
     * Filter events by organizer
     */
    public function byOrganizer(Builder $query, int $organizerId): Builder
    {
        return $query->where('organizer_id', $organizerId);
    }

    /**
     * Filter events by date range
     */
    public function byDateRange(Builder $query, ?string $startDate = null, ?string $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('end_time', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Filter events by event type
     */
    public function byEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Filter events by audience
     */
    public function byAudience(Builder $query, string $audience): Builder
    {
        return $query->where('audience', $audience);
    }

    /**
     * Filter upcoming events for a user
     */
    public function upcomingEventsForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('attendees', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('start_time', '>', now());
    }

    /**
     * Filter past events for a user
     */
    public function pastEventsForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('attendees', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('end_time', '<', now());
    }
}



    