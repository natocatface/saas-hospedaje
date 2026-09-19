<header class="hp-topbar">
    <div class="left">
        <button class="toggle" onclick="hpToggleSidebar()"><i class="bi bi-list"></i></button>
        <span class="d-none d-md-inline"><i class="bi bi-house-door me-1"></i>@yield('breadcrumb', 'Inicio')</span>
    </div>
    <div class="right">
        @php $empresa = auth()->user()->empresa; @endphp
        @if ($empresa)
            <span class="d-none d-lg-inline-flex align-items-center gap-2">
                <i class="bi bi-building text-primary"></i>
                <span class="fw-semibold text-dark">{{ $empresa->nombre }}</span>
                @if ($empresa->plan)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $empresa->plan->nombre }}</span>
                @endif
            </span>
            @if ($empresa->enTrial() && ! $empresa->suscripcionVigente())
                <a href="{{ route('suscripcion.renovar') }}" class="badge bg-info text-decoration-none"><i class="bi bi-stopwatch me-1"></i>Prueba: {{ $empresa->diasRestantes() }} días</a>
            @elseif ($empresa->estadoSuscripcion() === 'vencida')
                <a href="{{ route('suscripcion.renovar') }}" class="badge bg-warning text-dark text-decoration-none"><i class="bi bi-exclamation-triangle me-1"></i>Renovar</a>
            @endif
        @endif
        <span class="d-none d-sm-inline"><i class="bi bi-clock me-1"></i>{{ now()->format('d/m/Y H:i') }}</span>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none text-secondary dropdown-toggle"
               data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2 fs-5"></i>
                <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><span class="dropdown-item-text small text-muted">{{ ucfirst(auth()->user()->rol) }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('cuenta') }}"><i class="bi bi-person me-2"></i>Mi cuenta</a></li>
                @if (auth()->user()->esAdmin())
                <li><a class="dropdown-item" href="{{ route('plan') }}"><i class="bi bi-gem me-2"></i>Mi Plan</a></li>
                <li><a class="dropdown-item" href="{{ route('suscripcion.renovar') }}"><i class="bi bi-credit-card me-2"></i>Suscripción</a></li>
                <li><a class="dropdown-item" href="{{ route('configuracion') }}"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
