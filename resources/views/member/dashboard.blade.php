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
                            <h2 class="mb-2">Welcome back, {{ Auth::user()->name }}!</h2>
                            <p class="mb-0 opacity-75">Manage your events and discover new opportunities</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex justify-content-md-end gap-2">
                                <a href="{{ route('events.browse') }}" class="btn btn-light">
                                    <i class="fas fa-search me-1"></i>
                                    Browse Events
                                </a>
                                <a href="{{ route('member.profile') }}" class="btn btn-outline-light">
                                    <i class="fas fa-user me-1"></i>
                                    Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-primary mb-2">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['total_registered'] }}</h3>
                    <p class="text-muted mb-0">Total Events Registered</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-success mb-2">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['upcoming_events'] }}</h3>
                    <p class="text-muted mb-0">Upcoming Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6 text-info mb-2">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="mb-1">{{ $stats['past_events'] }}</h3>
                    <p class="text-muted mb-0">Past Events</p>
                </div>
            </div>
        </div>
    </div>

    <!-- My Registered Events -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-check me-2"></i>
                    My Registered Events
                </h4>
                <a href="{{ route('member.my-events') }}" class="btn btn-outline-primary btn-sm">
                    View All
                    <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            
            <div class="row" id="registeredEventsContainer">
                @forelse($registeredEvents->take(6) as $registration)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card event-card h-100">
                            <img src="{{ $registration->event->image_url ?? '/images/default-event.jpg' }}" 
                                 class="card-img-top" alt="{{ $registration->event->name }}" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $registration->event->name }}</h6>
                                        @if($registration->event->category)
                                            <span class="badge bg-primary">{{ $registration->event->category->name }}</span>
                                        @endif
                                    </div>
                                    <p class="card-text text-muted small mb-2">
                                        {{ Str::limit($registration->event->description, 100) }}
                                    </p>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($registration->event->start_time)->format('M d, Y g:i A') }}
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $registration->event->location ?? 'Online' }}
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Registered on {{ $registration->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary btn-sm flex-fill" 
                                                onclick="viewEventDetails({{ $registration->event->id }})">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Details
                                        </button>
                                        @if($registration->event->start_time > now())
                                            <button class="btn btn-outline-danger btn-sm" 
                                                    onclick="cancelRegistration({{ $registration->event->id }})">
                                                <i class="fas fa-times me-1"></i>
                                                Cancel
                                            </button>
                                        @else
                                            <span class="btn btn-outline-secondary btn-sm disabled">
                                                <i class="fas fa-clock me-1"></i>
                                                Past Event
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No registered events yet</h5>
                            <p class="text-muted">Start exploring events and register for ones that interest you!</p>
                            <a href="{{ route('events.browse') }}" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                Browse Events
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recommended Events -->
    @if($upcomingEvents->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="fas fa-star text-warning me-2"></i>
                Recommended for You
            </h4>
            
            <div class="row">
                @foreach($upcomingEvents as $event)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card event-card h-100">
                            <img src="{{ $event->image_url ?? '/images/default-event.jpg' }}" 
                                 class="card-img-top" alt="{{ $event->name }}" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">{{ $event->name }}</h6>
                                        @if($event->category)
                                            <span class="badge bg-primary">{{ $event->category->name }}</span>
                                        @endif
                                    </div>
                                    <p class="card-text text-muted small mb-2">
                                        {{ Str::limit($event->description, 100) }}
                                    </p>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            {{ \Carbon\Carbon::parse($event->start_time)->format('M d, Y g:i A') }}
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            {{ $event->location ?? 'Online' }}
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            by {{ $event->organizer->name }}
                                        </small>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            @if($event->is_free)
                                                <span class="badge bg-success">Free</span>
                                            @else
                                                <span class="badge bg-warning">${{ $event->ticket_price }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary btn-sm flex-fill" 
                                                onclick="viewEventDetails({{ $event->id }})">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Details
                                        </button>
                                        <button class="btn btn-primary btn-sm flex-fill" 
                                                onclick="registerForEvent({{ $event->id }})">
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            Register
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Event details will be loaded here -->
            </div>
            <div class="modal-footer" id="eventModalFooter">
                <!-- Action buttons will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// FIXED: Use the correct API endpoint for member dashboard
async function viewEventDetails(eventId) {
    try {
        showLoading();
        
        // CRITICAL FIX: Use the correct member API endpoint
        const response = await axios.get(`/web-api/member/event-details/${eventId}`);
        const data = response.data;
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load event details');
        }
        
        const event = data.event;
        const stats = data.stats;
        const registration = data.registration;
        
        // Populate modal title
        document.getElementById('eventModalTitle').textContent = event.name;
        
        // Populate modal body
        const modalBody = document.getElementById('eventModalBody');
        modalBody.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${event.image_url || '/images/default-event.jpg'}" 
                         class="img-fluid rounded mb-3" alt="${event.name}">
                </div>
                <div class="col-md-6">
                    <h5>${event.name}</h5>
                    <p class="text-muted">${event.description || 'No description available'}</p>
                    
                    <div class="mb-2">
                        <strong><i class="fas fa-calendar me-2"></i>Start:</strong>
                        ${formatDateTime(event.start_time)}
                    </div>
                    
                    ${event.end_time ? `
                    <div class="mb-2">
                        <strong><i class="fas fa-calendar me-2"></i>End:</strong>
                        ${formatDateTime(event.end_time)}
                    </div>
                    ` : ''}
                    
                    <div class="mb-2">
                        <strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong>
                        ${event.location || 'Online'}
                    </div>
                    
                    <div class="mb-2">
                        <strong><i class="fas fa-user me-2"></i>Organizer:</strong>
                        ${event.organizer.name}
                    </div>
                    
                    <div class="mb-2">
                        <strong><i class="fas fa-users me-2"></i>Attendees:</strong>
                        ${stats.attendee_count}
                        ${event.max_attendees ? ` / ${event.max_attendees}` : ''}
                    </div>
                    
                    ${event.category ? `
                    <div class="mb-2">
                        <strong><i class="fas fa-tag me-2"></i>Category:</strong>
                        <span class="badge bg-primary">${event.category.name}</span>
                    </div>
                    ` : ''}
                    
                    <div class="mb-2">
                        <strong><i class="fas fa-dollar-sign me-2"></i>Price:</strong>
                        ${event.is_free ? '<span class="badge bg-success">Free</span>' : `$${event.ticket_price}`}
                    </div>
                    
                    ${stats.is_registered ? `
                    <div class="mb-2">
                        <strong><i class="fas fa-check-circle me-2 text-success"></i>Status:</strong>
                        <span class="badge bg-success">Registered</span>
                        ${registration ? `<small class="text-muted d-block">Registered on ${formatDate(registration.registered_at)}</small>` : ''}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Populate modal footer
        const modalFooter = document.getElementById('eventModalFooter');
        const isUpcoming = new Date(event.start_time) > new Date();
        
        let footerContent = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        
        if (stats.is_registered) {
            if (isUpcoming) {
                footerContent += `
                    <button type="button" class="btn btn-outline-danger" 
                            onclick="cancelRegistration(${event.id})" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancel Registration
                    </button>
                `;
            }
        } else if (isUpcoming && stats.registration_open) {
            footerContent += `
                <button type="button" class="btn btn-primary" 
                        onclick="registerForEvent(${event.id})" data-bs-dismiss="modal">
                    <i class="fas fa-calendar-plus me-1"></i>
                    Register for Event
                </button>
            `;
        }
        
        modalFooter.innerHTML = footerContent;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error loading event details:', error);
        showAlert('Failed to load event details: ' + (error.response?.data?.message || error.message), 'danger');
    } finally {
        hideLoading();
    }
}

async function registerForEvent(eventId) {
    try {
        showLoading();
        
        const response = await axios.post(`/web-api/member/events/${eventId}/register`);
        
        if (response.data.success) {
            showAlert(response.data.message, 'success');
            // Refresh the page to update the dashboard
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(response.data.message || 'Registration failed');
        }
        
    } catch (error) {
        console.error('Error registering for event:', error);
        if (error.response && error.response.status === 401) {
            showAlert('Please login to register for events', 'warning');
            window.location.href = '/login';
        } else {
            showAlert('Failed to register: ' + (error.response?.data?.message || error.message), 'danger');
        }
    } finally {
        hideLoading();
    }
}

async function cancelRegistration(eventId) {
    if (!confirm('Are you sure you want to cancel your registration for this event?')) {
        return;
    }
    
    try {
        showLoading();
        
        const response = await axios.delete(`/web-api/member/events/${eventId}/register`);
        
        if (response.data.success) {
            showAlert(response.data.message, 'success');
            // Refresh the page to update the dashboard
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(response.data.message || 'Cancellation failed');
        }
        
    } catch (error) {
        console.error('Error cancelling registration:', error);
        showAlert('Failed to cancel registration: ' + (error.response?.data?.message || error.message), 'danger');
    } finally {
        hideLoading();
    }
}

// Utility functions
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function showLoading() {
    // You can implement a loading spinner here
    console.log('Loading...');
}

function hideLoading() {
    // Hide loading spinner
    console.log('Loading complete');
}

function showAlert(message, type = 'info') {
    // Create and show Bootstrap alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endpush
