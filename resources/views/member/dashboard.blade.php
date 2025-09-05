@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Welcome back, <span id="userName">Loading...</span>!</h2>
                            <p class="mb-0 opacity-75">Here's what's happening with your events</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <img id="userAvatar" src="" alt="Profile" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stats-card text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="eventsAttended">0</h3>
                            <p class="mb-0">Events Attended</p>
                        </div>
                        <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="upcomingRegistrations">0</h3>
                            <p class="mb-0">Upcoming Events</p>
                        </div>
                        <i class="fas fa-calendar-plus fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="eventsOrganized">0</h3>
                            <p class="mb-0">Events Organized</p>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">4.8</h3>
                            <p class="mb-0">Average Rating</p>
                        </div>
                        <i class="fas fa-star fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Registered Events -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Your Upcoming Events
                    </h5>
                    <span class="badge bg-primary" id="registeredEventsCount">0</span>
                </div>
                <div class="card-body">
                    <div id="registeredEventsContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading your events...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Events -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Recommended for You
                    </h5>
                </div>
                <div class="card-body">
                    <div id="recommendedEventsContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Finding events for you...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('events.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-search me-2"></i>
                                Browse Events
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('member.profile') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-user-edit me-2"></i>
                                Edit Profile
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-info w-100" onclick="showInterestsModal()">
                                <i class="fas fa-heart me-2"></i>
                                Update Interests
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('events.create') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-plus me-2"></i>
                                Create Event
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading event details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="eventActionBtn" style="display: none;">Register</button>
            </div>
        </div>
    </div>
</div>

<!-- Interests Modal -->
<div class="modal fade" id="interestsModal" tabindex="-1" aria-labelledby="interestsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="interestsModalLabel">Update Your Interests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="interestsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select your interests:</label>
                        <div id="interestsCheckboxes">
                            <!-- Interests will be loaded here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateInterests()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Global variables for dashboard functionality
let currentUser = null;
let dashboardData = null;

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

/**
 * Load dashboard data from API - FIXED API ENDPOINT
 */
function loadDashboardData() {
    fetch('/web-api/member/dashboard', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            dashboardData = data;
            currentUser = data.user;
            updateDashboardUI(data);
        } else {
            showError('Failed to load dashboard data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Dashboard loading error:', error);
        showError('Failed to load dashboard data. Please refresh the page.');
    });
}

/**
 * Update dashboard UI with loaded data
 */
function updateDashboardUI(data) {
    try {
        // Update user info
        document.getElementById('userName').textContent = data.user.name || 'User';
        document.getElementById('userAvatar').src = data.user.profile_picture || '/images/default-avatar.png';

        // Update stats
        document.getElementById('eventsAttended').textContent = data.stats.total_events_attended || 0;
        document.getElementById('upcomingRegistrations').textContent = data.stats.upcoming_registrations || 0;
        document.getElementById('eventsOrganized').textContent = data.stats.events_organized || 0;

        // Update registered events
        updateRegisteredEvents(data.registered_events || []);

        // Update recommended events
        updateRecommendedEvents(data.recommended_events || []);

        console.log('Dashboard updated successfully');
    } catch (error) {
        console.error('Error updating dashboard UI:', error);
        showError('Error displaying dashboard data');
    }
}

/**
 * Update registered events section
 */
