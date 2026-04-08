<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Add product to cart - Creates Order in Database
     */
    
    public function addToCart(Request $request)
{
    \Log::info('=== addToCart called ===', [
        'isJson' => $request->isJson(),
        'all' => $request->all(),
        'json' => $request->json()->all(),
        'user' => Auth::guard('customer')->id(),
    ]);

    try {
        // Ensure customer is logged in
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to cart',
            ], 401);
        }

        // ✅ Parse JSON body if Content-Type is application/json
        $data = $request->isJson() ? $request->json()->all() : $request->all();
        
        $validated = validator($data, [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:100',
            'address' => 'required|string|max:500',
        ])->validate();

        $product = Product::findOrFail($validated['product_id']);
        $customer = Auth::guard('customer')->user();

        // Check if product is in stock
        if ($product->stock_status === 'out_of_stock') {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this product is out of stock.',
            ], 422);
        }

        DB::beginTransaction();
        
        // Find or create order for this customer & company
        $order = Order::where('customer_id', $customer->id)
            ->where('company_id', $product->company_id)
            ->where('delivery_status', 'pending')
            ->first();

        if (!$order) {
            // Create new order
            $order = Order::create([
                'order_id' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_id' => $customer->id,
                'user_id' => null,
                'company_id' => $product->company_id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'total_amount' => 0,
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'delivery_status' => 'pending',
                'shipping_address' => $validated['address'],
            ]);
        }

        // ✅ Find or create order item - Use $order->id (numeric)
        $orderItem = OrderItem::where('order_id', $order->id)  // ✅ Numeric foreign key
            ->where('product_id', $product->id)
            ->first();

        if ($orderItem) {
            $orderItem->quantity += $validated['quantity'];
            $orderItem->subtotal = $orderItem->price * $orderItem->quantity;
            $orderItem->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,  // ✅ Use numeric orders.id
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $validated['quantity'],
                'price' => $product->price,
                'subtotal' => $product->price * $validated['quantity'],
            ]);
        }

        // ✅ Recalculate order total - Use $order->id (numeric), NOT $order->order_id (string)
        $order->total_amount = OrderItem::where('order_id', $order->id)->sum('subtotal');
        $order->save();

        DB::commit();

        // ✅ Calculate cart count - Use $order->id (numeric)
        $cartCount = OrderItem::where('order_id', $order->id)->sum('quantity');

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $cartCount,
            'order_id' => $order->order_id,  // ✅ Return human-readable order_id for display
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('CartController@addToCart error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Get cart items for current customer
     */
    public function getCart()
{
    if (!Auth::guard('customer')->check()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $customer = Auth::guard('customer')->user();
    
    $orders = Order::where('customer_id', $customer->id)
        ->where('delivery_status', 'pending')
        ->with(['items.product', 'company'])
        ->get();

    $totalItems = 0;
    $grandTotal = 0;
    $allItems = [];

    foreach ($orders as $order) {
        foreach ($order->items as $item) {
            $allItems[] = [
                // ✅ ADD THIS: product_id (the actual product ID, not order_items.id)
                'product_id' => $item->product_id,
                
                'order_id' => $order->order_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
                'company_name' => $order->company->name ?? '',
            ];
            $totalItems += $item->quantity;
            $grandTotal += $item->subtotal;
        }
    }

    return response()->json([
        'success' => true,
        'items' => $allItems,
        'total' => $grandTotal,
        'count' => $totalItems,
    ]);
}

/**
 * Remove item from cart - Deletes from order_items AND orders (if empty)
 */
public function removeFromCart(Request $request, $productId)
{
    if (!Auth::guard('customer')->check()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    $customer = Auth::guard('customer')->user();
    $customerId = $customer->id;
    
    \Log::info('=== removeFromCart START ===', [
        'customer_id' => $customerId,
        'product_id' => $productId,
    ]);

    // ✅ Find the order_item for this product and customer
    $orderItem = OrderItem::where('product_id', $productId)
        ->join('orders', 'order_items.order_id', '=', 'orders.id')  // ✅ Join on numeric id
        ->where('orders.customer_id', $customerId)
        ->where('orders.delivery_status', 'pending')  // Only pending orders (carts)
        ->select('order_items.*', 'orders.id as order_db_id')  // Get numeric orders.id
        ->first();

    if (!$orderItem) {
        \Log::warning('Order item not found', ['product_id' => $productId]);
        return response()->json([
            'success' => false,
            'message' => 'Item not found in cart',
        ], 404);
    }

    $orderDbId = $orderItem->order_db_id;  // This is orders.id (numeric), e.g., 8
    
    \Log::info('Order item found', [
        'order_item_id' => $orderItem->id,
        'order_db_id' => $orderDbId,
    ]);

    // ✅ Step 1: Delete the order_item
    OrderItem::where('id', $orderItem->id)->delete();
    \Log::info('Order item deleted', ['order_item_id' => $orderItem->id]);

    // ✅ Step 2: Check if order has any remaining items
    $remainingItems = OrderItem::where('order_id', $orderDbId)->count();
    \Log::info('Remaining items check', [
        'order_db_id' => $orderDbId,
        'remaining_count' => $remainingItems,
    ]);

    // ✅ Step 3: If no items remain, delete the order too
    if ($remainingItems === 0) {
    $order = Order::withTrashed()->where('id', $orderDbId)->first();
    if ($order) {
        $order->forceDelete();  // ✅ Permanent delete (bypasses soft deletes)
        \Log::info('Order permanently deleted', ['order_id' => $orderDbId]);
    }
} else {
        // ✅ Step 4: Update order total if items remain
        $order = Order::where('id', $orderDbId)->first();
        if ($order) {
            $order->total_amount = OrderItem::where('order_id', $orderDbId)->sum('subtotal');
            $order->save();
            \Log::info('Order total updated', ['order_id' => $orderDbId]);
        }
    }

    // ✅ Step 5: Calculate new cart count
    $cartCount = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.customer_id', $customerId)
        ->where('orders.delivery_status', 'pending')
        ->sum('order_items.quantity');

    \Log::info('=== removeFromCart END ===', [
        'cart_count' => $cartCount,
        'order_deleted' => $remainingItems === 0,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => $cartCount,
    ]);
}

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $customer = Auth::guard('customer')->user();
        
        Order::where('customer_id', $customer->id)
    ->where('delivery_status', 'pending')
    ->delete();

        return response()->json(['success' => true, 'message' => 'Cart cleared successfully']);
    }

    /**
     * Checkout - Convert cart to confirmed order
     */
    public function checkout(Request $request)
    {
        if (!Auth::guard('customer')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cod,online',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer = Auth::guard('customer')->user();
        
        $orders = Order::where('customer_id', $customer->id)
            ->where('delivery_status', 'pending')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        foreach ($orders as $order) {
            $order->update([
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'cod' ? 'pending' : 'processing',
                'delivery_status' => 'processing',
                'notes' => $validated['notes'] ?? $order->notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully!',
            'orders' => $orders,
        ]);
    }
}