<?php

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\AttendeeEnhancedController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\OrganizerController;
use App\Http\Controllers\Api\PublicController;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
     ->middleware('auth:sanctum');

// Public routes (no authentication required)
Route::prefix('public')->group(function () {
    Route::get('/events', [PublicController::class, 'browseEvents']);
    Route::get('/events/search', [PublicController::class, 'searchEvents']);
    Route::get('/events/{event}', [PublicController::class, 'getEventDetails']);
    Route::get('/events/featured', [PublicController::class, 'getFeaturedEvents']);
    Route::get('/categories', [PublicController::class, 'getCategories']);
    Route::get('/categories/{category}/events', [PublicController::class, 'getEventsByCategory']);
});

// Public routes (original - for backward compatibility)
Route::apiResource('events', EventController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

// Event filtering and search routes (public)
Route::get('/events/search', [EventController::class, 'search']);
Route::get('/events/category/{category}', [EventController::class, 'byCategory']);
Route::get('/events/place/{place}', [EventController::class, 'byPlace']);

Route::middleware('auth:sanctum')->group(function () {
    // Member routes (base authenticated user functionality)
    Route::prefix('member')->group(function () {
        Route::get('/dashboard', [MemberController::class, 'dashboard']);
        Route::put('/interests', [MemberController::class, 'updateInterests']);
        Route::get('/recommended-events', [MemberController::class, 'getRecommendedEvents']);
    });

    // Organizer routes (event creator functionality)
    Route::prefix('organizer')->group(function () {
        Route::get('/dashboard', [OrganizerController::class, 'dashboard']);
        Route::put('/events/{event}/registration-settings', [OrganizerController::class, 'updateRegistrationSettings']);
        Route::post('/events/{event}/communications', [OrganizerController::class, 'sendCommunication']);
        Route::get('/events/{event}/analytics', [OrganizerController::class, 'getEventAnalytics']);
        Route::post('/events/{event}/post-event-actions', [OrganizerController::class, 'postEventActions']);
    });

    // Enhanced Attendee routes (event participant functionality)
    Route::prefix('attendee')->group(function () {
        Route::get('/events/{event}/dashboard', [AttendeeEnhancedController::class, 'getEventDashboard']);
        Route::get('/events/{event}/ticket', [AttendeeEnhancedController::class, 'getTicket']);
        Route::get('/events/{event}/networking', [AttendeeEnhancedController::class, 'getNetworking']);
        Route::get('/events/{event}/tools', [AttendeeEnhancedController::class, 'getEventTools']);
        Route::post('/events/{event}/feedback', [AttendeeEnhancedController::class, 'submitFeedback']);
        Route::post('/events/{event}/check-in', [AttendeeEnhancedController::class, 'selfCheckIn']);
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
    
    // User events and attendances
    Route::get('/my-events', [ProfileController::class, 'myEvents']);
    Route::get('/my-attendances', [ProfileController::class, 'myAttendances']);

    // Protected event routes
    Route::apiResource('events', EventController::class)->except(['index', 'show']);
    Route::get('/my-organized-events', [EventController::class, 'myEvents']);
    
    // Admin/Organizer only routes
    Route::get('/events/attended-by/{userId}', [EventController::class, 'attendedByUser']);

    // Attendee routes
    Route::apiResource('events.attendees', AttendeeController::class);
    Route::post('/events/{event}/attendees/{attendee}/checkin', [AttendeeController::class, 'checkIn']);
    Route::get('/events/{event}/attendees/{attendee}/info', [AttendeeController::class, 'getAttendeeInfo']);

    // Protected category routes
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
});

// Legacy routes (for backward compatibility)
Route::post('/register', [RegisterController::class, 'register']);
Route::apiResource('events.attendees', AttendeeController::class)
    ->scoped()->except(['update']);
// Additional event filtering routes (public)
Route::get('/events/type/{eventType}', [EventController::class, 'byEventType']);
Route::get('/events/audience/{audience}', [EventController::class, 'byAudience']);

Route::middleware('auth:sanctum')->group(function () {
    // User event management routes
    Route::get('/my-upcoming-events', [EventController::class, 'upcomingEvents']);
    Route::get('/my-past-events', [EventController::class, 'pastEvents']);
});

