<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBatch;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\ProductUnit;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\UnitConversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'baseUnit', 'stockBalances.location']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('name_ur', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        $units = ProductUnit::all();
        $locations = Location::where('is_active', true)->get();

        return view('products.create', compact('categories', 'brands', 'manufacturers', 'units', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ur' => 'nullable|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'base_unit_id' => 'required|exists:product_units,id',
            'purchase_cost' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'min_margin_percent' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'expiry_tracking' => 'nullable|boolean',
            'opening_stock' => 'nullable|numeric|min:0',
            'opening_location_id' => 'nullable|exists:locations,id',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create([
                'name' => $request->name,
                'name_ur' => $request->name_ur,
                'sku' => strtoupper($request->sku),
                'barcode' => $request->barcode ?: strtoupper(Str::random(12)),
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'manufacturer_id' => $request->manufacturer_id,
                'base_unit_id' => $request->base_unit_id,
                'purchase_cost' => $request->purchase_cost,
                'wholesale_price' => $request->wholesale_price,
                'retail_price' => $request->retail_price,
                'min_margin_percent' => $request->min_margin_percent ?? 5.0,
                'reorder_level' => $request->reorder_level ?? 10,
                'expiry_tracking' => $request->boolean('expiry_tracking'),
                'is_active' => true,
                'description' => $request->description,
            ]);

            // Handle conversions if Carton unit is selected
            $cartonUnit = ProductUnit::where('short_name', 'ctn')->first();
            if ($cartonUnit && $request->filled('carton_conversion')) {
                UnitConversion::create([
                    'product_id' => $product->id,
                    'from_unit_id' => $cartonUnit->id,
                    'to_unit_id' => $product->base_unit_id,
                    'conversion_factor' => $request->carton_conversion,
                ]);
            }

            // Handle Opening Stock if provided
            if ($request->filled('opening_stock') && $request->opening_stock > 0 && $request->filled('opening_location_id')) {
                $batch = null;
                if ($product->expiry_tracking && $request->filled('batch_number')) {
                    $batch = InventoryBatch::create([
                        'product_id' => $product->id,
                        'location_id' => $request->opening_location_id,
                        'batch_number' => $request->batch_number,
                        'expiry_date' => $request->expiry_date,
                        'cost_price' => $product->purchase_cost,
                        'quantity' => $request->opening_stock,
                        'status' => 'active',
                    ]);
                }

                StockMovement::recordMovement(
                    $product->id,
                    $request->opening_location_id,
                    $request->opening_stock,
                    'opening',
                    'OPENING',
                    'INIT-' . $product->sku,
                    $product->purchase_cost,
                    $batch ? $batch->id : null,
                    'Opening balance entered at product registration',
                    auth()->id()
                );
            }

            AuditLog::log('create', $product, null, $product->toArray(), 'New product registered');
        });

        return redirect()->route('products.index')->with('success', 'Product registered successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        $units = ProductUnit::all();
        $locations = Location::where('is_active', true)->get();
        $priceHistories = $product->priceHistory()->with('user')->latest()->take(10)->get();

        return view('products.edit', compact('product', 'categories', 'brands', 'manufacturers', 'units', 'locations', 'priceHistories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ur' => 'nullable|string|max:255',
            'sku' => "required|string|unique:products,sku,{$product->id}",
            'barcode' => "nullable|string|unique:products,barcode,{$product->id}",
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'purchase_cost' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'min_margin_percent' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $old = $product->toArray();

        // Check if price changed -> record in price history (FR-33)
        $costChanged = (float)$product->purchase_cost !== (float)$request->purchase_cost;
        $retailChanged = (float)$product->retail_price !== (float)$request->retail_price;
        $wholesaleChanged = (float)$product->wholesale_price !== (float)$request->wholesale_price;

        if ($costChanged || $retailChanged || $wholesaleChanged) {
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'old_cost' => $product->purchase_cost,
                'new_cost' => $request->purchase_cost,
                'old_retail_price' => $product->retail_price,
                'new_retail_price' => $request->retail_price,
                'old_wholesale_price' => $product->wholesale_price,
                'new_wholesale_price' => $request->wholesale_price,
                'changed_by' => auth()->id(),
                'reason' => $request->price_change_reason ?: 'Rate revision',
            ]);
        }

        $product->update([
            'name' => $request->name,
            'name_ur' => $request->name_ur,
            'sku' => strtoupper($request->sku),
            'barcode' => $request->barcode,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'manufacturer_id' => $request->manufacturer_id,
            'purchase_cost' => $request->purchase_cost,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'min_margin_percent' => $request->min_margin_percent ?? 5.0,
            'reorder_level' => $request->reorder_level ?? 10,
            'expiry_tracking' => $request->boolean('expiry_tracking'),
            'is_active' => $request->boolean('is_active'),
            'description' => $request->description,
        ]);

        AuditLog::log('update', $product, $old, $product->toArray(), 'Product details updated');

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        AuditLog::log('toggle_status', $product, null, ['is_active' => $product->is_active], 'Product active status toggled');

        return back()->with('success', 'Product status updated.');
    }

    public function barcodes(Request $request)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = Product::find($request->product_id);
        }

        return view('products.barcodes', compact('products', 'selectedProduct'));
    }

    public function bulkRates(Request $request)
    {
        $categories = Category::where('is_active', true)->get();
        if ($request->isMethod('post')) {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
                'field' => 'required|in:wholesale_price,retail_price',
                'change_type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric',
                'operation' => 'required|in:increase,decrease',
            ]);

            $products = Product::where('category_id', $request->category_id)->get();
            foreach ($products as $p) {
                $current = (float)$p->{$request->field};
                $delta = $request->change_type === 'percentage' ? ($current * $request->value / 100) : $request->value;
                $new = $request->operation === 'increase' ? ($current + $delta) : ($current - $delta);
                $new = max(0, round($new, 2));

                ProductPriceHistory::create([
                    'product_id' => $p->id,
                    'old_retail_price' => $request->field === 'retail_price' ? $current : $p->retail_price,
                    'new_retail_price' => $request->field === 'retail_price' ? $new : $p->retail_price,
                    'old_wholesale_price' => $request->field === 'wholesale_price' ? $current : $p->wholesale_price,
                    'new_wholesale_price' => $request->field === 'wholesale_price' ? $new : $p->wholesale_price,
                    'changed_by' => auth()->id(),
                    'reason' => "Bulk {$request->operation} of {$request->value}" . ($request->change_type === 'percentage' ? '%' : ' Rs.'),
                ]);

                $p->{$request->field} = $new;
                $p->save();
            }

            return back()->with('success', "Bulk rates updated successfully for " . count($products) . " products.");
        }

        return view('products.bulk-rates', compact('categories'));
    }

    public function exportCsv()
    {
        $products = Product::with(['category', 'brand', 'baseUnit'])->get();
        $filename = "products_catalog_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'SKU', 'Barcode', 'Product Name (English)', 'Product Name (Urdu)', 'Category', 'Brand', 'Base Unit', 'Purchase Cost (PKR)', 'Wholesale Price (PKR)', 'Retail Price (PKR)', 'Total Stock', 'Status']);

            foreach ($products as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->sku,
                    $p->barcode,
                    $p->name,
                    $p->name_ur,
                    $p->category ? $p->category->name : '',
                    $p->brand ? $p->brand->name : '',
                    $p->baseUnit ? $p->baseUnit->name : '',
                    $p->purchase_cost,
                    $p->wholesale_price,
                    $p->retail_price,
                    $p->total_stock,
                    $p->is_active ? 'Active' : 'Inactive',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
