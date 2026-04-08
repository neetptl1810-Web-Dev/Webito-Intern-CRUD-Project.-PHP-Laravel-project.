<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\VerificationCode;

class AuthController extends Controller
{


    public function registerForm()
    {
        return view('auth.register');
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/login');
    }


    public function loginForm()
    {
        return view('auth.login');
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid credentials');
    }


    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function sendVerificationCode(Request $request)
{

    // 🔍 DEBUG: Log that method was reached
    \Log::info('=== sendVerificationCode START ===', [
        'email' => $request->email ?? 'no email',
        'app_env' => env('APP_ENV'),
    ]);

    try {
        // Validate email
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $email = $request->email;

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in database
        \App\Models\VerificationCode::updateOrCreate(
            ['email' => $email],
            [
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
                'used' => false,
            ]
        );

        // ✅ TESTING MODE: Skip email, return code in response
        // ⚠️ REMOVE THIS WHEN MAILTRAP APPROVES YOUR DOMAIN!
        return response()->json([
            'success' => true,
            'message' => 'Verification code generated (TESTING MODE)',
            'dev_code' => $code,       // Code shown in browser
            'email' => $email,         // Echo back for confirmation
            'expires_in' => '10 minutes',
        ]);

        /*
        // 📧 PRODUCTION CODE (Uncomment after Mailtrap approval):
        \Mail::raw(
            "Your verification code is: {$code}\n\nThis code expires in 10 minutes.",
            function ($message) use ($email) {
                $message->to($email)->subject('Verify Your Email - Company Management');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent to your email.',
        ]);
        */

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Handle validation errors (e.g., email already exists)
        return response()->json([
            'success' => false,
            'message' => $e->errors()['email'][0] ?? 'Validation failed',
        ], 422);

    } catch (\Exception $e) {
        // Log error for debugging
        \Log::error('sendVerificationCode failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error. Please try again.',
        ], 500);
    }
}

// =========================================
    // 👤 CUSTOMER AUTHENTICATION METHODS
    // =========================================

    /**
     * Show customer registration form
     */
    public function customerRegisterForm()
    {
        return view('auth.customer-register');
    }

    /**
     * Register new customer - STORES IN customers TABLE
     */
    public function customerRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // ✅ Create customer in customers table
        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        // Auto-login after registration
        Auth::guard('customer')->login($customer);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!',
                'customer' => [
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],
            ]);
        }

        // Fallback redirect
        return redirect()->route('products.page')
            ->with('success', 'Welcome! Your account has been created.');
    }

    /**
     * Show customer login form
     */
    public function customerLoginForm()
    {
        return view('auth.customer-login');
    }

    /**
     * Login customer
     */
    public function customerLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('customer')->attempt($credentials)) {
            $request->session()->regenerate();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                ]);
            }
            
            return redirect()->intended(route('products.page'))
                ->with('success', 'Welcome back!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Logout customer
     */
    public function customerLogout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        }
        
        return redirect()->route('products.page')
            ->with('success', 'You have been logged out.');
    }

    /**
     * Update customer profile
     */
    public function updateCustomerProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                \Storage::disk('public')->delete($customer->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('customers/avatars', 'public');
        }

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'avatar' => $customer->avatar ? asset('storage/' . $customer->avatar) : null,
            ],
        ]);
    }

    public function updateProfile(Request $request)
{
    $user = Auth::user(); // Uses default 'web' guard for admins
    
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
    ]);

    // Handle avatar upload
    if ($request->hasFile('avatar')) {
        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }
        $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    $user->update($validated);

    if ($request->ajax() || $request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ],
        ]);
    }

    return redirect()->back()->with('success', 'Profile updated successfully!');
}

}
