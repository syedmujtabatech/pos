<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'POS Counter') | POS</title>

  <!-- Google Fonts: Poppins & Noto Nastaliq Urdu -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Favicons -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon_io/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon_io/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon_io/favicon-16x16.png') }}">

  <!-- Bootstrap 5.3.3 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tabler Icons Webfont CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

  <!-- InApp Custom Theme CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/inapp-theme.css') }}">

  <style>
    .urdu-text { font-family: 'Noto Nastaliq Urdu', serif; }
  </style>

  @stack('styles')
</head>

<body>
  <!-- Overlay for Mobile Sidebar -->
  <div id="overlay" class="overlay"></div>

  <!-- TOPBAR -->
  <nav id="topbar" class="navbar fixed-top topbar px-3 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <!-- Desktop Sidebar Toggle -->
      <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-sm rounded-2 text-secondary" title="Toggle Sidebar">
        <i class="ti ti-layout-sidebar-left-expand fs-5"></i>
      </button>

      <!-- Mobile Sidebar Toggle -->
      <button id="mobileBtn" class="btn btn-light btn-sm d-lg-none text-secondary" title="Open Menu">
        <i class="ti ti-layout-sidebar-left-expand fs-5"></i>
      </button>

      <!-- Shop Brand Title Badge -->
      <div class="d-none d-md-flex align-items-center ms-2">
        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 fs-7 fw-bold">
          <i class="ti ti-device-tablet me-1"></i>POS &bull; Retail Store
        </span>
      </div>
    </div>

    <!-- Right Header Actions -->
    <div class="d-flex align-items-center gap-3">
      <!-- Quick Action: POS Counter (Always prominent) -->
      <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm px-3 fw-bold">
        <i class="ti ti-device-tablet"></i>
        <span>POS Counter Terminal</span>
      </a>

      <!-- Quick Action: Wholesale Bill -->
      @if(auth()->user()->hasPermission('sales.create'))
        <a href="{{ route('sales.wholesale.create') }}" class="btn btn-sm btn-primary d-none d-sm-inline-flex align-items-center gap-1 shadow-sm px-3">
          <i class="ti ti-plus"></i>
          <span>New Wholesale Bill</span>
        </a>
      @endif

      <!-- Notifications Dropdown -->
      <div class="dropdown">
        <a class="btn btn-light btn-sm rounded-circle position-relative p-2 text-secondary" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="ti ti-bell fs-5"></i>
          @php
            $nearExpiryCount = \App\Models\InventoryBatch::where('quantity', '>', 0)
              ->whereNotNull('expiry_date')
              ->where('expiry_date', '<=', now()->addDays(90))
              ->count();
          @endphp
          @if($nearExpiryCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
              {{ $nearExpiryCount }}
            </span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0" style="min-width: 300px;">
          <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
            <span class="fw-bold small">System Notifications</span>
            <span class="badge bg-danger rounded-pill">{{ $nearExpiryCount }} Alerts</span>
          </div>
          <div class="p-2">
            @if($nearExpiryCount > 0)
              <a href="{{ route('reports.index', ['type' => 'near_expiry']) }}" class="dropdown-item p-2 rounded small d-flex align-items-center gap-2 text-wrap">
                <i class="ti ti-alert-triangle text-warning fs-5"></i>
                <div>
                  <div class="fw-semibold">{{ $nearExpiryCount }} batches expiring soon</div>
                  <div class="text-muted" style="font-size: 11px;">FEFO Expiry alert for warehouse stock</div>
                </div>
              </a>
            @else
              <div class="text-center py-3 text-muted small">No active warnings.</div>
            @endif
          </div>
          <div class="border-top p-2 text-center">
            <a href="{{ route('reports.index', ['type' => 'near_expiry']) }}" class="text-primary small text-decoration-none">View All Alerts</a>
          </div>
        </div>
      </div>

      <!-- Staff Messages Dropdown -->
      @php
        $navUnreadCount = \App\Models\InternalMessage::where('receiver_id', auth()->id())->where('is_read', false)->count();
        $navRecentMessages = \App\Models\InternalMessage::with('sender')->where('receiver_id', auth()->id())->latest()->take(4)->get();
      @endphp
      <div class="dropdown">
        <a class="btn btn-light btn-sm rounded-circle position-relative p-2 text-secondary" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Staff Messages">
          <i class="ti ti-mail fs-5"></i>
          @if($navUnreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
              {{ $navUnreadCount }}
            </span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0" style="min-width: 320px;">
          <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light">
            <span class="fw-bold small"><i class="ti ti-mail me-1 text-primary"></i>Staff Messages</span>
            @if($navUnreadCount > 0)
              <span class="badge bg-danger rounded-pill">{{ $navUnreadCount }} New</span>
            @endif
          </div>
          <div class="p-2" style="max-height: 250px; overflow-y: auto;">
            @forelse($navRecentMessages as $nMsg)
              <a href="{{ route('messages.index') }}" class="dropdown-item p-2 rounded small d-flex align-items-center gap-2 {{ !$nMsg->is_read ? 'bg-light fw-bold' : '' }}">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                  {{ strtoupper(substr($nMsg->sender->name ?? 'S', 0, 1)) }}
                </div>
                <div class="text-truncate" style="max-width: 230px;">
                  <div class="text-dark small text-truncate">{{ $nMsg->subject }}</div>
                  <div class="text-muted" style="font-size: 11px;">From {{ $nMsg->sender->name ?? 'Staff' }} &bull; {{ $nMsg->created_at->diffForHumans() }}</div>
                </div>
              </a>
            @empty
              <div class="text-center py-3 text-muted small">No messages yet.</div>
            @endforelse
          </div>
          <div class="border-top p-2 text-center bg-light">
            <a href="{{ route('messages.index') }}" class="text-primary small text-decoration-none fw-semibold">Open All Messages</a>
          </div>
        </div>
      </div>

      <!-- User Profile Dropdown -->
      <div class="dropdown">
        <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="d-flex align-items-center text-decoration-none gap-2">
          <div class="avatar avatar-sm">
            <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="Avatar" class="rounded-circle border">
          </div>
          <div class="d-none d-md-block text-start" style="line-height: 1.2;">
            <span class="fw-semibold d-block text-dark small">{{ auth()->user()->name ?? 'Staff User' }}</span>
            <span class="text-muted" style="font-size: 11px;">{{ auth()->user()->role->name ?? 'Staff' }}</span>
          </div>
          <i class="ti ti-chevron-down text-muted small d-none d-md-inline"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end shadow-sm border p-0 mt-2" style="min-width: 220px;">
          <div class="p-3 border-bottom bg-light">
            <h6 class="mb-0 fw-bold">{{ auth()->user()->name ?? 'Staff' }}</h6>
            <small class="text-muted">{{ auth()->user()->email ?? auth()->user()->username }}</small>
            <div class="mt-1">
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ auth()->user()->role->name ?? 'Staff' }}</span>
            </div>
          </div>
          <div class="p-2">
            @if(auth()->user()->hasPermission('settings.manage'))
              <a class="dropdown-item py-2 rounded small" href="{{ route('settings.index') }}">
                <i class="ti ti-settings me-2"></i>Company Settings
              </a>
            @endif
            @if(auth()->user()->hasPermission('reports.view'))
              <a class="dropdown-item py-2 rounded small" href="{{ route('reports.index', ['type' => 'audit_logs']) }}">
                <i class="ti ti-history me-2"></i>Audit Activity Logs
              </a>
            @endif
            <hr class="dropdown-divider my-1">
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="dropdown-item py-2 rounded small text-danger">
                <i class="ti ti-logout me-2"></i>Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- SIDEBAR (Modern Retail POS Navigation) -->
  <aside id="sidebar" class="sidebar">
    <!-- Brand Logo Area -->
    <a href="{{ route('pos.index') }}" class="logo-area d-flex align-items-center gap-2 text-decoration-none py-3 px-3 border-bottom">
      <div class="rounded-3 bg-success text-white p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
        <i class="ti ti-device-tablet fs-4"></i>
      </div>
      <div class="logo-title">
        <span class="fw-bold fs-6 text-dark d-block" style="line-height: 1.1;">POS</span>
        <span class="text-muted" style="font-size: 11px;">Retail Terminal</span>
      </div>
    </a>

    <!-- Retail POS Navigation -->
    <ul class="nav flex-column pb-5 mt-2">
      <li class="nav-header text-uppercase px-3 pt-2 pb-1 text-muted" style="font-size: 11px; font-weight: 700;">Counter</li>
      <li>
        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-device-tablet text-success fs-5"></i>
          <span class="nav-text fw-bold">POS Counter</span>
        </a>
      </li>
      <li>
        <a href="{{ route('pos.invoices') }}" class="nav-link {{ request()->routeIs('pos.invoices') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-receipt text-primary fs-5"></i>
          <span class="nav-text">Sales &amp; Invoices</span>
        </a>
      </li>
      <li>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-layout-dashboard text-info fs-5"></i>
          <span class="nav-text">Dashboard</span>
        </a>
      </li>

      <li class="nav-header text-uppercase px-3 pt-3 pb-1 text-muted" style="font-size: 11px; font-weight: 700;">Inventory &amp; Khata</li>
      <li>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-box text-warning fs-5"></i>
          <span class="nav-text">Products &amp; Stock</span>
        </a>
      </li>
      <li>
        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-users text-danger fs-5"></i>
          <span class="nav-text">Customer Khata (Udhar)</span>
        </a>
      </li>
      <li>
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-truck text-secondary fs-5"></i>
          <span class="nav-text">Suppliers</span>
        </a>
      </li>

      <li class="nav-header text-uppercase px-3 pt-3 pb-1 text-muted" style="font-size: 11px; font-weight: 700;">Reports &amp; Setup</li>
      <li>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-chart-bar text-primary fs-5"></i>
          <span class="nav-text">Sales Reports</span>
        </a>
      </li>
      <li>
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-settings text-secondary fs-5"></i>
          <span class="nav-text">Store Settings</span>
        </a>
      </li>
      <li>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users*') ? 'active' : '' }} d-flex align-items-center gap-2">
          <i class="ti ti-user-shield text-dark fs-5"></i>
          <span class="nav-text">Cashier &amp; Staff</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- MAIN CONTENT -->
  <main id="content" class="content">
    <div class="container-fluid">
      <!-- Flash Messages -->
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
          <i class="ti ti-circle-check fs-4 me-2"></i>
          <div>{{ session('success') }}</div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
          <i class="ti ti-alert-triangle fs-4 me-2"></i>
          <div>{{ session('error') }}</div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
          <i class="ti ti-info-circle fs-4 me-2"></i>
          <div>{{ session('info') }}</div>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
          <div class="fw-bold mb-1"><i class="ti ti-alert-circle me-1"></i>Please fix the following errors:</div>
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <!-- Page Content -->
      @yield('content')

      <!-- Footer -->
      <footer class="text-center py-4 mt-5 text-secondary border-top small">
        <p class="mb-0">
          <strong>POS</strong> &bull; Point of Sale &amp; Inventory Management System
        </p>
      </footer>
    </div>
  </main>

  <!-- Bootstrap 5 JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- ApexCharts CDN -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <!-- InApp Sidebar Toggle Script -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sidebar = document.getElementById('sidebar');
      const content = document.getElementById('content');
      const topbar = document.getElementById('topbar');
      const toggleBtn = document.getElementById('toggleBtn');
      const mobileBtn = document.getElementById('mobileBtn');
      const overlay = document.getElementById('overlay');

      // Desktop collapse
      if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
          if (sidebar) sidebar.classList.toggle('collapsed');
          if (content) content.classList.toggle('full');
          if (topbar) topbar.classList.toggle('full');
        });
      }

      // Mobile open
      if (mobileBtn) {
        mobileBtn.addEventListener('click', function () {
          if (sidebar) sidebar.classList.add('mobile-show');
          if (overlay) overlay.classList.add('show');
        });
      }

      // Click outside to close
      if (overlay) {
        overlay.addEventListener('click', function () {
          if (sidebar) sidebar.classList.remove('mobile-show');
          if (overlay) overlay.classList.remove('show');
        });
      }
    });
  </script>

  @stack('scripts')
</body>
</html>
