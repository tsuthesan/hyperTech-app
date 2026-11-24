<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display categories.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $categories = Category::query()
            ->withCount('products')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('categories.index', compact(
            'categories',
            'search'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store category with automatic code.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],
        ]);

        $category = DB::transaction(function () use ($data) {

            $category = Category::create([
                'name' => $data['name'],
                'code' => 'TEMP-' . Str::uuid(),
            ]);

            $category->code = 'CAT' .
                str_pad($category->id, 6, '0', STR_PAD_LEFT);

            $category->save();

            return $category;
        });

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category created successfully. Code: ' . $category->code
            );
    }

    /**
     * Display category details.
     */
    public function show(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->latest()
            ->paginate(10);

        return view('categories.show', compact(
            'category',
            'products'
        ));
    }

    /**
     * Show edit form.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update category.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $category->id,
            ],
        ]);

        // Category code cannot be changed.
        $category->update($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete category.
     */
    public function destroy(Category $category)
    {
        $hasProducts = Product::where(
            'category_id',
            $category->id
        )->exists();

        if ($hasProducts) {
            return back()->with(
                'error',
                'This category has products linked to it. Remove or reassign those products before deleting.'
            );
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
