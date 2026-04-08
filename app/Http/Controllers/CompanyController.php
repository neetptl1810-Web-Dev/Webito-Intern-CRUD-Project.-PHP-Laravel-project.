<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product; // ← Add this import
use App\Models\Order;          // ✅ ADD THIS if missing
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('id', 'asc')->paginate(10);
        return view('company', compact('companies'));
    }

    private function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Company::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'description' => 'required|string|max:1000|min:2',
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'alpha_num',
                'unique:companies,username'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',      // uppercase
                'regex:/[0-9]/',      // number
                'regex:/[!@#$%^&*]/', // special char
            ],
        ], [
            'username.unique' => 'This username is already taken.',
            'password.regex' => 'Password must include uppercase, number, and special character.',
        ]);

        try {
            $company = Company::create([
                'name' => $validated['name'],
                'slug' => Company::generateUniqueSlug($validated['name']),
                'description' => $validated['description'],
                'username' => $validated['username'],
                'password' => bcrypt($validated['password']),
                'user_id' => auth()->id(),
            ]);

            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company added successfully!',
                    'company' => [
                        'id' => $company->id,
                        'name' => $company->name,
                        'slug' => $company->slug,
                    ]
                ]);
            }

            // Fallback for non-AJAX (redirect)
            return redirect()->route('products.page')
                ->with('success', 'Company "' . $company->name . '" added successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate key errors
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                return response()->json([
                    'success' => false,
                    'message' => 'A company with this username or slug already exists.',
                ], 422);
            }
            throw $e;
        }
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $company->update([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name'], $company->id),
            'description' => $validated['description'],
        ]);

        // ✅ Redirect to the NEW slug URL (not back to old URL)
        return redirect()->route('company.show', $company)
            ->with('success', 'Company updated successfully!');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        // ✅ Redirect to company list (not back to deleted company page)
        return redirect()->route('company.index')
            ->with('success', 'Company deleted successfully!');
    }

    public function show(Company $company)
    {
        // ✅ Load products relationship for show.blade.php
        $company->load('products');

        return view('company.show', compact('company'));
    }

    public function storeProduct(Request $request, Company $company)
    {
        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            return back()->withErrors(['image' => 'Upload Error: ' . $request->file('image')->getErrorMessage()]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'stock_status' => 'required|in:in_stock,low_stock,out_of_stock,pre_order',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
    $imagePath = $request->file('image')->store('products', 'public');
    // ✅ This now stores in: public/storage/products/
        }

        $company->products()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'stock_status' => $validated['stock_status'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'image' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Product added successfully!');
    }

    public function updateProduct(Request $request, Company $company, Product $product)
    {
        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            return back()->withErrors(['image' => 'Upload Error: ' . $request->file('image')->getErrorMessage()]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'stock_status' => 'required|in:in_stock,low_stock,out_of_stock,pre_order',
            'description' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully!');
    }

    public function deleteProduct(Company $company, Product $product)
    {
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        // ✅ Redirect to company show page (not back)
        return redirect()->route('company.show', $company)
            ->with('success', 'Product deleted successfully!');
    }

    public function productsPage()
    {
        $search = request('search');

        $query = Company::withCount('products');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')->paginate(12);

        return view('products.index', compact('companies', 'search'));
    }

    public function showCompanyProducts(Company $company)
    {
        $products = $company->products()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('products.company', compact('company', 'products'));
    }



    /**
     * Verify company username/password credentials
     */
    /**
     * Verify company username/password credentials
     */
    public function verifyCredentials(Request $request)
    {
        $request->validate([
            'company_slug' => 'required|string|exists:companies,slug',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $company = \App\Models\Company::where('slug', $request->company_slug)->firstOrFail();

        // Check username and password
        if (
            $company->username !== $request->username ||
            !\Illuminate\Support\Facades\Hash::check($request->password, $company->password)
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.',
            ], 401);
        }


        // ✅ Return redirect URL to COMPANY SHOW page (show.blade.php)
        return response()->json([
            'success' => true,
            'message' => 'Access granted!',
            'company_slug' => $company->slug,

            // ✅ Redirect to company.show page (shows show.blade.php)
            'redirect_url' => route('company.show', $company),
        ]);
    }

    // View orders for a company
    public function viewOrders(Company $company)
{
    $orders = $company->orders()
        // ✅ Add customer_phone and shipping_address to the select if needed
        ->with(['items.product'])  // You don't need 'user' if using customer guard
        ->orderBy('created_at', 'desc')
        ->paginate(15);
    
    return view('company.orders', compact('company', 'orders'));
}

    // Update order delivery status
    public function updateOrderStatus(Request $request, Company $company, Order $order)
{
    try {
        // Ensure order belongs to this company
        if ($order->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $validated = $request->validate([
            'delivery_status' => 'required|in:pending,processing,shipped,delivered,cancelled,no_response',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update order
        $order->update([
            'delivery_status' => $validated['delivery_status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully!',
                'order' => [
                    'delivery_status' => $order->delivery_status,
                    'delivery_status_label' => $this->getDeliveryStatusLabel($order->delivery_status),
                    'delivery_status_badge' => $this->getDeliveryStatusBadge($order->delivery_status),
                    'notes' => $order->notes,
                ],
            ]);
        }

        // Fallback redirect
        return redirect()->back()->with('success', 'Order status updated successfully!');

    } catch (\Exception $e) {
        \Log::error('updateOrderStatus error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'order_id' => $order->id ?? null,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * View order details (AJAX)
 */
public function viewOrderDetails(Company $company, Order $order)
{
    try {
        // Ensure order belongs to this company
        if ($order->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        // Load relationships
        $order->load(['items.product']);

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_id' => $order->order_id,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'customer_phone' => $order->customer_phone,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method === 'cod' ? '💵 Cash on Delivery' : '💳 Online Payment',
                    'payment_status' => $order->payment_status,
                    'delivery_status' => $order->delivery_status,
                    'delivery_status_label' => $this->getDeliveryStatusLabel($order->delivery_status),
                    'delivery_status_badge' => $this->getDeliveryStatusBadge($order->delivery_status),
                    'shipping_address' => $order->shipping_address,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at?->format('M d, Y h:i A'),
                    'items' => $order->items->map(function($item) {
                        return [
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                ]
            ]);
        }

        // Fallback: return view (if not AJAX)
        return view('company.order-details', compact('company', 'order'));

    } catch (\Exception $e) {
        \Log::error('viewOrderDetails error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'order_id' => $order->id ?? null,
            'company_id' => $company->id ?? null,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Helper: Get delivery status label
 */
private function getDeliveryStatusLabel(string $status): string
{
    return match($status) {
        'pending' => '⏳ Pending',
        'processing' => '⚙️ Processing',
        'shipped' => '🚚 Shipped',
        'delivered' => '✅ Delivered',
        'cancelled' => '❌ Cancelled',
        'no_response' => '📭 No Response',
        default => 'Unknown',
    };
}

/**
 * Helper: Get delivery status badge style
 */
private function getDeliveryStatusBadge(string $status): string
{
    return match($status) {
        'pending' => 'background: #f59e0b; color: white;',
        'processing' => 'background: #3b82f6; color: white;',
        'shipped' => 'background: #8b5cf6; color: white;',
        'delivered' => 'background: #10b981; color: white;',
        'cancelled' => 'background: #ef4444; color: white;',
        'no_response' => 'background: #64748b; color: white;',
        default => 'background: #64748b; color: white;',
    };
}
}
