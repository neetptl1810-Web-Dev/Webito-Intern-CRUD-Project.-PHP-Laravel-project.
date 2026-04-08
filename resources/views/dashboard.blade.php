<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Dashboard - Company Management System">

    <title>Dashboard | {{ auth()->user()->name }} - Company Management</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">

    {{-- Stylesheet - Dashboard Profile Only --}}
    <link rel="stylesheet" href="{{ asset('css/dashboard-profile.css') }}">

    {{-- FontAwesome Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="np-wind-orb np-wind-orb-1"></div>
    <div class="np-wind-orb np-wind-orb-2"></div>
    <div class="np-wind-orb np-wind-orb-3"></div>
    <div class="dashboard-wrapper">

        {{-- Page Header --}}
        <header class="dashboard-header">
            <h1>{{ auth()->user()->name }}'s Dashboard</h1>
            <p class="text-muted">Welcome! Manage your companies and products.</p>
        </header>

        {{-- Navigation --}}
        <nav class="navbar">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/company" class="nav-link {{ request()->is('company*') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i> Add Your Company
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/products" class="nav-link {{ request()->is('products*') ? 'active' : '' }}">
                        <i class="fas fa-boxes"></i> View Products
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Main Content --}}
        <main class="dashboard-content">

            {{-- Profile/Experience Card --}}
            <section class="dashboard-card">
                <div class="np-info-card">
                    <h6><i class="fas fa-briefcase"></i> Webito Internship Program (WIP)</h6>
                    <span class="np-role-badge">Full Stack Website Developer</span>
                    <span class="np-date"><i class="far fa-clock"></i> January 2026 - March 2026</span>

                    <ul class="np-achievement-list">
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Laravel Web Application:</strong> Built a scalable web app using Laravel 12 with MVC
                            architecture and Blade templating.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Secure Authentication:</strong> Implemented secure user registration, login, and
                            session management with password hashing.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Role-Based Access Control:</strong> Developed custom middleware to restrict access
                            based on user roles (Admin/User).
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Admin & User Dashboards:</strong> Created intuitive dashboards with full CRUD
                            functionality for managing companies and products.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>MySQL Database Integration:</strong> Designed efficient database schema and
                            implemented Eloquent ORM relationships.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>E-Commerce Platform:</strong> Built a module where authenticated users can register
                            their company and manage business listings.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Responsive Frontend:</strong> Developed a mobile-first, responsive UI using
                            Bootstrap 5 for seamless cross-device experience.
                        </li>
                    </ul>
                </div>
            </section>

            {{-- Welcome Message & Actions --}}
            <section class="dashboard-card">
                <div class="welcome-message">
                    <h3><i class="fas fa-hand-wave"></i> Welcome, {{ auth()->user()->name }}!</h3>
                    <p>You are successfully logged in. Start managing your companies and products below.</p>
                </div>

                <div class="action-buttons">
                    <a href="/company" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Company
                    </a>
                    <a href="/products" class="btn btn-outline-secondary">
                        <i class="fas fa-eye"></i> View All Products
                    </a>
                </div>
            </section>

            {{-- Logout Section --}}
            <section class="dashboard-card">
                <form method="POST" action="/logout" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </section>

        </main>

        {{-- Footer --}}
        <footer class="dashboard-footer">
            <p>&copy; {{ date('Y') }} Company Management System. All rights reserved.</p>
        </footer>

    </div>

    {{-- Optional: Inline Script for Page-Specific Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Add active class to current nav item
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>

</html>