<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Products/Index', [
            'products' => Product::with(['category', 'variants'])
                ->withCount('variants')
                ->withSum('variants', 'stock_qty')
                ->latest()
                ->paginate(10)
                ->through(fn ($product) => array_merge($product->toArray(), ['valuation' => $product->valuation])),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Fix: If simple product, ensure variant SKU matches product SKU to pass validation
        if (!$request->has_variants && isset($request->variants[0]) && empty($request->variants[0]['sku'])) {
            $variants = $request->variants;
            $variants[0]['sku'] = $request->sku;
            $request->merge(['variants' => $variants]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'required|array|min:1',
            'variants.*.name' => 'required|string',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.min_stock_level' => 'nullable|integer|min:0',
            'variants.*.barcode' => 'nullable|string',
            'variants.*.expiry_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'category_id' => $validated['category_id'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'unit' => $validated['unit'] ?? 'pcs',
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'] ?? false,
            ]);

            foreach ($validated['variants'] as $variantData) {
                $variant = $product->variants()->create($variantData);

                // If initial stock is provided, record a movement
                if ($variant->stock_qty > 0) {
                    $warehouse = \App\Models\Warehouse::first() ?? \App\Models\Warehouse::create(['name' => 'Default Warehouse']);
                    
                    \App\Models\StockMovement::create([
                        'variant_id' => $variant->id,
                        'warehouse_id' => $warehouse->id,
                        'type' => 'IN',
                        'quantity' => $variant->stock_qty,
                        'balance_after' => $variant->stock_qty,
                        'reference_no' => 'INITIAL-STOCK',
                        'user_id' => auth()->id(),
                        'notes' => 'Opening balance recorded during product creation.',
                    ]);
                }
            }

            return to_route('products.index')->with('success', 'Product created successfully.');
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => $product->load('variants', 'category'),
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'has_variants' => 'boolean',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.name' => 'required|string',
            'variants.*.sku' => 'required|string', // We will validate this manually to handle multiple variants correctly
            'variants.*.cost_price' => 'required|numeric|min:0',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.min_stock_level' => 'nullable|integer|min:0',
            'variants.*.barcode' => 'nullable|string',
        ]);

        // Manual validation for variant SKUs to properly ignore self
        foreach ($request->variants as $index => $v) {
            $variantId = $v['id'] ?? null;
            $v_sku = $v['sku'];
            
            $exists = DB::table('product_variants')
                ->where('sku', $v_sku)
                ->when($variantId, fn($q) => $q->where('id', '!=', $variantId))
                ->exists();
                
            if ($exists) {
                return back()->withErrors(["variants.$index.sku" => "The SKU has already been taken."]);
            }
        }

        // Fix the unique rule for variant SKU: it should be unique across all variants except the current ones of this product?
        // Actually, SKU should be globally unique in product_variants.
        // Let's simplify the validation for now or use a more precise one later.

        return DB::transaction(function () use ($validated, $product) {
            $product->update([
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'category_id' => $validated['category_id'],
                'brand' => $validated['brand'] ?? null,
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'] ?? false,
            ]);

            $existingVariantIds = [];
            foreach ($validated['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    $variant = $product->variants()->find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantData);
                        $existingVariantIds[] = $variant->id;
                    }
                } else {
                    $newVariant = $product->variants()->create($variantData);
                    $existingVariantIds[] = $newVariant->id;
                }
            }

            // Delete variants that were removed
            $product->variants()->whereNotIn('id', $existingVariantIds)->delete();

            return to_route('products.index')->with('success', 'Product updated successfully.');
        });
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return to_route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // Skip header

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $name = $row[0];
            $sku = $row[1];
            $categoryName = $row[2] ?? null;
            $stock = $row[3] ?? 0;
            $price = $row[4] ?? 0;

            DB::transaction(function () use ($name, $sku, $categoryName, $stock, $price) {
                $category = $categoryName ? Category::firstOrCreate(['name' => $categoryName], ['slug' => str($categoryName)->slug()]) : null;
                
                $product = Product::create([
                    'name' => $name,
                    'sku' => $sku,
                    'category_id' => $category?->id,
                    'unit' => 'pcs'
                ]);

                $product->variants()->create([
                    'sku' => $sku,
                    'name' => 'Default',
                    'stock_qty' => $stock,
                    'selling_price' => $price,
                    'cost_price' => $price * 0.8
                ]);
            });
            $imported++;
        }
        fclose($handle);
        
        return back()->with('success', "$imported products imported successfully.");
    }

    public function export()
    {
        // This is handled client-side in the Index.vue but we can add server-side if requested
        return back();
    }
}
