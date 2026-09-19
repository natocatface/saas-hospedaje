@php
    if (! function_exists('hpActive')) {
        function hpActive($patterns) {
            return request()->routeIs($patterns) ? 'active' : '';
        }
    }
    $u = auth()->user();
@endphp
<aside class="hp-sidebar">
    <div class="hp-brand">
        <i class="bi bi-buildings-fill"></i>
        <span>Hospedaje Pro</span>
    </div>

    <ul class="hp-menu">
        <li><a class="{{ hpActive('dashboard') }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a></li>
        @if ($u->puede('calendario.ver'))
        <li><a class="{{ hpActive('calendario') }}" href="{{ route('calendario') }}"><i class="bi bi-calendar3"></i> Calendario</a></li>
        @endif

        <li class="hp-heading">Operaciones</li>
        @if ($u->puede('reservas.gestionar'))
        <li><a class="{{ hpActive('reservas.*') }}" href="{{ route('reservas.index') }}"><i class="bi bi-journal-check"></i> Reservas</a></li>
        @endif
        @if ($u->puede('huespedes.gestionar'))
        <li><a class="{{ hpActive('huespedes.*') }}" href="{{ route('huespedes.index') }}"><i class="bi bi-people-fill"></i> Huéspedes</a></li>
        @endif
        @if ($u->puede('facturacion.ver'))
        <li><a class="{{ hpActive('facturacion*') }}" href="{{ route('facturacion') }}"><i class="bi bi-receipt"></i> Facturación</a></li>
        @endif
        @if ($u->puede('caja.gestionar'))
        <li><a class="{{ hpActive('caja') }}" href="{{ route('caja') }}"><i class="bi bi-cash-stack"></i> Caja</a></li>
        @endif
        @if ($u->puede('housekeeping.gestionar'))
        <li><a class="{{ hpActive('housekeeping') }}" href="{{ route('housekeeping') }}"><i class="bi bi-brush"></i> Housekeeping</a></li>
        @endif

        @if ($u->puede('habitaciones.gestionar') || $u->puede('productos.gestionar') || $u->puede('tarifas.gestionar') || $u->puede('reportes.ver'))
        <li class="hp-heading">Administración</li>
        @endif
        @if ($u->puede('habitaciones.gestionar'))
        <li><a class="{{ hpActive('habitaciones.*') }}" href="{{ route('habitaciones.index') }}"><i class="bi bi-door-closed-fill"></i> Habitaciones</a></li>
        @endif
        @if ($u->puede('productos.gestionar'))
        <li><a class="{{ hpActive('productos.*') }}" href="{{ route('productos.index') }}"><i class="bi bi-box-seam"></i> Productos</a></li>
        @endif
        @if ($u->puede('tarifas.gestionar'))
        <li><a class="{{ hpActive('tarifas.*') }}" href="{{ route('tarifas.index') }}"><i class="bi bi-tags-fill"></i> Tarifas Temporada</a></li>
        @endif
        @if ($u->puede('reportes.ver'))
        <li><a class="{{ hpActive('reportes') }}" href="{{ route('reportes') }}"><i class="bi bi-bar-chart-fill"></i> Reportes</a></li>
        @endif

        <li class="hp-heading">Sistema</li>
        @if ($u->esAdmin())
        <li><a class="{{ hpActive('plan') }}" href="{{ route('plan') }}"><i class="bi bi-gem"></i> Mi Plan</a></li>
        <li><a class="{{ hpActive('usuarios.*') }}" href="{{ route('usuarios.index') }}"><i class="bi bi-person-badge-fill"></i> Usuarios</a></li>
        <li><a class="{{ hpActive('roles') }}" href="{{ route('roles') }}"><i class="bi bi-shield-lock-fill"></i> Roles y permisos</a></li>
        <li><a class="{{ hpActive('configuracion') }}" href="{{ route('configuracion') }}"><i class="bi bi-gear-fill"></i> Configuración</a></li>
        <li><a class="{{ hpActive('facturacion.config') }}" href="{{ route('facturacion.config') }}"><i class="bi bi-file-earmark-text-fill"></i> Facturación Electrónica</a></li>
        @endif
        @if ($u->puede('facturacion.ver'))
        <li><a class="{{ hpActive('facturacion.resumen') }}" href="{{ route('facturacion.resumen') }}"><i class="bi bi-collection-fill"></i> Resumen SUNAT</a></li>
        @endif
        <li><a class="{{ hpActive('backup') }}" href="{{ route('backup') }}"><i class="bi bi-hdd-stack-fill"></i> Backup &amp; Sistema</a></li>
    </ul>
</aside>
