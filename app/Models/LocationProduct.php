<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationProduct extends Model
{
    use HasFactory;
    
    protected $table = 'location_products';

    protected $fillable = ['product_id', 'location_id', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function mutations()
    {
        return $this->hasMany(Mutation::class);
    }
}
