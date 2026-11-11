<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | POS System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #059669 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }
    .card-login {
      max-width: 450px;
      width: 100%;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.1);
      background: #ffffff;
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .demo-role-btn {
      font-size: 0.75rem;
      padding: 0.4rem 0.75rem;
      border-radius: 6px;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <div class="card card-login p-4 p-md-5">
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center p-3 bg-success text-white rounded-circle shadow-sm mb-3">
        <i class="ti ti-device-tablet fs-1"></i>
      </div>
      <h3 class="fw-bold mb-1 text-dark">POS</h3>
      <p class="text-muted small mb-0">Retail Point of Sale Counter</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success small mb-3 py-2">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger small mb-3 py-2">
        @foreach($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    <form action="{{ url('/login') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label small fw-semibold">Username or Email</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="ti ti-user text-primary"></i></span>
          <input type="text" name="username" id="usernameInput" class="form-control border-start-0" placeholder="admin or cashier" value="{{ old('username', 'admin') }}" required autofocus>
        </div>
      </div>

      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
          <label class="form-label small fw-semibold mb-0">Password</label>
          <span class="text-muted small">Default: <code>password</code></span>
        </div>
        <div class="input-group mt-1">
          <span class="input-group-text bg-light border-end-0"><i class="ti ti-lock text-primary"></i></span>
          <input type="password" name="password" id="passwordInput" class="form-control border-start-0" placeholder="Password" value="password" required>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
          <label class="form-check-label small text-secondary" for="rememberMe">
            Remember me
          </label>
        </div>
        <span class="text-muted small">Protected Terminal</span>
      </div>

      <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm mb-3 fs-6">
        <i class="ti ti-login me-1"></i> Sign In to Counter
      </button>
    </form>

    <div class="border-top pt-3 mt-2">
      <small class="text-muted fw-semibold d-block mb-2 text-center"><i class="ti ti-bolt text-warning me-1"></i> 1-Click Role Login:</small>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <button type="button" class="btn btn-outline-dark demo-role-btn" onclick="fillCredentials('admin')">
          <i class="ti ti-crown text-warning me-1"></i> Owner / Admin
        </button>
        <button type="button" class="btn btn-outline-success demo-role-btn" onclick="fillCredentials('cashier')">
          <i class="ti ti-device-tablet text-success me-1"></i> Counter Cashier
        </button>
        <button type="button" class="btn btn-outline-primary demo-role-btn" onclick="fillCredentials('manager')">
          <i class="ti ti-user-check text-primary me-1"></i> Store Manager
        </button>
      </div>
    </div>
  </div>

  <script>
    function fillCredentials(username) {
      document.getElementById('usernameInput').value = username;
      document.getElementById('passwordInput').value = 'password';
    }
  </script>
</body>
</html>
