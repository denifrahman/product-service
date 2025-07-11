<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'category', 'unit'];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_products')
            ->withPivot('stock')
            ->withTimestamps();
    }
}
