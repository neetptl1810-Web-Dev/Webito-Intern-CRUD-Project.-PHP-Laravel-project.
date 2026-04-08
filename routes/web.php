<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CartController;

// =========================================
// 📧 Email Verification
// =========================================
Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode'])
    ->name('verification.send');

// =========================================
// 🔐 Admin Auth Routes
// =========================================
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================
// 👤 Customer Auth Routes
// =========================================
Route::get('/customer/register', [AuthController::class, 'customerRegisterForm'])->name('customer.register.form');
Route::post('/customer/register', [AuthController::class, 'customerRegister'])->name('customer.register');
Route::get('/customer/login', [AuthController::class, 'customerLoginForm'])->name('customer.login.form');
Route::post('/customer/login', [AuthController::class, 'customerLogin'])->name('customer.login');
Route::post('/customer/logout', [AuthController::class, 'customerLogout'])->name('customer.logout');

// =========================================
// 📊 Dashboard (Admin Only)
// =========================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('custom.auth');

// =========================================
// 🔍 Username Check (AJAX)
// =========================================
Route::get('/check-username', function (\Illuminate\Http\Request $request) {
    $request->validate(['username' => 'required|string']);
    $exists = \App\Models\Company::where('username', $request->username)->exists();
    return response()->json([
        'available' => !$exists,
        'message' => $exists ? 'Username is already taken' : 'Username is available'
    ]);
})->name('company.check-username');

// =========================================
// 🏢 Company Routes (Admin Protected)
// =========================================
Route::middleware(['auth'])->group(function () {
    Route::resource('company', CompanyController::class)->except(['create', 'edit']);
    
    // Verify company credentials (AJAX)
    Route::post('/company/verify', [CompanyController::class, 'verifyCredentials'])
        ->name('company.verify');
    
    // Order management
    Route::get('/company/{company}/orders', [CompanyController::class, 'viewOrders'])
        ->name('company.orders');
    Route::get('/company/{company}/orders/{order}', [CompanyController::class, 'viewOrderDetails'])
    ->name('company.order.details');
    Route::put('/company/{company}/orders/{order}/status', [CompanyController::class, 'updateOrderStatus'])
        ->name('company.order.update-status');
    
    // Product CRUD
    Route::post('/company/{company}/products', [CompanyController::class, 'storeProduct'])
        ->name('company.products.store');
    Route::put('/company/{company}/products/{product}', [CompanyController::class, 'updateProduct'])
        ->name('company.products.update');
    Route::delete('/company/{company}/products/{product}', [CompanyController::class, 'deleteProduct'])
        ->name('company.products.destroy');
});

// Cart Routes (Protected)
Route::middleware(['auth:customer'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'getCart'])->name('cart.index');
    Route::delete('/cart/{product}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
});

// =========================================
// 👥 Profile Update Routes
// =========================================
// Admin profile
Route::middleware(['auth'])->group(function () {
    Route::post('/admin/profile/update', [AuthController::class, 'updateProfile'])
        ->name('profile.update');
});

// Customer profile
Route::middleware(['auth:customer'])->group(function () {
    Route::post('/customer/profile/update', [AuthController::class, 'updateCustomerProfile'])
        ->name('customer.profile.update');
});

// =========================================
// 🛍️ Products Pages (Public)
// =========================================
Route::get('/products', [CompanyController::class, 'productsPage'])->name('products.page');
Route::get('/products/{company}', [CompanyController::class, 'showCompanyProducts'])->name('products.company');

// =========================================
// 🏠 Welcome
// =========================================
Route::get('/', function () {
    return view('welcome');
});