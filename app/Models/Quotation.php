<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_no',
        'customer_id',
        'quotation_date',
        'valid_until',
        'sub_total',
        'discount',
        'grand_total',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'datetime',
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function user(){
        return $this->belongsTo(User::class,'created_by');
    }
}
