<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Hospedaje Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
</head>
<body class="m-body">
<div class="m-auth">
    <!-- Panel de marketing -->
    <div class="m-auth-left">
        <div class="a-logo"><i class="bi bi-buildings-fill"></i></div>
        <h2>Hospedaje Pro</h2>
        <div class="tagline">Sistema de gestión hotelera</div>
        <div class="m-saas-pill"><i class="bi bi-cloud-check-fill"></i> Aplicativo SaaS · 100% en la nube · Multiempresa</div>

        <ul class="m-feat-list">
            <li><span class="fi"><i class="bi bi-journal-check"></i></span><div><div class="ft">Gestión de Reservas</div><div class="fd">Control total de check-in, check-out y huéspedes</div></div></li>
            <li><span class="fi"><i class="bi bi-calendar3"></i></span><div><div class="ft">Calendario y Disponibilidad</div><div class="fd">Visualiza tu ocupación en tiempo real</div></div></li>
            <li><span class="fi"><i class="bi bi-graph-up-arrow"></i></span><div><div class="ft">Reportes Avanzados</div><div class="fd">Ingresos, ocupación y métricas del negocio</div></div></li>
            <li><span class="fi"><i class="bi bi-phone"></i></span><div><div class="ft">Acceso Multiplataforma</div><div class="fd">Disponible en cualquier dispositivo</div></div></li>
        </ul>

        <div class="m-auth-stats">
            <div class="s"><div class="n">500+</div><div class="l">Hoteles</div></div>
            <div class="s"><div class="n">98%</div><div class="l">Satisfacción</div></div>
            <div class="s"><div class="n">24/7</div><div class="l">Soporte</div></div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="m-auth-right">
        <h1>Bienvenido de vuelta 👋</h1>
        <p class="hint">Ingresa tus credenciales para acceder al sistema</p>

        @if ($errors->any())
            <div style="background:#fdeceb;color:#b3322c;border-left:4px solid #e0524d;border-radius:.5rem;padding:.7rem .9rem;font-size:.88rem;margin-bottom:1rem">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="m-field">
                <label>Correo electrónico</label>
                <div class="m-input-group">
                    <span class="ig-ic"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="admin@hospedaje.com" required autofocus>
                </div>
            </div>
            <div class="m-field">
                <label>Contraseña</label>
                <div class="m-input-group">
                    <span class="ig-ic"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" placeholder="••••••••" required>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center" style="margin-bottom:1.2rem">
                <label style="font-size:.85rem;color:#475569;display:flex;align-items:center;gap:.4rem">
                    <input type="checkbox" name="remember"> Recordarme
                </label>
                <span style="font-size:.85rem;color:#94a3b8">¿Olvidaste tu contraseña?</span>
            </div>
            <button type="submit" class="m-btn m-btn-brand" style="width:100%"><i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión</button>
        </form>

        <div class="m-divider">Cuentas de demostración</div>
        <div class="m-demo">
            <div class="dh"><i class="bi bi-lightning-charge-fill"></i> ACCESO RÁPIDO</div>
            <div class="drow" data-email="admin@hospedaje.com">
                <span class="em">admin@hospedaje.com</span>
                <span class="badge" style="background:#e9eef7;color:#155e9c;border-radius:6px;padding:.2rem .5rem;font-size:.72rem;font-weight:700">Admin</span>
            </div>
            <div class="drow" data-email="recepcion@hospedaje.com">
                <span class="em">recepcion@hospedaje.com</span>
                <span class="badge" style="background:#e6f7f3;color:#0d8a7e;border-radius:6px;padding:.2rem .5rem;font-size:.72rem;font-weight:700">Recepción</span>
            </div>
        </div>

        <div class="text-center" style="margin-top:1.6rem;font-size:.88rem;color:#64748b">
            ¿No tienes cuenta? <a href="{{ route('registro') }}" style="color:#155e9c;font-weight:600">Registra tu hotel</a>
        </div>
        <div class="text-center" style="margin-top:1.4rem;font-size:.78rem;color:#aab6c6">
            © {{ date('Y') }} Hospedaje Pro · Todos los derechos reservados
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.m-demo .drow').forEach(row => {
        row.addEventListener('click', () => {
            document.getElementById('email').value = row.dataset.email;
            document.getElementById('password').value = 'password';
            document.getElementById('password').focus();
        });
    });
</script>
</body>
</html>
