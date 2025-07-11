<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'mutation_type', 'old_quantity', 'quantity', 'note', 'user_id', 'location_product_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locationProduct()
    {
        return $this->belongsTo(LocationProduct::class);
    }
}
