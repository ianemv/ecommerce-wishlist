<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function wishlists()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }

    public function wishlistEntries()
    {
        return $this->hasMany(Wishlist::class);
    }
}