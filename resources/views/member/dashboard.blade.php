@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome back, <span id="userName">Member</span>!</h2>
                            <p class="mb-0 opacity-75">Discover amazing events and connect with like-minded people</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex justify-content-md-end justify-content-center">
                                <img id="userAvatar" src="" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-2x mb-3"></i>
                    <h3 class="mb-1" id="totalEventsAttended">0</h3>
                    <p class="mb-0">Events Attended</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-3"></i>
                    <h3 class="mb-1" id="upcomingRegistrations">0</h3>
                    <p class="mb-0">Upcoming Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-3"></i>
                    <h3 class="mb-1" id="recommendedCount">0</h3>
                    <p class="mb-0">Recommended</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h5>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('events.browse') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search me-2"></i>
                                Browse Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-success w-100" onclick="updateInterests()">
                                <i class="fas fa-heart me-2"></i>
                                Update Interests
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-user me-2"></i>
                                Edit Profile
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-secondary w-100" onclick="createEvent()">
                                <i class="fas fa-plus me-2"></i>
                                Create Event
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="recommended-tab" data-bs-toggle="tab" data-bs-target="#recommended" type="button" role="tab">
                        <i class="fas fa-star me-2"></i>
                        Recommended Events
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="registered-tab" data-bs-toggle="tab" data-bs-target="#registered" type="button" role="tab">
                        <i class="fas fa-calendar-check me-2"></i>
                        My Registered Events
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                        <i class="fas fa-clock me-2"></i>
                        Upcoming Events
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="dashboardTabsContent">
                <!-- Recommended Events Tab -->
                <div class="tab-pane fade show active" id="recommended" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Events You Might Like</h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshRecommendations()">
                                    <i class="fas fa-refresh me-1"></i>
                                    Refresh
                                </button>
                            </div>
                            <div class="row" id="recommendedEvents">
                                <!-- Recommended events will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registered Events Tab -->
                <div class="tab-pane fade" id="registered" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">Your Registered Events</h5>
                            <div class="row" id="registeredEvents">
                                <!-- Registered events will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events Tab -->
                <div class="tab-pane fade" id="upcoming" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">All Upcoming Events</h5>
                            <div class="row" id="upcomingEvents">
                                <!-- Upcoming events will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Interests Modal -->
<div class="modal fade" id="interestsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Your Interests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Select topics you're interested in to get better event recommendations.</p>
                <div class="row" id="interestsForm">
                    <!-- Interest checkboxes will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveInterests()">Save Interests</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let dashboardData = {};
let userInterests = [];

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
});

async function loadDashboard() {
    try {
        // Add cache-busting parameter to prevent cached responses
        const cacheBuster = new Date().getTime();
        const response = await axios.get(`/web-api/member/dashboard?t=${cacheBuster}`);
        dashboardData = response.data;
        
        // Update user info
        document.getElementById('userName').textContent = dashboardData.user.name;
        document.getElementById('userAvatar').src = dashboardData.user.profile_picture;
        
        // Update stats
        document.getElementById('totalEventsAttended').textContent = dashboardData.stats.total_events_attended;
        document.getElementById('upcomingRegistrations').textContent = dashboardData.stats.upcoming_registrations;
        document.getElementById('recommendedCount').textContent = dashboardData.recommended_events.length;
        
        // Store user interests
        userInterests = dashboardData.user.interests || [];
        
        // Load events
        loadRecommendedEvents();
        loadRegisteredEvents();
        loadUpcomingEvents();
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showAlert('Failed to load dashboard data', 'danger');
    }
}

function loadRecommendedEvents() {
    const container = document.getElementById('recommendedEvents');
    container.innerHTML = '';
    
    if (dashboardData.recommended_events.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted text-center">No recommended events at the moment. Update your interests to get better recommendations!</p></div>';
        return;
    }
    
    dashboardData.recommended_events.forEach(event => {
        container.appendChild(createEventCard(event, 'recommended'));
    });
}

