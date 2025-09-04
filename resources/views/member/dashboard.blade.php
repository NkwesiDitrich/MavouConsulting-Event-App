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
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="interestsCount">0</h3>
                            <p class="mb-0">Interests</p>
                        </div>
                        <i class="fas fa-heart fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0" id="recommendedCount">0</h3>
                            <p class="mb-0">Recommended</p>
                        </div>
                        <i class="fas fa-star fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Registered Events -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        My Upcoming Events
                    </h5>
                    <a href="{{ route('events.browse') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>
                        Find More Events
                    </a>
                </div>
                <div class="card-body">
                    <div id="registeredEvents">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended Events -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Recommended for You
                    </h5>
                </div>
                <div class="card-body">
                    <div id="recommendedEvents">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('events.browse') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>
                            Browse Events
                        </a>
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>
                            Edit Profile
                        </a>
                        <button class="btn btn-outline-success" onclick="updateInterests()">
                            <i class="fas fa-heart me-2"></i>
                            Update Interests
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Interests -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heart me-2"></i>
                        Your Interests
                    </h5>
                </div>
                <div class="card-body">
                    <div id="userInterests">
                        <p class="text-muted">Loading...</p>
                    </div>
                    <button class="btn btn-outline-primary btn-sm w-100" onclick="updateInterests()">
                        <i class="fas fa-edit me-1"></i>
                        Update Interests
                    </button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div id="recentActivity">
                        <p class="text-muted">No recent activity</p>
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
        const response = await axios.get('/web-api/member/dashboard');
        dashboardData = response.data;
        
        // Update user info
        document.getElementById('userName').textContent = dashboardData.user.name;
        document.getElementById('userAvatar').src = dashboardData.user.profile_picture;
        
        // Update stats
        document.getElementById('eventsAttended').textContent = dashboardData.stats.total_events_attended;
        document.getElementById('upcomingRegistrations').textContent = dashboardData.stats.upcoming_registrations;
        document.getElementById('interestsCount').textContent = (dashboardData.user.interests || []).length;
        document.getElementById('recommendedCount').textContent = dashboardData.recommended_events.length;
        
        // Display registered events
        displayRegisteredEvents();
        
        // Display recommended events
        displayRecommendedEvents();
        
        // Display user interests
        displayUserInterests();
        
        userInterests = dashboardData.user.interests || [];
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showAlert('Failed to load dashboard data', 'danger');
        
        // Show error state
        document.getElementById('registeredEvents').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                <p class="text-danger">Failed to load dashboard data</p>
                <button class="btn btn-outline-primary" onclick="loadDashboard()">Try Again</button>
            </div>
        `;
    }
}

function displayRegisteredEvents() {
    const container = document.getElementById('registeredEvents');
    
    if (!dashboardData.registered_events || dashboardData.registered_events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                <h6 class="text-muted">No upcoming events</h6>
                <p class="text-muted mb-3">You haven't registered for any upcoming events yet.</p>
                <a href="{{ route('events.browse') }}" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>
                    Browse Events
                </a>
            </div>
        `;
        return;
    }
    
    container.innerHTML = dashboardData.registered_events.map(event => `
        <div class="card mb-3 event-card">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="${event.image_url || '/images/default-event.jpg'}" class="img-fluid rounded-start h-100" alt="${event.name}" style="object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${event.name}</h6>
                            <span class="badge bg-primary">${event.category?.name || 'General'}</span>
                        </div>
                        <p class="card-text text-muted small">${event.description?.substring(0, 100) || ''}${event.description?.length > 100 ? '...' : ''}</p>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                by ${event.organizer?.name || 'Unknown'}
                            </small>
                            <button class="btn btn-outline-primary btn-sm" onclick="viewEventDetails(${event.id})">
                                <i class="fas fa-info-circle me-1"></i>
                                Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function displayRecommendedEvents() {
    const container = document.getElementById('recommendedEvents');
    
    if (!dashboardData.recommended_events || dashboardData.recommended_events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-star fa-2x text-muted mb-3"></i>
                <h6 class="text-muted">No recommendations yet</h6>
                <p class="text-muted mb-3">Update your interests to get personalized event recommendations.</p>
                <button class="btn btn-primary" onclick="updateInterests()">
                    <i class="fas fa-heart me-1"></i>
                    Set Interests
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = dashboardData.recommended_events.map(event => `
        <div class="card mb-3 event-card">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="${event.image_url || '/images/default-event.jpg'}" class="img-fluid rounded-start h-100" alt="${event.name}" style="object-fit: cover;">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${event.name}</h6>
                            <span class="badge bg-warning">Recommended</span>
                        </div>
                        <p class="card-text text-muted small">${event.description?.substring(0, 100) || ''}${event.description?.length > 100 ? '...' : ''}</p>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                ${formatDate(event.start_time)}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                ${event.location || 'Online'}
                            </small>
                            <div>
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="viewEventDetails(${event.id})">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Details
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="registerForEvent(${event.id})">
                                    <i class="fas fa-calendar-plus me-1"></i>
                                    Register
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function displayUserInterests() {
    const container = document.getElementById('userInterests');
    const interests = dashboardData.user.interests || [];
    
    if (interests.length === 0) {
        container.innerHTML = '<p class="text-muted">No interests selected yet</p>';
        return;
    }
    
    container.innerHTML = interests.map(interest => 
        `<span class="badge bg-primary me-1 mb-1">${interest}</span>`
    ).join('');
}

function updateInterests() {
    // Load interests form
    const form = document.getElementById('interestsForm');
    const availableInterests = [
        'Technology', 'Business', 'Networking', 'Education', 
        'Arts & Culture', 'Sports', 'Health & Wellness', 'Food & Drink',
        'Music', 'Photography', 'Travel', 'Science', 'Marketing',
        'Design', 'Finance', 'Entrepreneurship'
    ];
    
    form.innerHTML = '';
    availableInterests.forEach((interest, index) => {
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
        
        // Reload dashboard to get new recommendations
        setTimeout(() => {
            loadDashboard();
        }, 1000);
        
    } catch (error) {
        console.error('Error updating interests:', error);
        showAlert('Failed to update interests', 'danger');
    }
}

async function viewEventDetails(eventId) {
    try {
        const response = await axios.get(`/web-api/events/${eventId}`);
        const eventData = response.data;
        
        // You can implement a modal or redirect to event details page
        window.location.href = `/events/${eventId}`;
        
    } catch (error) {
        console.error('Error loading event details:', error);
        showAlert('Failed to load event details', 'danger');
    }
}

async function registerForEvent(eventId) {
    try {
        const response = await axios.post(`/web-api/events/${eventId}/register`);
        showAlert(response.data.message, 'success');
        
        // Reload dashboard to update registered events
        setTimeout(() => {
            loadDashboard();
        }, 1000);
        
    } catch (error) {
        console.error('Error registering for event:', error);
        if (error.response && error.response.status === 401) {
            showAlert('Please login to register for events', 'warning');
        } else {
            showAlert(error.response?.data?.message || 'Failed to register for event', 'danger');
        }
    }
}
</script>
@endpush
