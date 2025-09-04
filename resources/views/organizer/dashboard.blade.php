@extends('layouts.app')

@section('title', 'Organizer Dashboard')

@section('content')
<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-success text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Organizer Dashboard</h2>
                            <p class="mb-0 opacity-75">Manage your events and connect with your audience</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button class="btn btn-light btn-lg" onclick="createNewEvent()">
                                <i class="fas fa-plus me-2"></i>
                                Create New Event
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4" id="statsCards">
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar fa-2x mb-3"></i>
                    <h3 class="mb-1" id="totalEvents">0</h3>
                    <p class="mb-0">Total Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-3"></i>
                    <h3 class="mb-1" id="upcomingEvents">0</h3>
                    <p class="mb-0">Upcoming Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h3 class="mb-1" id="totalAttendees">0</h3>
                    <p class="mb-0">Total Attendees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                    <h3 class="mb-1" id="totalRevenue">$0</h3>
                    <p class="mb-0">Total Revenue</p>
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
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-primary w-100" onclick="createNewEvent()">
                                <i class="fas fa-plus me-2"></i>
                                New Event
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-info w-100" onclick="viewAnalytics()">
                                <i class="fas fa-chart-bar me-2"></i>
                                Analytics
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-success w-100" onclick="sendCommunication()">
                                <i class="fas fa-envelope me-2"></i>
                                Send Message
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-warning w-100" onclick="manageAttendees()">
                                <i class="fas fa-users me-2"></i>
                                Attendees
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-secondary w-100" onclick="exportData()">
                                <i class="fas fa-download me-2"></i>
                                Export Data
                            </button>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-outline-primary w-100" onclick="viewProfile()">
                                <i class="fas fa-user me-2"></i>
                                Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Events Management -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Your Events
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm active" onclick="filterEvents('all')">All</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterEvents('upcoming')">Upcoming</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterEvents('past')">Past</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterEvents('draft')">Draft</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="eventsTable">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Attendees</th>
                                    <th>Revenue</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="eventsTableBody">
                                <!-- Events will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Communication Modal -->
<div class="modal fade" id="communicationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Communication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="communicationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="eventSelect" class="form-label">Select Event</label>
                        <select class="form-select" id="eventSelect" name="event_id" required>
                            <option value="">Choose an event...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="recipientType" class="form-label">Recipients</label>
                        <select class="form-select" id="recipientType" name="recipient_type" required>
                            <option value="all">All Attendees</option>
                            <option value="checked_in">Checked-in Attendees</option>
                            <option value="not_checked_in">Not Checked-in</option>
                            <option value="waitlisted">Waitlisted</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Analytics Modal -->
<div class="modal fade" id="analyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="analyticsEventSelect" class="form-label">Select Event</label>
                    <select class="form-select" id="analyticsEventSelect">
                        <option value="">Choose an event...</option>
                    </select>
                </div>
                <div id="analyticsContent">
                    <!-- Analytics content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let dashboardData = {};
let currentFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadDashboard();
});

async function loadDashboard() {
    try {
        const response = await axios.get('/api/organizer/dashboard');
        dashboardData = response.data;
        
        // Update stats
        document.getElementById('totalEvents').textContent = dashboardData.stats.total_events;
        document.getElementById('upcomingEvents').textContent = dashboardData.stats.upcoming_events;
        document.getElementById('totalAttendees').textContent = dashboardData.stats.total_attendees;
        document.getElementById('totalRevenue').textContent = '$' + dashboardData.stats.total_revenue.toFixed(2);
        
        // Load events
        loadEvents();
        populateEventSelects();
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showAlert('Failed to load dashboard data', 'danger');
    }
}

