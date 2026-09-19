<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registra tu hotel | Hospedaje Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
    <style>
        .m-auth-right{width:54%;max-width:680px;padding-top:2.2rem;padding-bottom:2.2rem;overflow-y:auto;max-height:100vh}
        .reg-sec{font-size:.74rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin:1.4rem 0 .6rem}
        .reg-grid{display:grid;grid-template-columns:1fr 1fr;gap:.9rem}
        .reg-plan{cursor:pointer;border:2px solid #e4ebf3;border-radius:.8rem;padding:.9rem;transition:all .2s;text-align:center}
        .reg-plan:hover{border-color:#9fd6d0}
        .reg-plan.sel{border-color:var(--m-teal);box-shadow:0 0 0 .18rem rgba(15,182,168,.15)}
        .reg-plan input{display:none}
        .reg-plan .pn{font-weight:700;color:var(--m-navy)}
        .reg-plan .pp{color:var(--m-blue);font-weight:800;margin:.2rem 0}
        @media (max-width:575px){.reg-grid{grid-template-columns:1fr}}
    </style>
</head>
<body class="m-body">
<div class="m-auth">
    <!-- Panel de marketing -->
    <div class="m-auth-left">
        <div class="a-logo"><i class="bi bi-buildings-fill"></i></div>
        <h2>Empieza hoy mismo</h2>
        <div class="tagline">30 días de prueba gratis</div>

        <ul class="m-feat-list">
            <li><span class="fi"><i class="bi bi-check2-circle"></i></span><div><div class="ft">Configuración en minutos</div><div class="fd">Crea tu hotel y empieza a operar de inmediato</div></div></li>
            <li><span class="fi"><i class="bi bi-shield-lock"></i></span><div><div class="ft">Datos aislados y seguros</div><div class="fd">La información de tu hotel es solo tuya</div></div></li>
            <li><span class="fi"><i class="bi bi-credit-card"></i></span><div><div class="ft">Sin tarjeta para empezar</div><div class="fd">Prueba todo el plan sin compromiso</div></div></li>
            <li><span class="fi"><i class="bi bi-arrow-up-right-circle"></i></span><div><div class="ft">Escala cuando quieras</div><div class="fd">Cambia de plan según crezca tu hospedaje</div></div></li>
        </ul>

        <div class="m-auth-stats">
            <div class="s"><div class="n">500+</div><div class="l">Hoteles</div></div>
            <div class="s"><div class="n">120k+</div><div class="l">Reservas</div></div>
            <div class="s"><div class="n">99.9%</div><div class="l">Uptime</div></div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="m-auth-right">
        <h1>Crea tu cuenta</h1>
        <p class="hint">Registra tu hotel y gestiónalo en minutos</p>

        @if ($errors->any())
            <div style="background:#fdeceb;color:#b3322c;border-left:4px solid #e0524d;border-radius:.5rem;padding:.7rem .9rem;font-size:.88rem;margin-bottom:1rem">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('registro') }}">
            @csrf
            <div class="reg-sec">Datos del hotel</div>
            <div class="m-field">
                <label>Nombre del hotel *</label>
                <div class="m-input-group"><span class="ig-ic"><i class="bi bi-building"></i></span>
                    <input name="empresa_nombre" value="{{ old('empresa_nombre') }}" placeholder="Hotel Las Palmas" required></div>
            </div>
            <div class="reg-grid">
                <div class="m-field"><label>RUC</label>
                    <div class="m-input-group"><span class="ig-ic"><i class="bi bi-card-text"></i></span>
                        <input name="ruc" value="{{ old('ruc') }}" placeholder="20123456789"></div></div>
                <div class="m-field"><label>Teléfono</label>
                    <div class="m-input-group"><span class="ig-ic"><i class="bi bi-telephone"></i></span>
                        <input name="empresa_telefono" value="{{ old('empresa_telefono') }}" placeholder="(01) 555-1234"></div></div>
            </div>

            <div class="reg-sec">Administrador</div>
            <div class="m-field">
                <label>Tu nombre *</label>
                <div class="m-input-group"><span class="ig-ic"><i class="bi bi-person"></i></span>
                    <input name="name" value="{{ old('name') }}" placeholder="Nombre y apellido" required></div>
            </div>
            <div class="m-field">
                <label>Correo *</label>
                <div class="m-input-group"><span class="ig-ic"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@hotel.com" required></div>
            </div>
            <div class="reg-grid">
                <div class="m-field"><label>Contraseña *</label>
                    <div class="m-input-group"><span class="ig-ic"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" required></div></div>
                <div class="m-field"><label>Confirmar *</label>
                    <div class="m-input-group"><span class="ig-ic"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password_confirmation" required></div></div>
            </div>

            @if ($planes->count())
                <div class="reg-sec">Elige un plan</div>
                <div class="reg-grid" style="grid-template-columns:repeat({{ min(3,$planes->count()) }},1fr)">
                    @foreach ($planes as $i => $plan)
                        <label class="reg-plan {{ $i === 0 ? 'sel' : '' }}">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" {{ $i === 0 ? 'checked' : '' }}>
                            <div class="pn">{{ $plan->nombre }}</div>
                            <div class="pp">@if ($plan->precio_mensual > 0) S/ {{ number_format($plan->precio_mensual, 0) }}<small style="color:#94a3b8;font-weight:500">/mes</small>@else Gratis @endif</div>
                            <div style="font-size:.76rem;color:#64748b">{{ $plan->limiteHabitaciones() }} hab · {{ $plan->limiteUsuarios() }} usuarios</div>
                        </label>
                    @endforeach
                </div>
            @endif

            <button type="submit" class="m-btn m-btn-gold" style="width:100%;margin-top:1.6rem"><i class="bi bi-rocket-takeoff"></i> Crear mi cuenta</button>
        </form>

        <div class="text-center" style="margin-top:1.5rem;font-size:.88rem;color:#64748b">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color:#155e9c;font-weight:600">Inicia sesión</a>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.reg-plan').forEach(el => {
        el.addEventListener('click', () => {
            document.querySelectorAll('.reg-plan').forEach(p => p.classList.remove('sel'));
            el.classList.add('sel');
            el.querySelector('input').checked = true;
        });
    });
</script>
</body>
</html>
