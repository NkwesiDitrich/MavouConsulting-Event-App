<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EventHub - Discover Amazing Events</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .event-card {
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">
                <i class="fas fa-calendar-alt me-2"></i>EventHub
            </a>
            
            <div class="navbar-nav ms-auto">
                @auth
                    <a href="{{ url('/member/dashboard') }}" class="btn btn-outline-primary me-2">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                        Login
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            Register
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Discover Amazing Events Near You</h1>
                    <p class="lead mb-4">Connect with like-minded people, learn new skills, and create unforgettable memories at events tailored to your interests.</p>
                    <div class="d-flex gap-3">
                        @auth
                            <a href="{{ url('/member/dashboard') }}" class="btn btn-light btn-lg">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg">
                                Get Started
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg">
                                Sign In
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://via.placeholder.com/500x300/ffffff/667eea?text=Event+Hub" 
                         alt="Event Hub" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-center mb-3">Featured Events</h2>
                    <p class="text-center text-muted">Discover some of our most popular upcoming events</p>
                </div>
            </div>

            <div class="row" id="featuredEvents">
                <!-- Events will be loaded here via JavaScript -->
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 text-center">
                    <a href="{{ route('events.browse') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>
                        Browse All Events
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h2>Why Choose EventHub?</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4>Connect</h4>
                    <p>Meet people who share your interests and passions</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                    <h4>Learn</h4>
                    <p>Discover new skills and knowledge from experts</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h4>Experience</h4>
                    <p>Create unforgettable memories at amazing events</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>EventHub</h5>
                    <p>Your gateway to amazing events and experiences</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2024 EventHub. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Axios for API calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadFeaturedEvents();
        });

        async function loadFeaturedEvents() {
            try {
                console.log('Loading featured events...');
                const response = await axios.get('/api/public/events/featured');
                console.log('Featured events response:', response.data);
                
                const events = response.data.featured_events || response.data.events || [];
                
                const container = document.getElementById('featuredEvents');
                container.innerHTML = '';
                
                if (events.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-muted">No featured events available at the moment.</p>
                            <p class="text-muted">Check back soon for exciting upcoming events!</p>
                        </div>
                    `;
                    return;
                }
                
                // Show only first 6 events for featured section
                const featuredEvents = events.slice(0, 6);
                
                featuredEvents.forEach(event => {
                    const eventCard = createEventCard(event);
                    container.appendChild(eventCard);
                });
                
            } catch (error) {
                console.error('Error loading featured events:', error);
                document.getElementById('featuredEvents').innerHTML = `
                    <div class="col-12 text-center">
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Unable to load featured events at the moment.
                            <br>
                            <small class="text-muted">Please try refreshing the page or check back later.</small>
                        </div>
                    </div>
                `;
            }
        }

        function createEventCard(event) {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4 mb-4';
            
            const formattedDate = event.start_time ? 
                new Date(event.start_time).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'Date TBA';
                
            const truncatedDescription = event.description ? 
                (event.description.length > 100 ? event.description.substring(0, 100) + '...' : event.description) : 
                'No description available';
            
            const categoryName = event.category?.name || event.category || 'General';
            const organizerName = event.organizer?.name || event.organizer || 'Unknown';
            const location = event.location || 'Online';
            
            col.innerHTML = `
                <div class="card event-card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="https://via.placeholder.com/300x200/667eea/ffffff?text=${encodeURIComponent(event.name || 'Event')}" 
                             class="card-img-top" alt="${event.name || 'Event'}" style="height: 200px; object-fit: cover;">
                        <span class="category-badge badge bg-primary">${categoryName}</span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${event.name || 'Untitled Event'}</h5>
                        <p class="card-text text-muted small">${truncatedDescription}</p>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>${formattedDate}
                            </small>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>${location}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">by ${organizerName}</small>
                            <a href="/events/${event.id}" class="btn btn-sm btn-outline-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            return col;
        }
    </script>
</body>
</html>