function loadEvents() {
    const tbody = document.getElementById('eventsTableBody');
    tbody.innerHTML = '';
    
    let filteredEvents = dashboardData.events;
    
    if (currentFilter === 'upcoming') {
        filteredEvents = dashboardData.events.filter(event => new Date(event.start_time) > new Date());
    } else if (currentFilter === 'past') {
        filteredEvents = dashboardData.events.filter(event => new Date(event.end_time) < new Date());
    }
    
    if (filteredEvents.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No events found</td></tr>';
        return;
    }
    
    filteredEvents.forEach(event => {
        const row = document.createElement('tr');
        const attendeeCount = event.attendees ? event.attendees.length : 0;
        const revenue = (attendeeCount * (event.ticket_price || 0)).toFixed(2);
        const status = getEventStatus(event);
        
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">${event.name}</h6>
                        <small class="text-muted">${event.category?.name || 'General'}</small>
                    </div>
                </div>
            </td>
            <td>
                <small>${formatDate(event.start_time)}</small>
            </td>
            <td>
                <small>${event.location || 'Online'}</small>
            </td>
            <td>
                <span class="badge bg-info">${attendeeCount}</span>
            </td>
            <td>
                <span class="text-success">$${revenue}</span>
            </td>
            <td>
                <span class="badge ${status.class}">${status.text}</span>
            </td>
            <td>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-primary" onclick="editEvent(${event.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="viewEventAnalytics(${event.id})" title="Analytics">
                        <i class="fas fa-chart-bar"></i>
                    </button>
                    <button class="btn btn-outline-success" onclick="manageEventAttendees(${event.id})" title="Attendees">
                        <i class="fas fa-users"></i>
                    </button>
                    <button class="btn btn-outline-warning" onclick="sendEventCommunication(${event.id})" title="Message">
                        <i class="fas fa-envelope"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function getEventStatus(event) {
    const now = new Date();
    const startTime = new Date(event.start_time);
    const endTime = new Date(event.end_time);
    
    if (endTime < now) {
        return { text: 'Completed', class: 'bg-secondary' };
    } else if (startTime <= now && endTime >= now) {
        return { text: 'Live', class: 'bg-success' };
    } else if (startTime > now) {
        return { text: 'Upcoming', class: 'bg-primary' };
    } else {
        return { text: 'Draft', class: 'bg-warning' };
    }
}

function filterEvents(filter) {
    currentFilter = filter;
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    loadEvents();
}

function populateEventSelects() {
    const eventSelect = document.getElementById('eventSelect');
    const analyticsEventSelect = document.getElementById('analyticsEventSelect');
    
    eventSelect.innerHTML = '<option value="">Choose an event...</option>';
    analyticsEventSelect.innerHTML = '<option value="">Choose an event...</option>';
    
    dashboardData.events.forEach(event => {
        const option = `<option value="${event.id}">${event.name}</option>`;
        eventSelect.innerHTML += option;
        analyticsEventSelect.innerHTML += option;
    });
}

function createNewEvent() {
    window.location.href = '{{ route("events.create") }}';
}

function editEvent(eventId) {
    window.location.href = `/events/${eventId}/edit`;
}

function sendCommunication() {
    const modal = new bootstrap.Modal(document.getElementById('communicationModal'));
    modal.show();
}

function sendEventCommunication(eventId) {
    document.getElementById('eventSelect').value = eventId;
    sendCommunication();
}

function viewAnalytics() {
    const modal = new bootstrap.Modal(document.getElementById('analyticsModal'));
    modal.show();
}

async function viewEventAnalytics(eventId) {
    document.getElementById('analyticsEventSelect').value = eventId;
    await loadEventAnalytics(eventId);
    viewAnalytics();
}

async function loadEventAnalytics(eventId) {
    try {
        const response = await axios.get(`/api/organizer/events/${eventId}/analytics`);
        const analytics = response.data.analytics;
        
        document.getElementById('analyticsContent').innerHTML = `
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-primary">${analytics.total_registrations}</h4>
                            <p class="mb-0">Total Registrations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-success">${analytics.checked_in_count}</h4>
                            <p class="mb-0">Checked In</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-info">${analytics.check_in_rate.toFixed(1)}%</h4>
                            <p class="mb-0">Check-in Rate</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4 class="text-warning">$${analytics.revenue.toFixed(2)}</h4>
                            <p class="mb-0">Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
    } catch (error) {
        console.error('Error loading analytics:', error);
        showAlert('Failed to load analytics', 'danger');
    }
}

function manageAttendees() {
    window.location.href = '/organizer/attendees';
}

function manageEventAttendees(eventId) {
    window.location.href = `/events/${eventId}/attendees`;
}

function exportData() {
    showAlert('Export functionality coming soon!', 'info');
}

function viewProfile() {
    window.location.href = '/profile';
}

// Handle communication form submission
document.getElementById('communicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const eventId = formData.get('event_id');
    
    if (!eventId) {
        showAlert('Please select an event', 'warning');
        return;
    }
    
    try {
        const response = await axios.post(`/api/organizer/events/${eventId}/communications`, {
            subject: formData.get('subject'),
            message: formData.get('message'),
            recipient_type: formData.get('recipient_type')
        });
        
        showAlert('Communication sent successfully!', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('communicationModal'));
        modal.hide();
        
        // Reset form
        this.reset();
        
    } catch (error) {
        console.error('Error sending communication:', error);
        showAlert('Failed to send communication', 'danger');
    }
});

// Handle analytics event selection
document.getElementById('analyticsEventSelect').addEventListener('change', function() {
    if (this.value) {
        loadEventAnalytics(this.value);
    } else {
        document.getElementById('analyticsContent').innerHTML = '';
    }
});
</script>
@endpush

