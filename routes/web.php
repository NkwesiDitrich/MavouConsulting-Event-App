<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Web authentication POST routes
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials, $request->has('remember'))) {
        $request->session()->regenerate();
        
        // Redirect based on user role
        if (Auth::user()->role === 'organizer') {
            return redirect()->intended('/organizer/dashboard');
        } elseif (Auth::user()->role === 'admin') {
            return redirect()->intended('/admin/dashboard');
        }
        
        return redirect()->intended('/member/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
})->name('login.post');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'role' => 'sometimes|in:member,organizer'
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'member',
    ]);

    Auth::login($user);

    // Redirect based on user role
    if ($user->role === 'organizer') {
        return redirect('/organizer/dashboard');
    }
    
    return redirect('/member/dashboard');
})->name('register.post');

// Home page - redirect authenticated users to appropriate dashboard
Route::get('/', function () {
    if (Auth::check()) {
        // Redirect to appropriate dashboard based on user role
        return redirect()->route('member.dashboard');
    }
    return view('welcome');
})->name('home');

// Authentication routes - redirect authenticated users away
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('member.dashboard');
    }
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('member.dashboard');
    }
    return view('auth.register');
})->name('register');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Public event browsing
Route::get('/events/browse', function () {
    return view('events.browse');
})->name('events.browse');

Route::get('/events/{id}', function ($id) {
    return view('events.show', compact('id'));
})->name('events.show');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Member routes
    Route::get('/member/dashboard', function () {
        return view('member.dashboard');
    })->name('member.dashboard');
    
    // Organizer routes
    Route::get('/organizer/dashboard', function () {
        return view('organizer.dashboard');
    })->name('organizer.dashboard');
    
    Route::get('/events/create', function () {
        return view('events.create');
    })->name('events.create');
    
    Route::get('/events/{id}/edit', function ($id) {
        return view('events.edit', compact('id'));
    })->name('events.edit');
    
    Route::get('/events/{id}/attendees', function ($id) {
        return view('events.attendees', compact('id'));
    })->name('events.attendees');
    
    // Attendee routes
    Route::get('/attendee/events/{id}/dashboard', function ($id) {
        return view('attendee.dashboard', compact('id'));
    })->name('attendee.dashboard');
    
    // Profile routes
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');
    
    Route::get('/profile/events', function () {
        return view('profile.events');
    })->name('profile.events');
    
    Route::get('/profile/upgrade-to-organizer', function () {
        return view('profile.upgrade');
    })->name('profile.upgrade');
    
    // Admin routes (if needed)
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
});

// Web API routes (session-based authentication)
use App\Http\Controllers\Web\MemberController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\ProfileController;

// Public event routes (no authentication required)
Route::prefix('web-api')->group(function () {
    Route::get('/events', [EventController::class, 'browseEvents']);
    Route::get('/events/search', [EventController::class, 'searchEvents']);
    Route::get('/events/{id}', [EventController::class, 'getEventDetails']); // FIXED: This route was missing
    Route::get('/events/{id}/details', [EventController::class, 'getEventDetails']); // FIXED: Alternative route for event details
    Route::get('/events/featured', [EventController::class, 'getFeaturedEvents']);
    Route::get('/categories', [EventController::class, 'getCategories']);
});

// Protected web API routes (require web session authentication)
Route::middleware(['auth'])->prefix('web-api')->group(function () {
    // Member routes
    Route::get('/member/dashboard', [MemberController::class, 'dashboard']);
    Route::put('/member/interests', [MemberController::class, 'updateInterests']);
    Route::get('/member/recommended-events', [MemberController::class, 'getRecommendedEvents']);
    
    // Event registration - FIXED: Added unregister route
    Route::post('/events/{id}/register', [EventController::class, 'registerForEvent']);
    Route::delete('/events/{id}/register', [EventController::class, 'unregisterFromEvent']); // FIXED: Added unregister
    Route::delete('/events/{id}/unregister', [EventController::class, 'unregisterFromEvent']); // FIXED: Alternative unregister route
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::get('/profile/events', [ProfileController::class, 'myEvents']);
    Route::get('/profile/stats', [ProfileController::class, 'getStats']); // FIXED: Added stats route
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});
