@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <img id="profilePicture" src="" alt="Profile Picture" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <h4 id="profileName">Loading...</h4>
                    <p class="text-muted" id="profileEmail">Loading...</p>
                    <span class="badge bg-primary" id="profileRole">Loading...</span>
                    <div class="mt-3">
                        <p class="text-muted small" id="profileBio">Loading...</p>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="editProfile()">
                            <i class="fas fa-edit me-1"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Statistics</h6>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="mb-0" id="eventsAttended">0</h4>
                            <small class="text-muted">Events Attended</small>
                        </div>
                        <div class="col-6">
                            <h4 class="mb-0" id="eventsOrganized">0</h4>
                            <small class="text-muted">Events Organized</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interests Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6 class="card-title">Interests</h6>
                    <div id="interestsList">
                        <p class="text-muted">Loading...</p>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="editInterests()">
                        <i class="fas fa-heart me-1"></i>
                        Update Interests
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Tell us about yourself..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/yourprofile">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <input type="url" class="form-control" id="twitter_url" name="twitter_url" placeholder="https://twitter.com/yourhandle">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <small class="text-muted">Max file size: 2MB. Supported formats: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="loadProfile()">
                                <i class="fas fa-undo me-1"></i>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- My Events -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">My Events</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="eventsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="organized-tab" data-bs-toggle="tab" data-bs-target="#organized" type="button" role="tab">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Organized Events
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attended-tab" data-bs-toggle="tab" data-bs-target="#attended" type="button" role="tab">
                                <i class="fas fa-calendar-check me-1"></i>
                                Attended Events
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="eventsTabContent">
                        <div class="tab-pane fade show active" id="organized" role="tabpanel">
                            <div class="mt-3" id="organizedEvents">
                                <p class="text-muted">Loading...</p>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="attended" role="tabpanel">
                            <div class="mt-3" id="attendedEvents">
                                <p class="text-muted">Loading...</p>
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
                <h5 class="modal-title">Update Interests</h5>
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
let userProfile = {};
let userInterests = [];

document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
    loadMyEvents();
    
    // Set up form submissions
    document.getElementById('profileForm').addEventListener('submit', updateProfile);
    document.getElementById('passwordForm').addEventListener('submit', updatePassword);
});

async function loadProfile() {
    try {
        const response = await axios.get('/web-api/profile');
        userProfile = response.data.user;
        userInterests = userProfile.interests || [];
        
        // Update profile display
        document.getElementById('profilePicture').src = userProfile.profile_picture;
        document.getElementById('profileName').textContent = userProfile.name;
        document.getElementById('profileEmail').textContent = userProfile.email;
        document.getElementById('profileRole').textContent = userProfile.role.charAt(0).toUpperCase() + userProfile.role.slice(1);
        document.getElementById('profileBio').textContent = userProfile.bio || 'No bio provided';
        document.getElementById('eventsAttended').textContent = userProfile.events_attended || 0;
        
        // Update form fields
        document.getElementById('name').value = userProfile.name;
        document.getElementById('email').value = userProfile.email;
        document.getElementById('bio').value = userProfile.bio || '';
        document.getElementById('linkedin_url').value = userProfile.linkedin_url || '';
        document.getElementById('twitter_url').value = userProfile.twitter_url || '';
        
        // Update interests display
        displayInterests();
        
    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Failed to load profile data', 'danger');
    }
}

function displayInterests() {
    const container = document.getElementById('interestsList');
    
    if (userInterests.length === 0) {
        container.innerHTML = '<p class="text-muted">No interests selected</p>';
        return;
    }
    
    container.innerHTML = userInterests.map(interest => 
        `<span class="badge bg-primary me-1 mb-1">${interest}</span>`
    ).join('');
}

async function updateProfile(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        
        const response = await axios.put('/web-api/profile', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        userProfile = response.data.user;
        showAlert(response.data.message, 'success');
        
        // Update profile display
        document.getElementById('profilePicture').src = userProfile.profile_picture;
        document.getElementById('profileName').textContent = userProfile.name;
        document.getElementById('profileEmail').textContent = userProfile.email;
        document.getElementById('profileBio').textContent = userProfile.bio || 'No bio provided';
        
    } catch (error) {
        console.error('Error updating profile:', error);
        showAlert(error.response?.data?.message || 'Failed to update profile', 'danger');
    }
}

async function updatePassword(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    if (password !== passwordConfirmation) {
        showAlert('Passwords do not match', 'danger');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        const response = await axios.put('/web-api/profile/password', data);
        
        showAlert(response.data.message, 'success');
        
        // Clear form
        e.target.reset();
        
    } catch (error) {
        console.error('Error updating password:', error);
        showAlert(error.response?.data?.message || 'Failed to update password', 'danger');
    }
}

async function loadMyEvents() {
    try {
        const response = await axios.get('/web-api/profile/events');
        const data = response.data;
        
        // Display organized events
        const organizedContainer = document.getElementById('organizedEvents');
        if (data.organized_events.length === 0) {
            organizedContainer.innerHTML = '<p class="text-muted">You haven\'t organized any events yet.</p>';
        } else {
            organizedContainer.innerHTML = data.organized_events.map(event => `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${event.name}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${formatDate(event.start_time)}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    ${event.location || 'Online'}
                                </small>
                            </div>
                            <span class="badge bg-primary">${event.category?.name || 'General'}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Display attended events
        const attendedContainer = document.getElementById('attendedEvents');
        if (data.attended_events.length === 0) {
            attendedContainer.innerHTML = '<p class="text-muted">You haven\'t attended any events yet.</p>';
        } else {
            attendedContainer.innerHTML = data.attended_events.map(event => `
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${event.name}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${formatDate(event.start_time)}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    by ${event.organizer?.name || 'Unknown'}
                                </small>
                            </div>
                            <span class="badge bg-success">Attended</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Update organized events count
        document.getElementById('eventsOrganized').textContent = data.organized_events.length;
        
    } catch (error) {
        console.error('Error loading my events:', error);
        document.getElementById('organizedEvents').innerHTML = '<p class="text-danger">Failed to load organized events</p>';
        document.getElementById('attendedEvents').innerHTML = '<p class="text-danger">Failed to load attended events</p>';
    }
}

function editProfile() {
    // Scroll to profile form
    document.getElementById('profileForm').scrollIntoView({ behavior: 'smooth' });
}

function editInterests() {
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
        displayInterests();
        showAlert('Interests updated successfully!', 'success');
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('interestsModal'));
        modal.hide();
        
    } catch (error) {
        console.error('Error updating interests:', error);
        showAlert('Failed to update interests', 'danger');
    }
}
</script>
@endpush
