<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\StockTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index( Request $request)
    {
        $query = Invoice::with('customer', 'creator');
             if ($request->filled('search')) {
                 $search = $request->search;

                 $query->where(function ($q) use ($search) {
                     $q->where('invoice_no', 'like', "%{$search}%")
                         ->orWhereHas('customer', function ($customerQuery) use ($search) {
                             $customerQuery->where('name', 'like', "%{$search}%");
                         });
                 });
             }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('invoice_date', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('invoice_date', '<=', $request->to_date);
    }

    $invoices = $query
        ->latest('id')
        ->paginate(15)
        ->withQueryString();

    return view('invoices.index', compact('invoices'));


    }

    /**
     * Show invoice creation form.
     */
    public function create()
    {
        $customers=Customer::orderBy('name')->get(['id','name','phone']);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'product_code','name','selling_price', 'stock_quantity', 'cost_price','unit']);
        return view('invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created invoice.
     *
     * Implement invoice creation and stock deduction
     * after confirming the actual form and product model.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'quotation_id' => [
                'nullable',
                'exists:quotations,id',
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'tax' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment_method' => [
                'required',
                'in:cash,bank_transfer,credit',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'exists:products,id',
                'distinct',
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);

        $invoice = DB::transaction(function () use ($validated) {

            $subTotal = 0;
            $preparedItems = [];

            // Sort IDs consistently to reduce deadlock risk.
            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            $products = Product::whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($validated['items'] as $item) {

                $product = $products->get((int) $item['product_id']);

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

                $unitPrice = round((float) $item['unit_price'], 2);
                $totalPrice = round($quantity * $unitPrice, 2);

                $subTotal += $totalPrice;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $discount = round(
                (float) ($validated['discount'] ?? 0),
                2
            );

            $tax = round(
                (float) ($validated['tax'] ?? 0),
                2
            );

            if ($discount > $subTotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount cannot exceed subtotal.',
                ]);
            }

            $grandTotal = round(
                $subTotal - $discount + $tax,
                2
            );

            // Payments are recorded separately through recordPayment().
            $amountPaid = 0;
            $balance = $grandTotal;

            $invoice = Invoice::create([
                'invoice_no' => 'INV-' . now()->format('YmdHis')
                    . '-' . strtoupper(Str::random(4)),

                'customer_id' => $validated['customer_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'sub_total' => $subTotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'balance_amount' => $balance,
                'payment_method' => $validated['payment_method'],
                'status' => 'sent',
                'print_status' => 'not_printed',
                'invoice_pdf' => '',
                'created_by' => auth()->id(),
            ]);

            foreach ($preparedItems as $item) {

                $product = $item['product'];

                $invoiceItem = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                $product->decrement(
                    'stock_quantity',
                    $item['quantity']
                );

                StockTransaction::create([
                    'product_id' => $product->id,
                    'invoice_id' => $invoice->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'note' => 'Invoice sale: ' . $invoice->invoice_no,
                    'created_by' => auth()->id(),
                ]);
            }

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'items.product',
            'payments.receiver',
            'creator',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show edit form.
     */
    public function edit(string $id)
    {
        // Invoice editing will be implemented separately.
    }

    /**
     * Update invoice.
     */
    public function update(Request $request, string $id)
    {
        // Invoice update will be implemented separately.
    }

    /**
     * Delete invoice.
     */
    public function destroy(string $id)
    {
        // Invoice deletion will be implemented separately.
    }

    /**
     * Record invoice payment.
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],

            'payment_method' => [
                'required',
                'in:cash,bank_transfer,credit',
            ],

            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'paid_at' => [
                'required',
                'date',
            ],
        ]);

        DB::transaction(function () use ($invoice, $validated) {

            $lockedInvoice = Invoice::whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $lockedInvoice->balance_amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'This invoice has already been fully paid.',
                ]);
            }

            $amount = round((float) $validated['amount'], 2);

            if ($amount > (float) $lockedInvoice->balance_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the outstanding balance.',
                ]);
            }

            $paymentDate = Carbon::parse(
                $validated['paid_at'] ?? now()->toDateString()
            );

            $receiptNo = 'REC-' . now()->format('Ymd') . '-' .
                str_pad(
                    (InvoicePayment::whereDate('created_at', today())->count() + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                );

            InvoicePayment::create([
                'invoice_id' => $lockedInvoice->id,
                'receipt_no' => $receiptNo,
                'amount' => $amount,
               // 'payment_date' => $paymentDate->toDateString(),
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
                'paid_at' => $paymentDate,
            ]);

            $newPaid = round(
                (float) $lockedInvoice->amount_paid + $amount,
                2
            );

            $newBalance = round(
                (float) $lockedInvoice->grand_total - $newPaid,
                2
            );

            $lockedInvoice->update([
                'amount_paid' => $newPaid,
                'balance_amount' => $newBalance,
                'status' => $newBalance <= 0 ? 'paid' : 'partial',
            ]);
        });

        return back()->with(
            'success',
            'Payment recorded successfully.'
        );
    }

    /**
     * Download invoice PDF.
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'items.product',
            'payments.receiver',
            'creator',
        ]);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
            ->setPaper('a4', 'portrait');

        return $pdf->download(
            $invoice->invoice_no . '.pdf'
        );
    }

    /**
     * Quickly create a customer from the invoice screen.
     */
    public function quickStoreCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully.',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'whatsapp_number' => $customer->whatsapp_number,
                'email' => $customer->email,
                'address' => $customer->address,
            ],
        ], 201);
    }

    public function paymentReceipt(InvoicePayment $payment)
    {
        $payment->load([
            'invoice.customer',
            'invoice.items.product',
            'receiver',
        ]);

        return view('invoices.payment-receipt', compact('payment'));
    }

    public function paymentReceiptPdf(InvoicePayment $payment)
    {
        $payment->load([
            'invoice.customer',
            'receiver',
        ]);

        $pdf = Pdf::loadView('invoices.payment-receipt', [
            'payment' => $payment,
            'pdfMode' => true,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            ($payment->receipt_no ?? 'receipt-' . $payment->id) . '.pdf'
        );
    }
}
