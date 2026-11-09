<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AC-ID') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #4f46e5 !important;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            background: white !important;
        }
        
        /* Navigation active link */
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            color: #6b7280 !important;
            font-weight: 500;
        }
        
        .nav-link.active {
            color: #4f46e5 !important;
        }
        
        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 1rem;
            right: 1rem;
            height: 3px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 2px 2px 0 0;
        }
        
        .nav-link:hover {
            color: #4f46e5 !important;
        }
        
        /* Dropdown styling */
        .dropdown-toggle {
            color: #6b7280 !important;
            font-weight: 500;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        
        .dropdown-menu {
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            margin-top: 0.5rem !important;
        }
        
        .dropdown-item {
            transition: background-color 0.2s ease;
            padding: 0.75rem 1.25rem;
            color: #374151;
        }
        
        .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #4f46e5;
        }
        
        .dropdown-item i {
            width: 24px;
            text-align: center;
            color: #6b7280;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #e5e7eb;
        }
        
        /* Toast Animation */
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        
        .toast-container-custom {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1060;
        }
        
        /* Main content area */
        main {
            min-height: calc(100vh - 64px);
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                padding-top: 1rem;
            }
            
            .nav-link.active::after {
                display: none;
            }
            
            .nav-link.active {
                background-color: #f3f4f6;
                border-radius: 0.375rem;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container-custom"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                {{ config('app.name', 'AC-ID') }}
            </a>

            <!-- Hamburger Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" 
                           href="{{ route('dashboard') }}">
                            <i class="fas fa-chart-line me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('classes*') ? 'active' : '' }}" 
                           href="{{ route('classes.index') }}">
                            <i class="fas fa-school me-1"></i>
                            Classes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('import*') ? 'active' : '' }}" 
                           href="{{ route('import.index') }}">
                            <i class="fas fa-file-excel me-1"></i>
                            Importer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('cards*') ? 'active' : '' }}" 
                           href="{{ route('cards.index') }}">
                            <i class="fas fa-id-card me-1"></i>
                            Cartes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('statistics*') ? 'active' : '' }}" 
                           href="{{ route('statistics.index') }}">
                            <i class="fas fa-chart-bar me-1"></i>
                            Statistiques
                        </a>
                    </li>
                </ul>

                <!-- Settings Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none dropdown-toggle" 
                            type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        {{ Auth::user()->name ?? 'Utilisateur' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i>
                                Mon Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toast Notification Script -->
    <script>
        function showNotification(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');

            const bgColor = type === 'success' ? 'bg-success' :
                type === 'error' ? 'bg-danger' :
                type === 'warning' ? 'bg-warning' : 'bg-info';

            const icon = type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

            toast.className = `alert ${bgColor} text-white d-flex align-items-center gap-3 animate-slide-in shadow-lg`;
            toast.style.maxWidth = '400px';
            toast.style.minWidth = '300px';
            toast.innerHTML = `
                <i class="fas ${icon} fs-4"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.remove()"></button>
            `;

            container.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Display Laravel session messages
        @if(session('success'))
            showNotification('{{ session('success') }}', 'success');
        @endif

        @if(session('error'))
            showNotification('{{ session('error') }}', 'error');
        @endif

        @if(session('warning'))
            showNotification('{{ session('warning') }}', 'warning');
        @endif

        @if(session('info'))
            showNotification('{{ session('info') }}', 'info');
        @endif
    </script>
</body>
</html>