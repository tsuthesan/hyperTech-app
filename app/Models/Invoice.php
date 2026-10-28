<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'quotation_id',
        'invoice_date',
        'sub_total',
        'discount',
        'grand_total',
        'amount_paid',
        'balance_amount',
        'status',
        'print_status',
        'invoice_pdf',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function user(){
        return $this->belongsTo(User::class,'created_by');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

}
