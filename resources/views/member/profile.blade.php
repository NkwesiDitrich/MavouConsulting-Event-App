@extends('layouts.app')

@section('title', 'Member Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Profile Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="profile-picture-container position-relative">
                                <img id="profilePicture" src="" alt="Profile Picture" 
                                     class="rounded-circle img-fluid profile-picture" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                        onclick="document.getElementById('profilePictureInput').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h2 id="profileName">Loading...</h2>
                            <p class="text-muted mb-2" id="profileEmail">Loading...</p>
                            <p class="mb-3" id="profileBio">Loading...</p>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary" onclick="showEditProfileModal()">
                                    <i class="fas fa-edit me-2"></i>Edit Profile
                                </button>
                                <button class="btn btn-outline-secondary" onclick="showChangePasswordModal()">
                                    <i class="fas fa-lock me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Stats -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary" id="organizedEventsCount">0</h3>
                            <p class="mb-0">Events Organized</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success" id="attendedEventsCount">0</h3>
                            <p class="mb-0">Events Attended</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info">4.8</h3>
                            <p class="mb-0">Average Rating</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Events Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="eventTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="organized-tab" data-bs-toggle="tab" 
                                    data-bs-target="#organized" type="button" role="tab">
                                <i class="fas fa-calendar-plus me-2"></i>Organized Events
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attended-tab" data-bs-toggle="tab" 
                                    data-bs-target="#attended" type="button" role="tab">
                                <i class="fas fa-calendar-check me-2"></i>Attended Events
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="eventTabsContent">
                        <!-- Organized Events Tab -->
                        <div class="tab-pane fade show active" id="organized" role="tabpanel">
                            <div id="organizedEventsContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading organized events...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attended Events Tab -->
                        <div class="tab-pane fade" id="attended" role="tabpanel">
                            <div id="attendedEventsContainer">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading attended events...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editName" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editEmail" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="editEmail" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editPhone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="editPhone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editProfilePicture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="editProfilePicture" name="profile_picture" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif">
                            <div class="form-text">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF</div>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editBio" class="form-label">Bio</label>
                        <textarea class="form-control" id="editBio" name="bio" rows="4" 
                                  placeholder="Tell us about yourself..."></textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Profile Picture Preview -->
                    <div id="profilePicturePreview" class="mb-3" style="display: none;">
                        <label class="form-label">Profile Picture Preview</label>
                        <div class="text-center">
                            <img id="previewImage" src="" alt="Preview" class="rounded-circle" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">
                        <span class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password *</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password *</label>
                        <input type="password" class="form-control" id="confirmPassword" name="new_password_confirmation" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="changePasswordBtn">
                        <span class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden file input for profile picture -->
<input type="file" id="profilePictureInput" style="display: none;" accept="image/jpeg,image/png,image/jpg,image/gif">

@endsection

@section('scripts')
<script>
// Global variables
let currentUser = null;
let profileData = null;

// Initialize profile page
document.addEventListener('DOMContentLoaded', function() {
    loadProfileData();
    setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Profile picture input change
    document.getElementById('profilePictureInput').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            uploadProfilePicture(e.target.files[0]);
        }
    });

    // Edit profile picture input change
    document.getElementById('editProfilePicture').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            previewProfilePicture(e.target.files[0]);
        }
    });

    // Edit profile form submit
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });

    // Change password form submit
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        changePassword();
    });

    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', function() {
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = this.value;
        
        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });
}

/**
 * Load profile data from API
 */
function loadProfileData() {
    fetch('/web-api/member/profile', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            profileData = data;
            currentUser = data.user;
            updateProfileUI(data);
        } else {
            showError('Failed to load profile data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Profile loading error:', error);
        showError('Failed to load profile data. Please refresh the page.');
    });
}

/**
 * Update profile UI with loaded data
 */
function updateProfileUI(data) {
    try {
        // Update profile header
        document.getElementById('profileName').textContent = data.user.name || 'User';
        document.getElementById('profileEmail').textContent = data.user.email || '';
        document.getElementById('profileBio').textContent = data.user.bio || 'No bio available';
        document.getElementById('profilePicture').src = data.user.profile_picture || '/images/default-avatar.png';

        // Update stats
        document.getElementById('organizedEventsCount').textContent = data.organized_events?.length || 0;
        document.getElementById('attendedEventsCount').textContent = data.attended_events?.length || 0;

        // Update events
        updateOrganizedEvents(data.organized_events || []);
        updateAttendedEvents(data.attended_events || []);

        console.log('Profile updated successfully');
    } catch (error) {
        console.error('Error updating profile UI:', error);
        showError('Error displaying profile data');
    }
}

/**
 * Update organized events section
 */
