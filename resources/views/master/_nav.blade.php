<div class="card shadow-sm mb-4 border-0 bg-white">
  <div class="card-body p-2">
    <ul class="nav nav-pills gap-2 flex-wrap">
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('products*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('products.index') }}">
          <i class="ti ti-box-seam me-1"></i> Products (پروڈکٹس / آئٹمز)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('categories*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('categories.index') }}">
          <i class="ti ti-category me-1"></i> Categories (کیٹیگریز)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('brands*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('brands.index') }}">
          <i class="ti ti-tag me-1"></i> Brands & Companies (برانڈز)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('units*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('units.index') }}">
          <i class="ti ti-ruler-2 me-1"></i> Units of Measure (پیمائش کے یونٹس)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('locations*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('locations.index') }}">
          <i class="ti ti-building-warehouse me-1"></i> Warehouses & Godowns (گودام)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link py-2 px-3 {{ request()->routeIs('areas*') ? 'active bg-primary text-white' : 'text-dark border bg-light' }}" href="{{ route('areas.index') }}">
          <i class="ti ti-map-pin me-1"></i> Market Areas & Routes (مارکیٹ روٹس)
        </a>
      </li>
    </ul>
  </div>
</div>
