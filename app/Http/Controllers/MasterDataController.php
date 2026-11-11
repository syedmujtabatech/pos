<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\ProductUnit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    // Categories
    public function categories()
    {
        $categories = Category::with('parent')->latest('id')->paginate(20);
        $parentCategories = Category::whereNull('parent_id')->get();
        return view('master.categories', compact('categories', 'parentCategories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ur' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slug = Str::slug($request->name);
        Category::create([
            'name' => $request->name,
            'name_ur' => $request->name_ur,
            'slug' => $slug . '-' . rand(10, 99),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    // Brands & Manufacturers
    public function brands()
    {
        $brands = Brand::latest('id')->paginate(20);
        $manufacturers = Manufacturer::latest('id')->paginate(20);
        return view('master.brands', compact('brands', 'manufacturers'));
    }

    public function storeBrand(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(10, 99),
            'is_active' => true,
        ]);
        return back()->with('success', 'Brand created successfully.');
    }

    public function storeManufacturer(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Manufacturer::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . rand(10, 99),
            'contact' => $request->contact,
            'is_active' => true,
        ]);
        return back()->with('success', 'Manufacturer created successfully.');
    }

    // Units & Conversions
    public function units()
    {
        $units = ProductUnit::all();
        $conversions = UnitConversion::with(['fromUnit', 'toUnit', 'product'])->get();
        return view('master.units', compact('units', 'conversions'));
    }

    public function storeUnit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:20',
        ]);

        ProductUnit::create([
            'name' => $request->name,
            'short_name' => $request->short_name,
            'is_base_unit' => $request->boolean('is_base_unit'),
        ]);

        return back()->with('success', 'Unit created successfully.');
    }

    // Warehouses & Locations
    public function locations()
    {
        $locations = Location::withCount(['stockBalances'])->get();
        return view('master.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:locations,code',
            'type' => 'required|in:godown,retail_shop',
        ]);

        Location::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        return back()->with('success', 'Location / Godown created successfully.');
    }

    // Areas & Market Routes
    public function areas()
    {
        $areas = Area::with(['assignedSalesman'])->withCount('customers')->latest('id')->paginate(20);
        $salesmen = User::whereHas('role', fn($q) => $q->where('slug', 'salesman'))->get();
        return view('master.areas', compact('areas', 'salesmen'));
    }

    public function storeArea(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:100',
            'route_code' => 'nullable|string|max:50',
            'assigned_salesman_id' => 'nullable|exists:users,id',
        ]);

        $area = Area::create([
            'name' => $request->name,
            'city' => $request->city ?: 'Karachi',
            'route_code' => $request->route_code ? strtoupper($request->route_code) : null,
            'assigned_salesman_id' => $request->assigned_salesman_id,
        ]);

        AuditLog::log('create', $area, null, $area->toArray(), "New Market Area/Route '{$area->name}' added");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'area' => $area->load('assignedSalesman'),
                'message' => "Area '{$area->name}' created successfully."
            ]);
        }

        return back()->with('success', "Area / Route '{$area->name}' created successfully.");
    }
}
