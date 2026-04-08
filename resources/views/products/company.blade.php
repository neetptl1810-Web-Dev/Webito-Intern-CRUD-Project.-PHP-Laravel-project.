<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $company->name }} - Products</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('internicon.ico') }}">

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">

  {{-- ✅ Standalone CSS - No style.css dependency --}}
  <link rel="stylesheet" href="{{ asset('css/product-page.css') }}">

  {{-- Meta for CSRF token --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
  {{-- Animated Background --}}
  <div class="ec-particles">
    <div class="ec-particle"></div>
    <div class="ec-particle"></div>
    <div class="ec-particle"></div>
    <div class="ec-particle"></div>
    <div class="ec-particle"></div>
  </div>

  {{-- Header with User Profile --}}
<header class="ec-header">
    <div class="header-container">
        {{-- Logo/Back Link --}}
        <a href="{{ route('products.page') }}" class="header-logo">
            ← Back to Companies
        </a>

        {{-- User Profile Dropdown - CUSTOMER ONLY --}}
        <div class="user-profile" id="userProfile">
            @auth('customer')
                {{-- ✅ Customer is logged in --}}
                <div class="profile-avatar" onclick="toggleProfileMenu()">
                    @if(Auth::guard('customer')->user()?->avatar)
                        <img src="{{ asset('storage/' . Auth::guard('customer')->user()->avatar) }}" alt="Profile">
                    @else
                        <span>{{ substr(Auth::guard('customer')->user()->name, 0, 1) }}</span>
                    @endif
                </div>

                <div class="profile-menu" id="profileMenu">
                    <div class="profile-header">
                        <h4>{{ Auth::guard('customer')->user()->name }}</h4>
                        <p>{{ Auth::guard('customer')->user()->email }}</p>
                    </div>

                    {{-- ✅ ADD THIS: View Cart Link --}}
                    <a href="#" onclick="openCartModal(); return false;" class="profile-cart-link">
                        🛒 View Cart
                        <span id="cart-count-badge" class="cart-badge">0</span>
                    </a>

                    <a href="#" onclick="openProfileModal(); return false;">Edit Profile</a>
                    <a href="{{ route('customer.logout') }}"
                       onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                        Logout
                    </a>
                    <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>

            @else
                {{-- ✅ Not logged in - Show Login Button --}}
                <button class="btn-login" onclick="openAuthModal('login'); return false;">Login / Register</button>
            @endauth
        </div>
    </div>
</header>

  {{-- Company Hero Section --}}
  <section class="company-hero">
    <div class="hero-content">
      <h1>🏢 {{ $company->name }}</h1>
      <p>{{ $company->description ?? 'Explore our amazing collection of products.' }}</p>
    </div>
  </section>

  {{-- Main Content --}}
  <main class="main-content">
    <div class="container">
      @if($products->isEmpty())
        <div class="empty-state">
          <div class="empty-icon">📦</div>
          <h3>No Products Yet</h3>
          <p>This company hasn't added any products yet.</p>
          <a href="{{ route('products.page') }}" class="btn-primary">Browse Other Companies</a>
        </div>
      @else
        <div class="products-grid">
          @foreach($products as $product)
            <article class="product-card" data-product-id="{{ $product->id }}">
              <div class="product-image">
                {{-- ✅ InfinityFree-compatible image path --}}
@if($product->image)
    <img src="{{ asset('storage/products/' . basename($product->image)) }}"
         alt="{{ $product->name }}"
         class="product-image"
         onerror="this.src='{{ asset('images/no-image.png') }}'">
@else
    <img src="{{ asset('images/no-image.png') }}"
         alt="No image"
         class="product-image">
@endif

                {{-- Stock Status Badge --}}
                @if($product->stock_status === 'out_of_stock')
                  <span class="stock-badge out-of-stock">❌ Out of Stock</span>
                @elseif($product->stock_status === 'low_stock')
                  <span class="stock-badge low-stock">⚠️ Low Stock</span>
                @else
                  <span class="stock-badge in-stock">✅ In Stock</span>
                @endif
              </div>

              <div class="product-info">
                <h3>{{ $product->name }}</h3>
                <span class="product-type">{{ $product->type }}</span>

                @if($product->price)
                  <span class="product-price">Rs {{ number_format($product->price, 2) }}</span>
                @endif

                <p class="product-description">{{ Str::limit($product->description, 100) }}</p>

                <div class="product-actions">
                  <button class="btn-about"
                    onclick="openProductAbout({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->description) }}', '{{ $product->type }}', {{ $product->price ?? 'null' }})">
                    ℹ️ About
                  </button>

                  @if($product->stock_status !== 'out_of_stock')
                    <button class="btn-buy"
                      onclick="openBuyModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price ?? 'null' }})">
                      💰 Buy Now
                    </button>
                  @else
                    <button class="btn-buy disabled" disabled>
                      ❌ Out of Stock
                    </button>
                  @endif
                </div>
              </div>
            </article>
          @endforeach
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
          <div class="pagination-wrapper">
            {{ $products->links('pagination::simple-bootstrap-4') }}
          </div>
        @endif
      @endif
    </div>
  </main>

  {{-- Footer --}}
  <footer class="ec-footer">
    <div class="footer-content">
      <p>© {{ date('Y') }} {{ $company->name }}. All rights reserved.</p>
    </div>
  </footer>

  {{-- =========================================
  MODALS
  ========================================= --}}

  {{-- Product About Modal --}}
  <div id="productAboutModal" class="modal-overlay">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h3>ℹ️ Product Details</h3>
        <button class="modal-close" onclick="closeProductAboutModal()">&times;</button>
      </div>
      <div id="productAboutContent" class="modal-body">
        <!-- Content loaded via JS -->
      </div>
    </div>
  </div>

  {{-- Buy Product Modal --}}
  <div id="buyProductModal" class="modal-overlay">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h3>💰 Buy Product</h3>
        <button class="modal-close" onclick="closeBuyProductModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div id="buyProductContent">
          <!-- Content loaded via JS -->
        </div>
      </div>
    </div>
  </div>

  {{-- Authentication Modal --}}
  <div id="authModal" class="modal-overlay">
    <div class="modal-content auth-modal">
      <div class="modal-header">
        <h3 id="authModalTitle">Login</h3>
        <button class="modal-close" onclick="closeAuthModal()">&times;</button>
      </div>
      <div class="modal-body">
        <form id="authForm" method="POST">
          @csrf
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
          </div>
          <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
          </div>
          <button type="submit" class="btn-primary">Login</button>
          <p class="auth-switch">
            Don't have an account?
            <a href="#" onclick="switchAuthMode('register')">Register</a>
          </p>
        </form>
      </div>
    </div>
  </div>

  {{-- Profile Modal --}}
  <div id="profileModal" class="modal-overlay">
    <div class="modal-content modal-lg">
      <div class="modal-header">
        <h3>👤 Edit Profile</h3>
        <button class="modal-close" onclick="closeProfileModal()">&times;</button>
      </div>
      <div class="modal-body">
        {{-- Profile Modal Form --}}
{{-- ✅ FIXED - No @method('PUT'), just use POST --}}
<form id="profileForm" method="POST" enctype="multipart/form-data"
      action="{{ Auth::guard('customer')->check() ? '/customer/profile/update' : '/admin/profile/update' }}">
    @csrf
    {{-- @method('PUT') removed --}}

    @php
        $currentUser = Auth::guard('customer')->user() ?? Auth::user();
    @endphp

    <div class="form-group">
        <label>Profile Photo</label>
        <div class="profile-photo-preview">
            @if($currentUser && $currentUser->avatar)
                <img src="{{ asset('storage/' . $currentUser->avatar) }}" id="profilePhotoPreview">
            @else
                <div id="profilePhotoPreview" class="no-photo">No photo</div>
            @endif
            <input type="file" name="avatar" accept="image/*" onchange="previewProfilePhoto(this)">
        </div>
    </div>

    <div class="form-group">
        <label>Name *</label>
        <input type="text" name="name" value="{{ $currentUser->name ?? '' }}" required>
    </div>

    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="{{ $currentUser->email ?? '' }}" required>
    </div>

    <div class="form-group">
        <label>Phone</label>
        <input type="tel" name="phone" value="{{ $currentUser->phone ?? '' }}">
    </div>

    <div class="form-group">
        <label>Address</label>
        <textarea name="address" rows="3"
            placeholder="Your address will be auto-filled from location...">{{ $currentUser->address ?? '' }}</textarea>
        <button type="button" class="btn-location" onclick="getLocation()">📍 Use Current Location</button>
    </div>

    <button type="submit" class="btn-primary">Update Profile</button>
</form>
      </div>
    </div>
  </div>

  {{-- Scripts --}}
  <script src="{{ asset('js/product-page.js') }}"></script>

  {{-- Geolocation API --}}
  <script>
    // Initialize geolocation if user is logged in
    @auth
      window.addEventListener('load', function () {
        // Check if address is empty, then request location
        const addressField = document.querySelector('textarea[name="address"]');
        if (addressField && !addressField.value.trim()) {
          setTimeout(() => {
            if (confirm('Would you like to use your current location for address?')) {
              getLocation();
            }
          }, 2000);
        }
      });
    @endauth
  </script>
{{-- Cart Modal --}}
<div id="cartModal" class="modal-overlay">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3>🛒 Your Cart</h3>
            <button class="modal-close" onclick="closeCartModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="cartContent">
                <!-- Cart items loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>
</body>

</html>