function updateRegisteredEvents(events) {
    const container = document.getElementById('registeredEventsContainer');
    const countBadge = document.getElementById('registeredEventsCount');
    
    countBadge.textContent = events.length;

    if (events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No upcoming events</h6>
                <p class="text-muted mb-3">You haven't registered for any upcoming events yet.</p>
                <a href="/events" class="btn btn-primary">Browse Events</a>
            </div>
        `;
        return;
    }

    let eventsHtml = '';
    events.forEach(event => {
        const startDate = new Date(event.start_time);
        const formattedDate = startDate.toLocaleDateString('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const formattedTime = startDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        eventsHtml += `
            <div class="event-card mb-3 p-3 border rounded">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">${escapeHtml(event.name)}</h6>
                        <p class="text-muted mb-1">
                            <i class="fas fa-calendar me-1"></i>
                            ${formattedDate} at ${formattedTime}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            ${escapeHtml(event.location || 'Location TBD')}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <button class="btn btn-outline-primary btn-sm" onclick="showEventDetails(${event.id})">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = eventsHtml;
}

/**
 * Update recommended events section
 */
function updateRecommendedEvents(events) {
    const container = document.getElementById('recommendedEventsContainer');

    if (events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-lightbulb fa-2x text-muted mb-3"></i>
                <p class="text-muted">No recommendations available</p>
            </div>
        `;
        return;
    }

    let eventsHtml = '';
    events.slice(0, 3).forEach(event => {
        const startDate = new Date(event.start_time);
        const formattedDate = startDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });

        eventsHtml += `
            <div class="recommended-event mb-3 p-2 border rounded">
                <h6 class="mb-1 small">${escapeHtml(event.name)}</h6>
                <p class="text-muted mb-1 small">
                    <i class="fas fa-calendar me-1"></i>
                    ${formattedDate}
                </p>
                <button class="btn btn-outline-primary btn-sm w-100" onclick="showEventDetails(${event.id})">
                    View Details
                </button>
            </div>
        `;
    });

    container.innerHTML = eventsHtml;
}

/**
 * Show event details modal - FIXED API ENDPOINT
 */
function showEventDetails(eventId) {
    const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
    const content = document.getElementById('eventDetailsContent');
    const actionBtn = document.getElementById('eventActionBtn');

    // Show loading state
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading event details...</p>
        </div>
    `;
    actionBtn.style.display = 'none';
    modal.show();

    // Fetch event details using CORRECT API endpoint
    fetch(`/web-api/member/event-details/${eventId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayEventDetails(data.event);
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load event details: ${data.message || 'Unknown error'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading event details:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Failed to load event details. Please try again.
            </div>
        `;
    });
}

/**
 * Display event details in modal
 */
function displayEventDetails(event) {
    const content = document.getElementById('eventDetailsContent');
    const actionBtn = document.getElementById('eventActionBtn');

    const startDate = new Date(event.start_time);
    const endDate = new Date(event.end_time);
    const formattedStartDate = startDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    const formattedStartTime = startDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
    const formattedEndTime = endDate.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });

    content.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <img src="${event.image_url || '/images/default-event.jpg'}" 
                     alt="${escapeHtml(event.name)}" 
                     class="img-fluid rounded">
            </div>
            <div class="col-md-8">
                <h4>${escapeHtml(event.name)}</h4>
                <p class="text-muted mb-2">${escapeHtml(event.slogan || '')}</p>
                
                <div class="mb-3">
                    <h6>Event Details</h6>
                    <p><strong>Category:</strong> ${escapeHtml(event.category)}</p>
                    <p><strong>Type:</strong> ${escapeHtml(event.event_type || 'General')}</p>
                    <p><strong>Audience:</strong> ${escapeHtml(event.audience || 'All')}</p>
                    <p><strong>Organizer:</strong> ${escapeHtml(event.organizer_name)}</p>
                </div>

                <div class="mb-3">
                    <h6>Date & Time</h6>
                    <p><strong>Date:</strong> ${formattedStartDate}</p>
                    <p><strong>Time:</strong> ${formattedStartTime} - ${formattedEndTime}</p>
                    <p><strong>Location:</strong> ${escapeHtml(event.location || 'TBD')}</p>
                </div>

                <div class="mb-3">
                    <h6>Description</h6>
                    <p>${escapeHtml(event.description || 'No description available.')}</p>
                </div>

                <div class="mb-3">
                    <p><strong>Attendees:</strong> ${event.attendee_count || 0} registered</p>
                    ${event.is_registered ? 
                        '<span class="badge bg-success">You are registered</span>' : 
                        '<span class="badge bg-secondary">Not registered</span>'
                    }
                </div>
            </div>
        </div>
    `;

    // Show appropriate action button
    if (!event.is_organizer) {
        actionBtn.style.display = 'inline-block';
        if (event.is_registered) {
            actionBtn.textContent = 'Cancel Registration';
            actionBtn.className = 'btn btn-danger';
            actionBtn.onclick = () => cancelRegistration(event.id);
        } else {
            actionBtn.textContent = 'Register for Event';
            actionBtn.className = 'btn btn-primary';
            actionBtn.onclick = () => registerForEvent(event.id);
        }
    } else {
        actionBtn.style.display = 'none';
    }
}

/**
 * Register for event
 */
function registerForEvent(eventId) {
    const actionBtn = document.getElementById('eventActionBtn');
    const originalText = actionBtn.textContent;
    
    actionBtn.disabled = true;
    actionBtn.textContent = 'Registering...';

    fetch('/web-api/member/register-event', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Successfully registered for event!');
            bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
            loadDashboardData(); // Refresh dashboard
        } else {
            showError(data.message || 'Failed to register for event');
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showError('Failed to register for event. Please try again.');
    })
    .finally(() => {
        actionBtn.disabled = false;
        actionBtn.textContent = originalText;
    });
}

/**
 * Cancel event registration
 */
function cancelRegistration(eventId) {
    if (!confirm('Are you sure you want to cancel your registration for this event?')) {
        return;
    }

    const actionBtn = document.getElementById('eventActionBtn');
    const originalText = actionBtn.textContent;
    
    actionBtn.disabled = true;
    actionBtn.textContent = 'Cancelling...';

    fetch('/web-api/member/cancel-registration', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ event_id: eventId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Registration cancelled successfully!');
            bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
            loadDashboardData(); // Refresh dashboard
        } else {
            showError(data.message || 'Failed to cancel registration');
        }
    })
    .catch(error => {
        console.error('Cancellation error:', error);
        showError('Failed to cancel registration. Please try again.');
    })
    .finally(() => {
        actionBtn.disabled = false;
        actionBtn.textContent = originalText;
    });
}

/**
 * Show interests modal
 */
function showInterestsModal() {
    const modal = new bootstrap.Modal(document.getElementById('interestsModal'));
    loadInterestsOptions();
    modal.show();
}

/**
 * Load interests options
 */
function loadInterestsOptions() {
    const container = document.getElementById('interestsCheckboxes');
    const availableInterests = [
        'Technology', 'Business', 'Health', 'Education', 'Entertainment',
        'Sports', 'Arts', 'Music', 'Food', 'Travel', 'Fashion', 'Science'
    ];

    const userInterests = currentUser?.interests || [];

    let checkboxesHtml = '';
    availableInterests.forEach(interest => {
        const isChecked = userInterests.includes(interest) ? 'checked' : '';
        checkboxesHtml += `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="${interest}" 
                       id="interest_${interest}" ${isChecked}>
                <label class="form-check-label" for="interest_${interest}">
                    ${interest}
                </label>
            </div>
        `;
    });

    container.innerHTML = checkboxesHtml;
}

/**
 * Update user interests
 */
function updateInterests() {
    const checkboxes = document.querySelectorAll('#interestsCheckboxes input[type="checkbox"]:checked');
    const selectedInterests = Array.from(checkboxes).map(cb => cb.value);

    fetch('/web-api/member/update-interests', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ interests: selectedInterests })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Interests updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('interestsModal')).hide();
            currentUser.interests = selectedInterests;
            loadDashboardData(); // Refresh recommendations
        } else {
            showError(data.message || 'Failed to update interests');
        }
    })
    .catch(error => {
        console.error('Error updating interests:', error);
        showError('Failed to update interests. Please try again.');
    });
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Show success message
 */
function showSuccess(message) {
    // Create and show success toast/alert
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-success');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Show error message
 */
function showError(message) {
    // Create and show error toast/alert
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 8 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-danger');
        if (alert) {
            alert.remove();
        }
    }, 8000);
}
</script>

<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: transform 0.2s;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.event-card {
    transition: all 0.2s;
    border: 1px solid #e9ecef !important;
}

.event-card:hover {
    border-color: #007bff !important;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.recommended-event {
    transition: all 0.2s;
    border: 1px solid #e9ecef !important;
}

.recommended-event:hover {
    border-color: #28a745 !important;
    box-shadow: 0 2px 8px rgba(40,167,69,0.1);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}
</style>
@endsection
