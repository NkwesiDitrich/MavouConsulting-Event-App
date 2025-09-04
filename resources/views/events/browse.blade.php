@extends('layouts.app')

@section('title', 'Browse Events')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">Discover Amazing Events</h2>
                            <p class="mb-0 opacity-75">Find events that match your interests and connect with like-minded people</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search events...">
                                <button class="btn btn-light" type="button" onclick="searchEvents()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label for="categoryFilter" class="form-label">Category</label>
                            <select class="form-select" id="categoryFilter" onchange="applyFilters()">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="locationFilter" class="form-label">Location</label>
                            <select class="form-select" id="locationFilter" onchange="applyFilters()">
                                <option value="">All Locations</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="dateFromFilter" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="dateFromFilter" onchange="applyFilters()">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="dateToFilter" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="dateToFilter" onchange="applyFilters()">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Events -->
    <div class="row mb-4" id="featuredSection">
        <div class="col-12">
            <h4 class="mb-3">
                <i class="fas fa-star text-warning me-2"></i>
                Featured Events
            </h4>
            <div class="row" id="featuredEvents">
                <!-- Featured events will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    All Events
                    <span class="badge bg-primary ms-2" id="eventCount">0</span>
                </h4>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm active" onclick="changeView('grid')">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <div id="eventsContainer">
                <div class="row" id="eventsGrid">
                    <!-- Events will be loaded here -->
                </div>
            </div>
            
            <!-- Loading Spinner -->
            <div class="text-center py-4 d-none" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary" id="loadMoreBtn" onclick="loadMoreEvents()">
                    <i class="fas fa-plus me-2"></i>
                    Load More Events
                </button>
            </div>
        </div>
    </div>
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
let currentPage = 1;
let currentView = 'grid';
let filters = {};
let allEvents = [];
let featuredEvents = [];

document.addEventListener('DOMContentLoaded', function() {
    loadInitialData();
    
    // Set up search input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchEvents();
        }
    });
});

async function loadInitialData() {
    try {
        // Load featured events
        const featuredResponse = await axios.get('/api/public/events/featured');
        featuredEvents = featuredResponse.data.featured_events;
        loadFeaturedEvents();
        
        // Load all events with filters
        await loadEvents();
        
        // Load filter options
        await loadFilterOptions();
        
    } catch (error) {
        console.error('Error loading initial data:', error);
        showAlert('Failed to load events', 'danger');
    }
}

async function loadEvents(page = 1) {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            page: page,
            ...filters
        });
        
        const response = await axios.get(`/api/public/events?${params}`);
        const data = response.data;
        
        if (page === 1) {
            allEvents = data.events.data;
        } else {
            allEvents = [...allEvents, ...data.events.data];
        }
        
        displayEvents();
        updateEventCount();
        
        // Update load more button
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (data.events.next_page_url) {
            loadMoreBtn.style.display = 'block';
            currentPage = page;
        } else {
            loadMoreBtn.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Error loading events:', error);
        showAlert('Failed to load events', 'danger');
    } finally {
        hideLoading();
    }
}

function loadFeaturedEvents() {
    const container = document.getElementById('featuredEvents');
    container.innerHTML = '';
    
    if (featuredEvents.length === 0) {
        document.getElementById('featuredSection').style.display = 'none';
        return;
    }
    
    featuredEvents.slice(0, 3).forEach(event => {
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-3';
        col.innerHTML = createEventCard(event, true);
        container.appendChild(col);
    });
}

