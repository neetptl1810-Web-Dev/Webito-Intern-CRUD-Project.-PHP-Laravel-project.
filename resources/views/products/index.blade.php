<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello World - Browse Products</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">
    
    {{-- ✅ Only load page-specific CSS --}}
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    {{-- Animated Background --}}
    <div class="np-particles">
        <div class="np-particle"></div>
        <div class="np-particle"></div>
        <div class="np-particle"></div>
        <div class="np-particle"></div>
        <div class="np-particle"></div>
    </div>

    {{-- Hero Header --}}
    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">🛍️ Hello World</h1>
            <p class="header-subtitle">Discover amazing products from trusted companies</p>
            
            {{-- Search Bar --}}
            <form method="GET" action="{{ route('products.page') }}" class="search-form">
                <div class="search-input-wrapper">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text"
                           name="search"
                           id="search"
                           placeholder="Search Companies to buy their products..."
                           value="{{ request('search') }}"
                           class="search-input"
                           autocomplete="off">
                </div>
                <button type="submit" class="btn-search">
                    <span>Search</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('products.page') }}" class="btn-clear">Clear</a>
                @endif
            </form>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="main-content">
        <div class="container">
            {{-- Search Results Info --}}
            @if(request('search'))
                <div class="search-results-info">
                    <span class="results-badge">🔍</span>
                    <span class="results-text">
                        Found <strong>{{ $companies->count() }}</strong> 
                        {{ Str::plural('company', $companies->count()) }} 
                        for "<strong>{{ request('search') }}</strong>"
                    </span>
                </div>
            @endif

            {{-- Empty State --}}
            @if($companies->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 3h18v18H3z"/>
                            <path d="M7 7h10v10H7z"/>
                            <path d="M9 9h6v6H9z"/>
                        </svg>
                    </div>
                    @if(request('search'))
                        <h3>No companies found</h3>
                        <p>Try adjusting your search terms or browse all companies.</p>
                        <a href="{{ route('products.page') }}" class="btn-primary">
                            View All Companies
                        </a>
                    @else
                        <h3>Welcome to {{ config('app.name', 'ShopHub') }}!</h3>
                        <p>Companies will appear here once they start adding products.</p>
                        <a href="{{ route('company.index') }}" class="btn-primary">
                            Add Your Company
                        </a>
                    @endif
                </div>
            @else
                {{-- Section Title --}}
                @if(!request('search'))
                    <div class="section-header">
                        <h2>🏢 Featured Companies</h2>
                        <p>Explore products from our trusted partners</p>
                    </div>
                @endif

                {{-- Companies Grid --}}
                <div class="companies-grid">
                    @foreach($companies as $comp)
                        <a href="{{ route('products.company', $comp) }}" class="company-card">
                            <div class="card-badge">{{ $comp->products_count }} Products</div>
                            
                            <div class="card-content">
                                <h3 class="card-title">{{ $comp->name }}</h3>
                                <p class="card-description">{{ Str::limit($comp->description, 120) }}</p>
                                
                                <div class="card-footer">
                                    <span class="card-tag">🛍️ Shop Now</span>
                                    <span class="card-arrow">→</span>
                                </div>
                            </div>
                            
                            <div class="card-glow"></div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($companies->hasPages())
                  <div class="pagination-wrapper">
                    {{ $companies->appends(['search' => request('search')])->links('pagination::simple-bootstrap-4') }}
                  </div>
                @endif
            @endif
        </div>
    </main>

    {{-- Footer --}}
    <footer class="page-footer">
        <div class="footer-content">
            <p>© {{ date('Y') }} {{ config('app.name', 'ShopHub') }}. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact</a>
            </div>
        </div>
    </footer>

    {{-- Page-specific JavaScript --}}
    <script src="{{ asset('js/products.js') }}"></script>
</body>
</html>