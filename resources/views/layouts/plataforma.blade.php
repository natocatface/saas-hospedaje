<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Plataforma') | Hospedaje Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="hp-wrapper">
    <aside class="hp-sidebar">
        <div class="hp-brand"><i class="bi bi-globe2"></i> <span>Plataforma</span></div>
        <ul class="hp-menu">
            <li class="hp-heading">Super Admin</li>
            <li><a class="{{ request()->routeIs('plataforma.dashboard') ? 'active' : '' }}" href="{{ route('plataforma.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a class="{{ request()->routeIs('plataforma.empresas.*') ? 'active' : '' }}" href="{{ route('plataforma.empresas.index') }}"><i class="bi bi-building"></i> Empresas</a></li>
        </ul>
    </aside>
    <div class="hp-backdrop" onclick="document.body.classList.remove('hp-open')"></div>

    <div class="hp-main">
        <header class="hp-topbar">
            <div class="left">
                <button class="toggle" onclick="document.body.classList.toggle('hp-open')"><i class="bi bi-list"></i></button>
                <span class="d-none d-md-inline"><i class="bi bi-globe2 me-1"></i>Panel de plataforma</span>
            </div>
            <div class="right">
                <span class="badge bg-dark">SUPER ADMIN</span>
                <span class="d-none d-sm-inline"><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y H:i') }}</span>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-2 fs-5"></i><span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="hp-content">
            @if (session('ok'))
                <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i> {{ session('ok') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