function displayEvents() {
    const container = document.getElementById('eventsGrid');
    container.innerHTML = '';
    
    if (allEvents.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No events found</h5>
                    <p class="text-muted">Try adjusting your filters or search terms</p>
                </div>
            </div>
        `;
        return;
    }
    
    allEvents.forEach(event => {
        const col = document.createElement('div');
        col.className = currentView === 'grid' ? 'col-md-6 col-lg-4 mb-4' : 'col-12 mb-3';
        col.innerHTML = createEventCard(event, false, currentView === 'list');
        container.appendChild(col);
    });
}

function createEventCard(event, isFeatured = false, isList = false) {
    const attendeeCount = event.attendees_count || 0;
    const isUpcoming = new Date(event.start_time) > new Date();
    const featuredBadge = isFeatured ? '<span class="badge bg-warning position-absolute top-0 start-0 m-2">Featured</span>' : '';
    
    if (isList) {
        return `
            <div class="card event-card h-100">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="${event.image_url || '/images/default-event.jpg'}" class="img-fluid rounded-start h-100" alt="${event.name}" style="object-fit: cover;">
                        ${featuredBadge}
                    </div>
                    <div class="col-md-8">
                        <div class="card-body h-100 d-flex flex-column">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-1">${event.name}</h5>
                                    <span class="badge bg-primary">${event.category?.name || 'General'}</span>
                                </div>
                                <p class="card-text text-muted">${event.description?.substring(0, 150) || ''}${event.description?.length > 150 ? '...' : ''}</p>
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
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-info me-2">${attendeeCount} attendees</span>
                                    ${event.is_free ? '<span class="badge bg-success">Free</span>' : `<span class="badge bg-warning">$${event.ticket_price}</span>`}
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm me-2" onclick="viewEventDetails(${event.id})">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Details
                                    </button>
                                    ${isUpcoming ? `<button class="btn btn-primary btn-sm" onclick="registerForEvent(${event.id})">
                                        <i class="fas fa-calendar-plus me-1"></i>
                                        Register
                                    </button>` : '<span class="text-muted">Event Ended</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    return `
        <div class="card event-card h-100 position-relative">
            <img src="${event.image_url || '/images/default-event.jpg'}" class="card-img-top" alt="${event.name}" style="height: 200px; object-fit: cover;">
            ${featuredBadge}
            <div class="card-body d-flex flex-column">
                <div class="flex-grow-1">
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
                </div>
                <div class="mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-info me-1">${attendeeCount}</span>
                            ${event.is_free ? '<span class="badge bg-success">Free</span>' : `<span class="badge bg-warning">$${event.ticket_price}</span>`}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm flex-fill" onclick="viewEventDetails(${event.id})">
                            <i class="fas fa-info-circle me-1"></i>
                            Details
                        </button>
                        ${isUpcoming ? `<button class="btn btn-primary btn-sm flex-fill" onclick="registerForEvent(${event.id})">
                            <i class="fas fa-calendar-plus me-1"></i>
                            Register
                        </button>` : '<span class="text-muted small">Event Ended</span>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

async function loadFilterOptions() {
    try {
        const response = await axios.get('/api/public/events');
        const data = response.data;
        
        // Populate category filter
        const categoryFilter = document.getElementById('categoryFilter');
        data.filters.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categoryFilter.appendChild(option);
        });
        
        // Populate location filter
        const locationFilter = document.getElementById('locationFilter');
        data.filters.locations.forEach(location => {
            if (location) {
                const option = document.createElement('option');
                option.value = location;
                option.textContent = location;
                locationFilter.appendChild(option);
            }
        });
        
    } catch (error) {
        console.error('Error loading filter options:', error);
    }
}

function applyFilters() {
    filters = {};
    
    const category = document.getElementById('categoryFilter').value;
    const location = document.getElementById('locationFilter').value;
    const dateFrom = document.getElementById('dateFromFilter').value;
    const dateTo = document.getElementById('dateToFilter').value;
    
    if (category) filters.category = category;
    if (location) filters.location = location;
    if (dateFrom) filters.date_from = dateFrom;
    if (dateTo) filters.date_to = dateTo;
    
    currentPage = 1;
    loadEvents(1);
}

function clearFilters() {
    document.getElementById('categoryFilter').value = '';
    document.getElementById('locationFilter').value = '';
    document.getElementById('dateFromFilter').value = '';
    document.getElementById('dateToFilter').value = '';
    
    filters = {};
    currentPage = 1;
    loadEvents(1);
}

async function searchEvents() {
    const searchTerm = document.getElementById('searchInput').value.trim();
    
    if (!searchTerm) {
        applyFilters();
        return;
    }
    
    try {
        showLoading();
        
        const response = await axios.get(`/api/public/events/search?q=${encodeURIComponent(searchTerm)}`);
        allEvents = response.data.events;
        
        displayEvents();
        updateEventCount();
        
        // Hide load more button for search results
        document.getElementById('loadMoreBtn').style.display = 'none';
        
    } catch (error) {
        console.error('Error searching events:', error);
        showAlert('Failed to search events', 'danger');
    } finally {
        hideLoading();
    }
}

function loadMoreEvents() {
    loadEvents(currentPage + 1);
}

function changeView(view) {
    currentView = view;
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    displayEvents();
}

function updateEventCount() {
    document.getElementById('eventCount').textContent = allEvents.length;
}

async function viewEventDetails(eventId) {
    try {
        const response = await axios.get(`/api/public/events/${eventId}`);
        const event = response.data.event;
        const stats = response.data.stats;
        
        document.getElementById('eventModalTitle').textContent = event.name;
        
        document.getElementById('eventModalBody').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <img src="${event.image_url || '/images/default-event.jpg'}" class="img-fluid rounded mb-3" alt="${event.name}">
                </div>
                <div class="col-md-6">
                    <h5>${event.name}</h5>
                    <p class="text-muted">${event.description || 'No description available'}</p>
                    
                    <div class="mb-2">
                        <strong>Date:</strong> ${formatDate(event.start_time)}
                    </div>
                    <div class="mb-2">
                        <strong>Location:</strong> ${event.location || 'Online'}
                    </div>
                    <div class="mb-2">
                        <strong>Organizer:</strong> ${event.organizer?.name || 'Unknown'}
                    </div>
                    <div class="mb-2">
                        <strong>Category:</strong> ${event.category?.name || 'General'}
                    </div>
                    <div class="mb-2">
                        <strong>Attendees:</strong> ${stats.attendee_count}
                    </div>
                    <div class="mb-2">
                        <strong>Price:</strong> ${event.is_free ? 'Free' : `$${event.ticket_price}`}
                    </div>
                    
                    ${stats.is_full ? '<div class="alert alert-warning">This event is full</div>' : ''}
                    ${!stats.registration_open ? '<div class="alert alert-danger">Registration is closed</div>' : ''}
                </div>
            </div>
        `;
        
        const isUpcoming = new Date(event.start_time) > new Date();
        const canRegister = isUpcoming && stats.registration_open && !stats.is_full;
        
        document.getElementById('eventModalFooter').innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            ${canRegister ? `<button type="button" class="btn btn-primary" onclick="registerForEvent(${event.id})">
                <i class="fas fa-calendar-plus me-2"></i>
                Register for Event
            </button>` : ''}
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));
        modal.show();
        
    } catch (error) {
        console.error('Error loading event details:', error);
        showAlert('Failed to load event details', 'danger');
    }
}

async function registerForEvent(eventId) {
    // Check if user is logged in
    const token = localStorage.getItem('auth_token');
    if (!token) {
        showAlert('Please login to register for events', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 1500);
        return;
    }
    
    try {
        const response = await axios.post(`/api/events/${eventId}/attendees`);
        showAlert('Successfully registered for the event!', 'success');
        
        // Close modal if open
        const modal = bootstrap.Modal.getInstance(document.getElementById('eventModal'));
        if (modal) {
            modal.hide();
        }
        
        // Refresh the event data
        loadEvents(1);
        
    } catch (error) {
        console.error('Error registering for event:', error);
        if (error.response && error.response.data.message) {
            showAlert(error.response.data.message, 'danger');
        } else {
            showAlert('Failed to register for event', 'danger');
        }
    }
}

function showLoading() {
    document.getElementById('loadingSpinner').classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingSpinner').classList.add('d-none');
}
</script>
@endpush

