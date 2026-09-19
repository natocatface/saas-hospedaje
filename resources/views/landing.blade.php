<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospedaje Pro — Gestiona tu hotel de forma inteligente</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
</head>
<body class="m-body">

<!-- Navbar -->
<nav class="m-nav" id="nav">
    <a href="{{ route('landing') }}" class="m-brand"><span class="m-logo"><i class="bi bi-buildings-fill"></i></span> Hospedaje Pro</a>
    <div class="m-navlinks">
        <a href="#funciones">Funciones</a>
        <a href="#precios">Precios</a>
        <a href="{{ route('login') }}">Iniciar sesión</a>
        <a href="{{ route('registro') }}" class="m-btn m-btn-brand"><i class="bi bi-rocket-takeoff"></i> Prueba gratis</a>
    </div>
</nav>

<!-- Hero -->
<header class="m-hero">
    <div class="m-hero-inner">
        <span class="m-badge">🏨 Plataforma SaaS #1 para Hoteles y Hospedajes en LATAM</span>
        <h1>Gestiona tu hotel<br><span class="m-gradient-text">de forma inteligente</span></h1>
        <p class="lead">Todo lo que necesitas para administrar reservas, huéspedes, facturación y habitaciones. Sin complicaciones, desde cualquier dispositivo.</p>
        <div class="m-hero-cta">
            <a href="{{ route('registro') }}" class="m-btn m-btn-gold"><i class="bi bi-rocket-takeoff"></i> Comenzar gratis — 30 días</a>
            <a href="#precios" class="m-btn m-btn-ghost"><i class="bi bi-tag"></i> Ver precios</a>
        </div>
        <div class="m-stats">
            <div class="m-stat"><div class="n"><span>500+</span></div><div class="l">Hoteles activos</div></div>
            <div class="m-stat"><div class="n"><span>120k+</span></div><div class="l">Reservas gestionadas</div></div>
            <div class="m-stat"><div class="n"><span>99.9%</span></div><div class="l">Uptime garantizado</div></div>
            <div class="m-stat"><div class="n"><span>30 días</span></div><div class="l">Prueba gratuita</div></div>
        </div>
    </div>
</header>

<!-- Funciones -->
<section class="m-section" id="funciones">
    <h2>Todo tu hospedaje en un solo lugar</h2>
    <p class="sub">Una plataforma completa pensada para la operación diaria de hoteles, hostales y casas de hospedaje.</p>
    <div class="m-feature-grid">
        <div class="m-feature"><div class="ic"><i class="bi bi-journal-check"></i></div><h3>Reservas y Check-in</h3><p>Crea reservas en segundos, controla check-in/check-out y el estado de cada habitación en tiempo real.</p></div>
        <div class="m-feature"><div class="ic"><i class="bi bi-calendar3"></i></div><h3>Calendario y Disponibilidad</h3><p>Visualiza la ocupación por día y habitación con un calendario claro e interactivo.</p></div>
        <div class="m-feature gold"><div class="ic"><i class="bi bi-receipt"></i></div><h3>Facturación e IGV</h3><p>Emite comprobantes, registra pagos (efectivo, tarjeta, Yape) y controla las cuentas por cobrar.</p></div>
        <div class="m-feature"><div class="ic"><i class="bi bi-brush"></i></div><h3>Housekeeping</h3><p>Tablero de limpieza y mantenimiento para coordinar al equipo y dejar las habitaciones listas.</p></div>
        <div class="m-feature"><div class="ic"><i class="bi bi-bar-chart-line"></i></div><h3>Reportes y Tarifas</h3><p>Ingresos, ocupación, habitaciones más rentables y tarifas por temporada alta o baja.</p></div>
        <div class="m-feature gold"><div class="ic"><i class="bi bi-phone"></i></div><h3>Multiplataforma</h3><p>Diseño responsivo: administra tu hotel desde la computadora, la tablet o el celular.</p></div>
    </div>
</section>

<!-- Precios -->
<section class="m-pricing">
    <div class="m-section" id="precios">
        <h2>Planes para cada hospedaje</h2>
        <p class="sub">Empieza gratis y escala cuando lo necesites. Sin permanencia.</p>
        <div class="m-price-grid">
            @foreach ($planes as $plan)
                <div class="m-price {{ $plan->slug === 'pro' ? 'feat' : '' }}">
                    @if ($plan->slug === 'pro')<span class="tag">MÁS POPULAR</span>@endif
                    <h3>{{ $plan->nombre }}</h3>
                    <div class="amt">
                        @if ($plan->precio_mensual > 0) S/ {{ number_format($plan->precio_mensual, 0) }}<small>/mes</small>
                        @else Gratis @endif
                    </div>
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i> {{ $plan->limiteHabitaciones() }} habitaciones</li>
                        <li><i class="bi bi-check-circle-fill"></i> {{ $plan->limiteUsuarios() }} usuarios</li>
                        <li><i class="bi bi-{{ $plan->permite_reportes ? 'check-circle-fill' : 'dash-circle text-muted' }}"></i> Reportes y exportación</li>
                        <li><i class="bi bi-{{ $plan->permite_temporadas ? 'check-circle-fill' : 'dash-circle text-muted' }}"></i> Tarifas de temporada</li>
                    </ul>
                    <a href="{{ route('registro') }}" class="m-btn {{ $plan->slug === 'pro' ? 'm-btn-gold' : 'm-btn-brand' }}" style="width:100%">Elegir {{ $plan->nombre }}</a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA final -->
<section class="m-cta-band">
    <h2>Lleva tu hotel al siguiente nivel</h2>
    <p>Únete a cientos de hospedajes que ya gestionan su operación con Hospedaje Pro.</p>
    <a href="{{ route('registro') }}" class="m-btn m-btn-gold"><i class="bi bi-rocket-takeoff"></i> Crear mi cuenta gratis</a>
</section>

<footer class="m-foot">
    © {{ date('Y') }} Hospedaje Pro · Plataforma de gestión hotelera ·
    <a href="{{ route('login') }}" style="color:#9fb2cb">Iniciar sesión</a>
</footer>

<script>
    const nav = document.getElementById('nav');
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 30);
    document.addEventListener('scroll', onScroll); onScroll();
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const t = document.querySelector(a.getAttribute('href'));
            if (t){ e.preventDefault(); t.scrollIntoView({behavior:'smooth'}); }
        });
    });
</script>
</body>
</html>
