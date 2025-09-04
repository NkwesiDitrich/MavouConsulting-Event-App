@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        My Profile
                    </h4>
                </div>
                <div class="card-body">
                    <form id="profileForm">
                        @csrf
                        
                        <!-- Profile Picture Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="profilePicture" src="" alt="Profile Picture" 
                                     class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                        style="width: 35px; height: 35px;" onclick="document.getElementById('profilePictureInput').click()">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" style="display: none;">
                            <div class="mt-2">
                                <small class="text-muted">Click the camera icon to change your profile picture</small>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" name="role" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="events_attended" class="form-label">Events Attended</label>
                                <input type="text" class="form-control" id="events_attended" name="events_attended" readonly>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                      placeholder="Tell us about yourself..."></textarea>
                        </div>

                        <!-- Social Links -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fab fa-linkedin"></i>
                                    </span>
                                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url" 
                                           placeholder="https://linkedin.com/in/username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="twitter_url" class="form-label">Twitter URL</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fab fa-twitter"></i>
                                    </span>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" 
                                           placeholder="https://twitter.com/username">
                                </div>
                            </div>
                        </div>

                        <!-- Interests Section -->
                        <div class="mb-3">
                            <label class="form-label">Interests</label>
                            <div class="row" id="interestsContainer">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Technology" id="interest-tech">
                                        <label class="form-check-label" for="interest-tech">Technology</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Business" id="interest-business">
                                        <label class="form-check-label" for="interest-business">Business</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Networking" id="interest-networking">
                                        <label class="form-check-label" for="interest-networking">Networking</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Education" id="interest-education">
                                        <label class="form-check-label" for="interest-education">Education</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Arts" id="interest-arts">
                                        <label class="form-check-label" for="interest-arts">Arts & Culture</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="Sports" id="interest-sports">
                                        <label class="form-check-label" for="interest-sports">Sports</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Change Password</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>
                                <small class="text-muted">Leave password fields empty if you don't want to change your password</small>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('member.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Dashboard
                            </a>
                            <div>
                                <button type="button" class="btn btn-danger me-2" onclick="deleteAccount()">
                                    <i class="fas fa-trash me-2"></i>
                                    Delete Account
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Update Profile
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
                <p>All your data including events, attendances, and profile information will be permanently deleted.</p>
                <div class="mb-3">
                    <label for="deletePassword" class="form-label">Enter your password to confirm:</label>
                    <input type="password" class="form-control" id="deletePassword" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                    <i class="fas fa-trash me-2"></i>
                    Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentUser = {};

document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
    
    // Handle profile picture change
    document.getElementById('profilePictureInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePicture').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle form submission
    document.getElementById('profileForm').addEventListener('submit', updateProfile);
});

async function loadProfile() {
    try {
        const response = await axios.get('/api/profile');
        currentUser = response.data.user;
        
        // Populate form fields
        document.getElementById('name').value = currentUser.name || '';
        document.getElementById('email').value = currentUser.email || '';
        document.getElementById('role').value = currentUser.role ? currentUser.role.charAt(0).toUpperCase() + currentUser.role.slice(1) : '';
        document.getElementById('events_attended').value = currentUser.events_attended || 0;
        document.getElementById('bio').value = currentUser.bio || '';
        document.getElementById('linkedin_url').value = currentUser.linkedin_url || '';
        document.getElementById('twitter_url').value = currentUser.twitter_url || '';
        
        // Set profile picture
        document.getElementById('profilePicture').src = currentUser.profile_picture || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(currentUser.name);
        
        // Set interests
        if (currentUser.interests && Array.isArray(currentUser.interests)) {
            currentUser.interests.forEach(interest => {
                const checkbox = document.querySelector(`input[value="${interest}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
        
    } catch (error) {
        console.error('Error loading profile:', error);
        showAlert('Failed to load profile data', 'danger');
    }
}

async function updateProfile(e) {
    e.preventDefault();
    
    const formData = new FormData();
    const form = document.getElementById('profileForm');
    
    // Add basic fields
    formData.append('name', document.getElementById('name').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('bio', document.getElementById('bio').value);
    formData.append('linkedin_url', document.getElementById('linkedin_url').value);
    formData.append('twitter_url', document.getElementById('twitter_url').value);
    
    // Add interests
    const interests = [];
    document.querySelectorAll('#interestsContainer input[type="checkbox"]:checked').forEach(checkbox => {
        interests.push(checkbox.value);
    });
    formData.append('interests', JSON.stringify(interests));
    
    // Add profile picture if changed
    const profilePictureInput = document.getElementById('profilePictureInput');
    if (profilePictureInput.files[0]) {
        formData.append('profile_picture', profilePictureInput.files[0]);
    }
    
    // Add password fields if provided
    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    
    if (newPassword) {
        if (!currentPassword) {
            showAlert('Current password is required to change password', 'danger');
            return;
        }
        if (newPassword !== passwordConfirmation) {
            showAlert('New password and confirmation do not match', 'danger');
            return;
        }
        formData.append('current_password', currentPassword);
        formData.append('password', newPassword);
        formData.append('password_confirmation', passwordConfirmation);
    }
    
    try {
        const response = await axios.put('/api/profile', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        showAlert('Profile updated successfully!', 'success');
        
        // Clear password fields
        document.getElementById('current_password').value = '';
        document.getElementById('password').value = '';
        document.getElementById('password_confirmation').value = '';
        
        // Reload profile data
        setTimeout(() => {
            loadProfile();
        }, 1000);
        
    } catch (error) {
        console.error('Error updating profile:', error);
        
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
            showAlert('Failed to update profile. Please try again.', 'danger');
        }
    }
}

function deleteAccount() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
    modal.show();
}

async function confirmDeleteAccount() {
    const password = document.getElementById('deletePassword').value;
    
    if (!password) {
        showAlert('Password is required to delete account', 'danger');
        return;
    }
    
    try {
        await axios.delete('/api/profile', {
            data: { password: password }
        });
        
        showAlert('Account deleted successfully. You will be redirected to the home page.', 'success');
        
        // Clear local storage and redirect
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        
        setTimeout(() => {
            window.location.href = '/';
        }, 2000);
        
    } catch (error) {
        console.error('Error deleting account:', error);
        
        if (error.response && error.response.data.message) {
            showAlert(error.response.data.message, 'danger');
        } else {
            showAlert('Failed to delete account. Please try again.', 'danger');
        }
    }
}
</script>
@endpush

