<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        $search = $request->input('search');

        $suppliers = Supplier::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('supplier_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $supplier = DB::transaction(function () use ($data) {

            // Temporary unique code to satisfy the unique constraint.
            $data['supplier_code'] = 'TEMP-' . Str::uuid();

            $supplier = Supplier::create($data);

            // Generate permanent code using the actual database ID.
            $supplier->supplier_code = 'SUP' .
                str_pad($supplier->id, 6, '0', STR_PAD_LEFT);

            $supplier->save();

            return $supplier;
        });

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully. Code: ' . $supplier->supplier_code);

    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $products = Product::where('supplier_id', $supplier->id)
            ->latest()
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'products'));    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        // Supplier code is generated automatically and cannot be changed.
        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $deleted = DB::transaction(function () use ($supplier) {

            $lockedSupplier = Supplier::whereKey($supplier->id)
                ->lockForUpdate()
                ->firstOrFail();

            $hasProducts = Product::where(
                'supplier_id',
                $lockedSupplier->id
            )->exists();

            if ($hasProducts) {
                return false;
            }

            $lockedSupplier->delete();

            return true;
        });

        if (!$deleted) {
            return back()->with(
                'error',
                'This supplier is linked to products. You cannot delete it.'
            );
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
