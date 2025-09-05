<?php

use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Member Routes
Route::middleware(['auth'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberController::class, 'dashboard'])->name('dashboard');
    Route::get('/events', [MemberController::class, 'browseEvents'])->name('events');
    Route::get('/events/{event}', [MemberController::class, 'eventDetails'])->name('event.details');
    Route::get('/profile', [MemberController::class, 'profile'])->name('profile');
    Route::post('/profile', [MemberController::class, 'updateProfile'])->name('profile.update');
});

// Event Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    
    // Event Registration Routes
    Route::post('/events/{event}/register', [MemberController::class, 'registerForEvent'])->name('events.register');
    Route::delete('/events/{event}/register', [MemberController::class, 'cancelRegistration'])->name('events.unregister');
});

// Organizer Routes
Route::middleware(['auth'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/dashboard', [EventController::class, 'organizerDashboard'])->name('dashboard');
    Route::get('/events', [EventController::class, 'myEvents'])->name('events');
    Route::get('/events/{event}/attendees', [EventController::class, 'attendees'])->name('event.attendees');
});

// Attendee Routes
Route::middleware(['auth'])->prefix('attendee')->name('attendee.')->group(function () {
    Route::get('/events', [MemberController::class, 'myRegisteredEvents'])->name('events');
    Route::get('/events/{event}', [MemberController::class, 'attendeeEventHub'])->name('event.hub');
});

// Remove the auth.php require line that was causing the error
// require __DIR__.'/auth.php';
