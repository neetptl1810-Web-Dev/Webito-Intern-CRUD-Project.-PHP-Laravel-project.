<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'user_id',
    'customer_id',
    'company_id',
    'order_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'total_amount',
    'payment_method',
    'payment_status',
    'delivery_status',
    'shipping_address',
    'notes',
];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship: Order belongs to Company
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relationship: Order has many Order Items
    public function items()
{
    return $this->hasMany(OrderItem::class, 'order_id');  // order_items.order_id matches orders.id
}

public function getRouteKeyName()
{
    return 'id'; // Use numeric ID for binding, not order_id string
}

    // Generate unique order ID
    public static function generateOrderId()
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    // Get delivery status badge color
    public function getDeliveryStatusBadge(): string
    {
        return match($this->delivery_status) {
            'pending' => 'background: #f59e0b; color: white;',
            'processing' => 'background: #3b82f6; color: white;',
            'shipped' => 'background: #8b5cf6; color: white;',
            'delivered' => 'background: #10b981; color: white;',
            'cancelled' => 'background: #ef4444; color: white;',
            default => 'background: #64748b; color: white;',
        };
    }

    // Get delivery status label
    public function getDeliveryStatusLabel(): string
    {
        return match($this->delivery_status) {
            'pending' => '⏳ Pending',
            'processing' => '⚙️ Processing',
            'shipped' => '🚚 Shipped',
            'delivered' => '✅ Delivered',
            'cancelled' => '❌ Cancelled',
            default => 'Unknown',
        };
    }

    // Get payment method label
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'cod' => '💵 Cash on Delivery',
            'online' => '💳 Online Payment',
            default => 'Unknown',
        };
    }
}