<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // ← ADD THIS IMPORT (was missing!)

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',      // ← ADD THIS (was missing!)
        'description',
        'username',       // ← Added
        'password',       // ← Added (will be hashed)
        'user_id',
    ];

    protected $hidden = [
        'password',       // ← Hide password from JSON output
    ];

    protected $casts = [
        'password' => 'hashed',  // ← Auto-hash password
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    // Helper: Generate unique slug
    public static function generateUniqueSlug($name, $id = null)
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    // Add this relationship
public function orders()
{
    return $this->hasMany(Order::class);
}
}
