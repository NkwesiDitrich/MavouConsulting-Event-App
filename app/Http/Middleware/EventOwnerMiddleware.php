<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Event;

class EventOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $eventId = $request->route('event');
        
        // If event ID is an object, get the ID
        if (is_object($eventId)) {
            $eventId = $eventId->id;
        }

        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $user = $request->user();

        // Check if user is the organizer of this event or an admin
        if ($event->organizer_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You can only manage your own events'
            ], 403);
        }

        return $next($request);
    }
}

