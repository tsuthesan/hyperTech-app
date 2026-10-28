<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = [
        'product_id',
        'old_price',
        'new_price',
        'updated_by',
        'updated_at_price',
    ];

    protected $casts =[
        'updated_at_price' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
