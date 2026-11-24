<?php
namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function stockIn(
        int $productId,
        int $quantity,
        ?float $unitPrice = null,
        ?string $note = null
    ): Product {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        return DB::transaction(function () use (
            $productId,
            $quantity,
            $unitPrice,
            $note
        ) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product is inactive.',
                ]);
            }

            $product->stock_quantity += $quantity;

            if ($unitPrice !== null) {
                $product->cost_price = $unitPrice;
            }

            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'unit_price' => $unitPrice ?? $product->cost_price,
                'note' => $note ?? 'Stock received',
                'created_by' => auth()->id(),
            ]);

            return $product;
        });
    }

    public function adjust(
        int $productId,
        int $newQuantity,
        string $note
    ): Product {
        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'new_quantity' => 'Stock cannot be negative.',
            ]);
        }

        return DB::transaction(function () use (
            $productId,
            $newQuantity,
            $note
        ) {
            $product = Product::whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product is inactive.',
                ]);
            }

            $difference = $newQuantity - $product->stock_quantity;

            if ($difference === 0) {
                throw ValidationException::withMessages([
                    'new_quantity' => 'There is no stock difference to adjust.',
                ]);
            }

            $product->stock_quantity = $newQuantity;
            $product->save();

            StockTransaction::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => $difference,
                'unit_price' => $product->cost_price,
                'note' => $note,
                'created_by' => auth()->id(),
            ]);

            return $product;
        });
    }
}
