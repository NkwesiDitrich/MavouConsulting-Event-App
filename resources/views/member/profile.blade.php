@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Profile Header -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $user->profile_picture ? Storage::url($user->profile_picture) : '/images/default-avatar.png' }}" 
                             alt="Profile Picture" 
                             class="rounded-circle" 
                             width="120" 
                             height="120" 
                             style="object-fit: cover;"
                             id="profilePreview">
                        <button type="button" class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle" 
                                onclick="document.getElementById('profilePictureInput').click()" 
                                style="width: 35px; height: 35px;">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    @if($user->bio)
                        <p class="text-muted">{{ $user->bio }}</p>
                    @endif
                </div>
            </div>

            <!-- Profile Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        Update Profile
                    </h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Hidden file input -->
                        <input type="file" 
                               id="profilePictureInput" 
                               name="profile_picture" 
                               accept="image/*" 
                               style="display: none;" 
                               onchange="previewProfilePicture(this)">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ $user->name }}" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="{{ $user->email }}" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ $user->phone }}">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" 
                                      id="bio" 
                                      name="bio" 
                                      rows="4" 
                                      maxlength="1000" 
                                      placeholder="Tell us about yourself...">{{ $user->bio }}</textarea>
                            <div class="form-text">
                                <span id="bioCount">{{ strlen($user->bio ?? '') }}</span>/1000 characters
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Password Change Section -->
                        <hr>
                        <h6 class="mb-3">
                            <i class="fas fa-lock me-2"></i>
                            Change Password (Optional)
                        </h6>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="current_password" 
                                   name="current_password">
                            <div class="form-text">Required only if you want to change your password</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password" 
                                       name="new_password" 
                                       minlength="8">
                                <div class="form-text">Minimum 8 characters</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('member.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary" id="updateBtn">
                                <i class="fas fa-save me-1"></i>
                                Update Profile
                            </button>
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
    const form = document.getElementById('profileForm');
    const bioTextarea = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');
    const updateBtn = document.getElementById('updateBtn');

    // Bio character counter
    bioTextarea.addEventListener('input', function() {
        bioCount.textContent = this.value.length;
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous validation errors
        clearValidationErrors();
        
        // Show loading state
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';
        
        try {
            // Create FormData object to handle file upload
            const formData = new FormData(form);
            
            const response = await axios.post('/member/profile', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.data.success) {
                showAlert(response.data.message, 'success');
                
                // Update the profile preview if new data is available
                if (response.data.user) {
                    const user = response.data.user;
                    
                    // Update profile picture if changed
                    if (user.profile_picture_url) {
                        document.getElementById('profilePreview').src = user.profile_picture_url;
                    }
                    
                    // Clear password fields
                    document.getElementById('current_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('new_password_confirmation').value = '';
                }
            } else {
                throw new Error(response.data.message || 'Update failed');
            }
            
        } catch (error) {
            console.error('Profile update error:', error);
            
            if (error.response && error.response.status === 422) {
                // Validation errors
                const errors = error.response.data.errors;
                displayValidationErrors(errors);
                showAlert('Please fix the validation errors below', 'danger');
            } else {
                showAlert(error.response?.data?.message || 'Failed to update profile', 'danger');
            }
        } finally {
            // Reset button state
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Profile';
        }
    });
});

function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showAlert('Please select a valid image file', 'danger');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showAlert('Image size must be less than 2MB', 'danger');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function clearValidationErrors() {
    // Remove validation classes and error messages
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
}

function displayValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = errors[field][0];
            }
        }
    });
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
