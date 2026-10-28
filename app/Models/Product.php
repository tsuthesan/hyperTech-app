<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'supplier_id',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock_quantity',
        'qr_code',
        'qr_image',
        'is_active',
        'last_price_updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_price_updated_at' => 'datetime',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function quotationItems()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

}