function updateOrganizedEvents(events) {
    const container = document.getElementById('organizedEventsContainer');

    if (events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No events organized yet</h6>
                <p class="text-muted mb-3">Start organizing events to build your community!</p>
                <a href="/member/events/create" class="btn btn-primary">Create Your First Event</a>
            </div>
        `;
        return;
    }

    let eventsHtml = '<div class="row">';
    events.forEach(event => {
        const startDate = new Date(event.start_time);
        const formattedDate = startDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        eventsHtml += `
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">${escapeHtml(event.name)}</h6>
                        <p class="card-text text-muted small">${escapeHtml(event.description?.substring(0, 100) || '')}...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                            </small>
                            <div>
                                <a href="/member/events/${event.id}/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="/events/${event.id}" class="btn btn-sm btn-outline-secondary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    eventsHtml += '</div>';

    container.innerHTML = eventsHtml;
}

/**
 * Update attended events section
 */
function updateAttendedEvents(events) {
    const container = document.getElementById('attendedEventsContainer');

    if (events.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No events attended yet</h6>
                <p class="text-muted mb-3">Discover and join exciting events in your area!</p>
                <a href="/events" class="btn btn-primary">Browse Events</a>
            </div>
        `;
        return;
    }

    let eventsHtml = '<div class="row">';
    events.forEach(event => {
        const startDate = new Date(event.start_time);
        const formattedDate = startDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        eventsHtml += `
            <div class="col-md-6 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">${escapeHtml(event.name)}</h6>
                        <p class="card-text text-muted small">${escapeHtml(event.description?.substring(0, 100) || '')}...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                            </small>
                            <div>
                                <span class="badge bg-success">Attended</span>
                                <a href="/events/${event.id}" class="btn btn-sm btn-outline-secondary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    eventsHtml += '</div>';

    container.innerHTML = eventsHtml;
}

/**
 * Show edit profile modal
 */
function showEditProfileModal() {
    if (!currentUser) {
        showError('Profile data not loaded');
        return;
    }

    // Populate form with current data
    document.getElementById('editName').value = currentUser.name || '';
    document.getElementById('editEmail').value = currentUser.email || '';
    document.getElementById('editPhone').value = currentUser.phone || '';
    document.getElementById('editBio').value = currentUser.bio || '';

    // Clear previous validation states
    clearFormValidation('editProfileForm');

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

/**
 * Show change password modal
 */
function showChangePasswordModal() {
    // Clear form
    document.getElementById('changePasswordForm').reset();
    clearFormValidation('changePasswordForm');

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

/**
 * Preview profile picture before upload
 */
function previewProfilePicture(file) {
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
        showError('File size must be less than 2MB');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImage').src = e.target.result;
        document.getElementById('profilePicturePreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

/**
 * Upload profile picture directly
 */
function uploadProfilePicture(file) {
    if (file.size > 2 * 1024 * 1024) { // 2MB limit
        showError('File size must be less than 2MB');
        return;
    }

    const formData = new FormData();
    formData.append('profile_picture', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch('/web-api/member/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Profile picture updated successfully!');
            document.getElementById('profilePicture').src = data.user.profile_picture;
            currentUser.profile_picture = data.user.profile_picture;
        } else {
            showError(data.message || 'Failed to update profile picture');
        }
    })
    .catch(error => {
        console.error('Profile picture upload error:', error);
        showError('Failed to update profile picture. Please try again.');
    });
}

/**
 * Update profile
 */
function updateProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveProfileBtn');
    const spinner = saveBtn.querySelector('.spinner-border');

    // Clear previous validation
    clearFormValidation('editProfileForm');

    // Show loading state
    saveBtn.disabled = true;
    spinner.style.display = 'inline-block';

    fetch('/web-api/member/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Profile updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
            
            // Update current user data
            currentUser = { ...currentUser, ...data.user };
            
            // Update UI
            document.getElementById('profileName').textContent = currentUser.name;
            document.getElementById('profileEmail').textContent = currentUser.email;
            document.getElementById('profileBio').textContent = currentUser.bio || 'No bio available';
            if (data.user.profile_picture) {
                document.getElementById('profilePicture').src = data.user.profile_picture;
            }
        } else {
            if (data.errors) {
                displayFormErrors('editProfileForm', data.errors);
            } else {
                showError(data.message || 'Failed to update profile');
            }
        }
    })
    .catch(error => {
        console.error('Profile update error:', error);
        showError('Failed to update profile. Please try again.');
    })
    .finally(() => {
        saveBtn.disabled = false;
        spinner.style.display = 'none';
    });
}

/**
 * Change password
 */
function changePassword() {
    const form = document.getElementById('changePasswordForm');
    const formData = new FormData(form);
    const changeBtn = document.getElementById('changePasswordBtn');
    const spinner = changeBtn.querySelector('.spinner-border');

    // Clear previous validation
    clearFormValidation('changePasswordForm');

    // Validate password confirmation
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        document.getElementById('confirmPassword').classList.add('is-invalid');
        document.getElementById('confirmPassword').nextElementSibling.textContent = 'Passwords do not match';
        return;
    }

    // Show loading state
    changeBtn.disabled = true;
    spinner.style.display = 'inline-block';

    fetch('/web-api/member/profile/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Password changed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
            form.reset();
        } else {
            if (data.errors) {
                displayFormErrors('changePasswordForm', data.errors);
            } else {
                showError(data.message || 'Failed to change password');
            }
        }
    })
    .catch(error => {
        console.error('Password change error:', error);
        showError('Failed to change password. Please try again.');
    })
    .finally(() => {
        changeBtn.disabled = false;
        spinner.style.display = 'none';
    });
}

/**
 * Clear form validation states
 */
function clearFormValidation(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('.form-control');
    const feedbacks = form.querySelectorAll('.invalid-feedback');

    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
    });

    feedbacks.forEach(feedback => {
        feedback.textContent = '';
    });
}

/**
 * Display form validation errors
 */
function displayFormErrors(formId, errors) {
    const form = document.getElementById(formId);

    Object.keys(errors).forEach(fieldName => {
        const input = form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = errors[fieldName][0];
            }
        }
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
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert-success');
        if (alert) alert.remove();
    }, 5000);
}

/**
 * Show error message
 */
function showError(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert-danger');
        if (alert) alert.remove();
    }, 8000);
}
</script>

<style>
.profile-picture-container {
    display: inline-block;
}

.profile-picture {
    border: 4px solid #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background-color: transparent;
    border-bottom: 2px solid #007bff;
    color: #007bff;
}

.modal-lg {
    max-width: 800px;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn {
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
}
</style>
@endsection
