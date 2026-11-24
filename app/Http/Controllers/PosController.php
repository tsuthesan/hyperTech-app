<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = DB::table('customers')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('pos.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function searchProducts(Request $request)
    {
        $search = trim($request->get('search', ''));

        $products = DB::table('products')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id',
                'product_code',
                'name',
                'selling_price',
                'stock_quantity',
                'unit',
            ]);

        return response()->json($products);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => [
                'required',
                'in:cash,bank_transfer,credit',
            ],
        ]);

        $invoiceId = DB::transaction(function () use ($validated) {

            // Lock products in ID order to reduce locking conflicts.
            $items = collect($validated['items'])
                ->sortBy('product_id')
                ->values();

            $productIds = $items->pluck('product_id')->all();

            $products = DB::table('products')
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $invoiceLines = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product is unavailable.',
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ($product->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}.",
                    ]);
                }

                // Always use the database selling price.
                // Never trust the price submitted by the browser.
                $unitPrice = (float) $product->selling_price;
                $lineTotal = round($quantity * $unitPrice, 2);

                $subtotal += $lineTotal;

                $invoiceLines[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = round((float) ($validated['discount'] ?? 0), 2);
            $tax = round((float) ($validated['tax'] ?? 0), 2);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount cannot exceed the subtotal.',
                ]);
            }

            $grandTotal = round($subtotal - $discount + $tax, 2);
            $amountPaid = round((float) $validated['amount_paid'], 2);

            if ($amountPaid > $grandTotal) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Payment cannot exceed the invoice total.',
                ]);
            }

            $balance = round($grandTotal - $amountPaid, 2);

            $status = $balance <= 0
                ? 'paid'
                : ($amountPaid > 0 ? 'partial' : 'sent');

            // Random suffix avoids relying on a count-based invoice number.
            $invoiceNo = 'HTS-' . now()->format('Ymd') . '-'
                . strtoupper(Str::random(6));

            $invoiceId = DB::table('invoices')->insertGetId([
                'invoice_no' => $invoiceNo,
                'customer_id' => $validated['customer_id'],
                'quotation_id' => null,
                'invoice_date' => now()->toDateString(),
                'sub_total' => $subtotal,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'balance_amount' => $balance,
                'payment_method' => $validated['payment_method'],
                'status' => $status,
                'print_status' => 'pending',
                'invoice_pdf' => '',
                'tax' => $tax,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($amountPaid > 0) {
                InvoicePayment::create([
                    'invoice_id' => $invoiceId,
                    'amount' => $amountPaid,
                    'payment_method' => $validated['payment_method'],
                    'received_by' => auth()->id(),
                    'paid_at' => now(),
                    'notes' => 'Initial POS payment',
                ]);
            }

            foreach ($invoiceLines as $line) {
                DB::table('invoice_items')->insert([
                    'invoice_id' => $invoiceId,
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'total_price' => $line['total_price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Deduct stock only after all stock checks have passed.
                DB::table('products')
                    ->where('id', $line['product_id'])
                    ->decrement('stock_quantity', $line['quantity']);

                DB::table('stock_transactions')->insert([
                    'product_id' => $line['product_id'],
                    'type' => 'sale',
                    'quantity' => -$line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'note' => "POS sale: {$invoiceNo}",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $invoiceId;
        });

        return redirect()
            ->route('invoices.show', $invoiceId)
            ->with('success', 'Invoice created successfully.');
    }

}
