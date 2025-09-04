@extends('layouts.app')

@section('title', 'Create Event')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Event
                    </h4>
                </div>
                <div class="card-body">
                    <form id="createEventForm">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="slogan" class="form-label">Event Slogan</label>
                                <input type="text" class="form-control" id="slogan" name="slogan" placeholder="A catchy tagline for your event">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Describe your event in detail..."></textarea>
                            </div>
                        </div>

                        <!-- Event Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_type" class="form-label">Event Type *</label>
                                <select class="form-control" id="event_type" name="event_type" required>
                                    <option value="">Select Event Type</option>
                                    <option value="conference">Conference</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="seminar">Seminar</option>
                                    <option value="networking">Networking</option>
                                    <option value="webinar">Webinar</option>
                                    <option value="meetup">Meetup</option>
                                    <option value="training">Training</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="audience" class="form-label">Target Audience</label>
                                <select class="form-control" id="audience" name="audience">
                                    <option value="">Select Audience</option>
                                    <option value="professionals">Professionals</option>
                                    <option value="students">Students</option>
                                    <option value="entrepreneurs">Entrepreneurs</option>
                                    <option value="developers">Developers</option>
                                    <option value="general">General Public</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <!-- Categories will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location *</label>
                                <input type="text" class="form-control" id="location" name="location" required placeholder="Event venue or 'Online'">
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Start Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label">End Date & Time *</label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>

                        <!-- Registration Settings -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Registration Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="max_attendees" class="form-label">Maximum Attendees</label>
                                        <input type="number" class="form-control" id="max_attendees" name="max_attendees" min="1" placeholder="Leave empty for unlimited">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                        <input type="datetime-local" class="form-control" id="registration_deadline" name="registration_deadline">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_free" name="is_free" checked>
                                            <label class="form-check-label" for="is_free">
                                                Free Event
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="allow_waitlist" name="allow_waitlist">
                                            <label class="form-check-label" for="allow_waitlist">
                                                Allow Waitlist
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" id="ticketPriceRow" style="display: none;">
                                    <div class="col-md-6">
                                        <label for="ticket_price" class="form-label">Ticket Price ($)</label>
                                        <input type="number" class="form-control" id="ticket_price" name="ticket_price" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="meeting_link" class="form-label">Meeting Link (for virtual events)</label>
                                <input type="url" class="form-control" id="meeting_link" name="meeting_link" placeholder="https://zoom.us/j/...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="image_url" class="form-label">Event Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Back to Dashboard
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                                            <i class="fas fa-save me-2"></i>
                                            Save Draft
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-calendar-plus me-2"></i>
                                            Create Event
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    setupFormValidation();
    
    // Toggle ticket price field based on free event checkbox
    document.getElementById('is_free').addEventListener('change', function() {
        const ticketPriceRow = document.getElementById('ticketPriceRow');
        if (this.checked) {
            ticketPriceRow.style.display = 'none';
            document.getElementById('ticket_price').value = '';
        } else {
            ticketPriceRow.style.display = 'block';
        }
    });
});

async function loadCategories() {
    try {
        const response = await axios.get('/api/categories');
        const categorySelect = document.getElementById('category_id');
        
        response.data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function setupFormValidation() {
    const form = document.getElementById('createEventForm');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        await createEvent();
    });
}

function validateForm() {
    const startTime = new Date(document.getElementById('start_time').value);
    const endTime = new Date(document.getElementById('end_time').value);
    const registrationDeadline = document.getElementById('registration_deadline').value;
    
    // Validate start time is in the future
    if (startTime <= new Date()) {
        showAlert('Start time must be in the future', 'danger');
        return false;
    }
    
    // Validate end time is after start time
    if (endTime <= startTime) {
        showAlert('End time must be after start time', 'danger');
        return false;
    }
    
    // Validate registration deadline if provided
    if (registrationDeadline) {
        const deadline = new Date(registrationDeadline);
        if (deadline >= startTime) {
            showAlert('Registration deadline must be before the event start time', 'danger');
            return false;
        }
    }
    
    return true;
}

async function createEvent() {
    const form = document.getElementById('createEventForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const eventData = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'is_free' || key === 'allow_waitlist') {
            eventData[key] = form.querySelector(`[name="${key}"]`).checked;
        } else if (value !== '') {
            eventData[key] = value;
        }
    }
    
    try {
        const response = await axios.post('/api/events', eventData);
        
        showAlert('Event created successfully!', 'success');
        
        // Redirect to organizer dashboard after successful creation
        setTimeout(() => {
            window.location.href = '{{ route("organizer.dashboard") }}';
        }, 1500);
        
    } catch (error) {
        console.error('Error creating event:', error);
        
        if (error.response && error.response.data.errors) {
            const errors = error.response.data.errors;
            let errorMessage = 'Please fix the following errors:\n';
            for (let field in errors) {
                errorMessage += `- ${errors[field][0]}\n`;
            }
            showAlert(errorMessage, 'danger');
        } else if (error.response && error.response.data.message) {
            showAlert(error.response.data.message, 'danger');
        } else {
            showAlert('Failed to create event. Please try again.', 'danger');
        }
    }
}

function saveDraft() {
    // Save form data to localStorage for later
    const form = document.getElementById('createEventForm');
    const formData = new FormData(form);
    const draftData = {};
    
    for (let [key, value] of formData.entries()) {
        draftData[key] = value;
    }
    
    localStorage.setItem('eventDraft', JSON.stringify(draftData));
    showAlert('Draft saved successfully!', 'success');
}

// Load draft data if available
function loadDraft() {
    const draftData = localStorage.getItem('eventDraft');
    if (draftData) {
        const data = JSON.parse(draftData);
        const form = document.getElementById('createEventForm');
        
        for (let key in data) {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = data[key] === 'on';
                } else {
                    field.value = data[key];
                }
            }
        }
        
        showAlert('Draft loaded!', 'info');
    }
}

// Ask user if they want to load draft on page load
window.addEventListener('load', function() {
    if (localStorage.getItem('eventDraft')) {
        if (confirm('You have a saved draft. Would you like to load it?')) {
            loadDraft();
        }
    }
});
</script>
@endpush

