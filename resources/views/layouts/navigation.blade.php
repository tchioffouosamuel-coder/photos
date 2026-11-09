<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo class="d-inline-block align-text-top" />
        </a>

        <!-- Hamburger Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                       href="{{ route('dashboard') }}">
                        {{ __('Dashboard') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('classes*') ? 'active' : '' }}" 
                       href="{{ route('classes.index') }}">
                        {{ __('Classes') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('statistics*') ? 'active' : '' }}" 
                       href="{{ route('statistics.index') }}">
                        {{ __('Statistiques') }}
                    </a>
                </li>
            </ul>

            <!-- Settings Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none dropdown-toggle text-secondary" 
                        type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i>{{ __('Profile') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>{{ __('Log Out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
    /* Navigation active link style */
    .nav-link {
        position: relative;
        transition: all 0.3s ease;
        padding: 0.5rem 1rem;
    }
    
    .nav-link.active {
        color: #0d6efd !important;
        font-weight: 500;
    }
    
    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 1rem;
        right: 1rem;
        height: 2px;
        background-color: #0d6efd;
    }
    
    .nav-link:hover {
        color: #0d6efd;
    }
    
    /* Dropdown styling */
    .dropdown-toggle::after {
        margin-left: 0.5rem;
    }
    
    .dropdown-item {
        transition: background-color 0.2s ease;
        padding: 0.5rem 1rem;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-item i {
        width: 20px;
        text-align: center;
    }
    
    .navbar {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .navbar {
            background-color: #2d2d2d !important;
            border-bottom-color: #404040 !important;
        }
        
        .navbar-light .navbar-nav .nav-link {
            color: #e5e5e5;
        }
        
        .navbar-light .navbar-nav .nav-link.active {
            color: #4d9cff !important;
        }
        
        .navbar-light .navbar-nav .nav-link.active::after {
            background-color: #4d9cff;
        }
        
        .navbar-light .navbar-nav .nav-link:hover {
            color: #4d9cff;
        }
        
        .btn-link {
            color: #e5e5e5 !important;
        }
        
        .dropdown-menu {
            background-color: #2d2d2d;
            border-color: #404040;
        }
        
        .dropdown-item {
            color: #e5e5e5;
        }
        
        .dropdown-item:hover {
            background-color: #404040;
        }
        
        .dropdown-divider {
            border-color: #404040;
        }
    }
</style>