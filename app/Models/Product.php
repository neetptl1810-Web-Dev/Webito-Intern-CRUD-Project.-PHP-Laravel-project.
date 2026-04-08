<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'description',
        'image',
        'price',
        'stock_status',
    ];

    // ✅ Add this relationship
    public function company()
{
    return $this->belongsTo(Company::class);
}

    // ✅ Helper to get full image URL
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/no-image.png');
    }

    public function getStockStatusBadge(): string
    {
        return match($this->stock_status) {
            'in_stock' => 'background: #10b981; color: white;',
            'low_stock' => 'background: #f59e0b; color: white;',
            'out_of_stock' => 'background: #ef4444; color: white;',
            'pre_order' => 'background: #3b82f6; color: white;',
            default => 'background: #64748b; color: white;',
        };
    }

    // Get stock status label
    public function getStockStatusLabel(): string
    {
        return match($this->stock_status) {
            'in_stock' => '✅ In Stock',
            'low_stock' => '⚠️ Low Stock',
            'out_of_stock' => '❌ Out of Stock',
            'pre_order' => '📦 Pre-Order',
            default => 'Unknown',
        };
    }

    public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}

}
