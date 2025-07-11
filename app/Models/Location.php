<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    
    protected $fillable = ['code', 'name', 'status', 'latitude', 'longitude', 'address'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'location_products')
            ->withPivot('stock')
            ->withTimestamps();
    }
}