function loadRegisteredEvents() {
    const container = document.getElementById('registeredEvents');
    container.innerHTML = '';
    
    if (dashboardData.registered_events.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted text-center">You haven\'t registered for any events yet. <a href="/events/browse">Browse events</a> to get started!</p></div>';
        return;
    }
    
    dashboardData.registered_events.forEach(event => {
        container.appendChild(createEventCard(event, 'registered'));
    });
}

function loadUpcomingEvents() {
    const container = document.getElementById('upcomingEvents');
    container.innerHTML = '';
    
    if (dashboardData.upcoming_events.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted text-center">No upcoming events available.</p></div>';
        return;
    }
    
    dashboardData.upcoming_events.forEach(event => {
        container.appendChild(createEventCard(event, 'upcoming'));
    });
}

function createEventCard(event, type) {
    const col = document.createElement('div');
    col.className = 'col-md-6 col-lg-4 mb-3';
    
    const isRegistered = type === 'registered';
    const actionButton = isRegistered 
        ? `<button class="btn btn-success btn-sm" onclick="viewEventDashboard(${event.id})">
             <i class="fas fa-tachometer-alt me-1"></i>
             View Dashboard
           </button>`
        : `<button class="btn btn-primary btn-sm" onclick="registerForEvent(${event.id})">
             <i class="fas fa-calendar-plus me-1"></i>
             Register
           </button>`;
    
    col.innerHTML = `
        <div class="card event-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">${event.name}</h6>
                    <span class="badge bg-primary">${event.category?.name || 'General'}</span>
                </div>
                <p class="card-text text-muted small mb-2">${event.description?.substring(0, 100) || ''}${event.description?.length > 100 ? '...' : ''}</p>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i>
                        ${formatDate(event.start_time)}
                    </small>
                </div>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        ${event.location || 'Online'}
                    </small>
                </div>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i>
                        by ${event.organizer?.name || 'Unknown'}
                    </small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    ${actionButton}
                    <button class="btn btn-outline-secondary btn-sm" onclick="viewEventDetails(${event.id})">
                        <i class="fas fa-info-circle me-1"></i>
                        Details
                    </button>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

function updateInterests() {
    // Load interests form
    const form = document.getElementById('interestsForm');
    const interests = [
        'Technology', 'Business', 'Networking', 'Education', 
        'Arts & Culture', 'Sports', 'Health & Wellness', 'Food & Drink',
        'Music', 'Photography', 'Travel', 'Science'
    ];
    
    form.innerHTML = '';
    interests.forEach((interest, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-6 mb-2';
        
        const checked = userInterests.includes(interest) ? 'checked' : '';
        col.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${interest}" id="interest-${index}" ${checked}>
                <label class="form-check-label" for="interest-${index}">
                    ${interest}
                </label>
            </div>
        `;
        form.appendChild(col);
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('interestsModal'));
    modal.show();
}

async function saveInterests() {
    const selectedInterests = [];
    document.querySelectorAll('#interestsForm input[type="checkbox"]:checked').forEach(checkbox => {
        selectedInterests.push(checkbox.value);
    });
    
    try {
        const response = await axios.put('/web-api/member/interests', {
            interests: selectedInterests
        });
        
        userInterests = selectedInterests;
        showAlert('Interests updated successfully!', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('interestsModal'));
        modal.hide();
        
        // Refresh recommendations
        refreshRecommendations();
        
    } catch (error) {
        console.error('Error updating interests:', error);
        showAlert('Failed to update interests', 'danger');
    }
}

async function refreshRecommendations() {
    try {
        const response = await axios.get('/web-api/member/recommended-events');
        dashboardData.recommended_events = response.data.recommended_events;
        
        document.getElementById('recommendedCount').textContent = dashboardData.recommended_events.length;
        loadRecommendedEvents();
        
        showAlert('Recommendations refreshed!', 'success');
    } catch (error) {
        console.error('Error refreshing recommendations:', error);
        showAlert('Failed to refresh recommendations', 'danger');
    }
}