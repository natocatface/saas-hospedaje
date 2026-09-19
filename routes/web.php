<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ConsumoController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\FacturacionElectronicaController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\HuespedController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\Plataforma\DashboardController as PlataformaDashboard;
use App\Http\Controllers\Plataforma\EmpresaController as PlataformaEmpresa;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RolPermisoController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\TarifaTemporadaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// Autenticación
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/registro', [RegistroController::class, 'show'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'register']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    // ============ PLATAFORMA (super admin) ============
    Route::middleware('superadmin')->prefix('admin')->name('plataforma.')->group(function () {
        Route::get('/', [PlataformaDashboard::class, 'index'])->name('dashboard');
        Route::get('/empresas', [PlataformaEmpresa::class, 'index'])->name('empresas.index');
        Route::get('/empresas/{empresa}', [PlataformaEmpresa::class, 'show'])->name('empresas.show');
        Route::post('/empresas/{empresa}/toggle', [PlataformaEmpresa::class, 'toggle'])->name('empresas.toggle');
        Route::post('/empresas/{empresa}/plan', [PlataformaEmpresa::class, 'cambiarPlan'])->name('empresas.plan');
    });

    // ============ ÁREA TENANT (hoteles) ============
    Route::middleware('tenant')->group(function () {

        // Siempre accesible (aunque la suscripción esté vencida)
        Route::get('/mi-cuenta', [CuentaController::class, 'edit'])->name('cuenta');
        Route::put('/mi-cuenta', [CuentaController::class, 'update'])->name('cuenta.update');
        Route::get('/renovar', [SuscripcionController::class, 'renovar'])->name('suscripcion.renovar');
        Route::post('/renovar', [SuscripcionController::class, 'pagar'])->name('suscripcion.pagar');

        // Requiere suscripción vigente
        Route::middleware('suscripcion')->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::middleware('puede:calendario.ver')->group(function () {
                Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario');
                Route::get('/calendario/eventos', [CalendarioController::class, 'eventos'])->name('calendario.eventos');
            });

            Route::middleware('puede:reservas.gestionar')->group(function () {
                Route::get('/reservas-tarifa-sugerida', [ReservaController::class, 'tarifaSugerida'])->name('reservas.tarifa-sugerida');
                Route::get('/reservas-disponibilidad', [ReservaController::class, 'disponibilidad'])->name('reservas.disponibilidad');
                Route::resource('reservas', ReservaController::class);
                Route::patch('reservas/{reserva}/estado', [ReservaController::class, 'cambiarEstado'])->name('reservas.estado');
                Route::post('reservas/{reserva}/consumos', [ConsumoController::class, 'store'])->name('reservas.consumos.store');
                Route::delete('reservas/{reserva}/consumos/{consumo}', [ConsumoController::class, 'destroy'])->name('reservas.consumos.destroy');
            });

            Route::middleware('puede:huespedes.gestionar')->group(function () {
                Route::resource('huespedes', HuespedController::class)->parameters(['huespedes' => 'huesped'])->except('show');
            });

            Route::middleware('puede:facturacion.ver')->group(function () {
                Route::get('/facturacion', [FacturaController::class, 'index'])->name('facturacion');
                Route::get('/facturacion/{factura}', [FacturaController::class, 'show'])->name('facturacion.show')->whereNumber('factura');
                Route::get('/facturacion/{factura}/imprimir', [FacturaController::class, 'imprimir'])->name('facturacion.imprimir')->whereNumber('factura');
                Route::patch('/facturacion/{factura}/pagar', [FacturaController::class, 'pagar'])->name('facturacion.pagar');
                Route::post('/facturacion/{factura}/pagos', [PagoController::class, 'store'])->name('facturacion.pago');
                Route::delete('/facturacion/{factura}/pagos/{pago}', [PagoController::class, 'destroy'])->name('facturacion.pago.destroy');

                // Resumen diario de boletas (RC) — antes que las rutas con {factura}
                Route::get('/facturacion/resumen', [FacturacionElectronicaController::class, 'resumenIndex'])->name('facturacion.resumen');
                Route::post('/facturacion/resumen/generar', [FacturacionElectronicaController::class, 'resumenGenerar'])->name('facturacion.resumen.generar');
                Route::post('/facturacion/resumen/{resumen}/consultar', [FacturacionElectronicaController::class, 'resumenConsultar'])->name('facturacion.resumen.consultar')->whereNumber('resumen');
                Route::get('/facturacion/resumen/{resumen}/cdr', [FacturacionElectronicaController::class, 'resumenCdr'])->name('facturacion.resumen.cdr')->whereNumber('resumen');

                // Emisión electrónica del comprobante ante SUNAT
                Route::post('/facturacion/{factura}/emitir-sunat', [FacturacionElectronicaController::class, 'emitir'])->name('facturacion.emitir')->whereNumber('factura');
                Route::get('/facturacion/{factura}/xml', [FacturacionElectronicaController::class, 'descargarXml'])->name('facturacion.xml')->whereNumber('factura');
                Route::get('/facturacion/{factura}/cdr', [FacturacionElectronicaController::class, 'descargarCdr'])->name('facturacion.cdr')->whereNumber('factura');
            });
            Route::patch('/facturacion/{factura}/anular', [FacturaController::class, 'anular'])
                ->name('facturacion.anular')->middleware('puede:facturacion.anular');
            Route::post('/facturacion/{factura}/nota-credito', [FacturacionElectronicaController::class, 'notaCredito'])
                ->name('facturacion.nota-credito')->middleware('puede:facturacion.anular')->whereNumber('factura');

            Route::middleware('puede:housekeeping.gestionar')->group(function () {
                Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping');
                Route::patch('/housekeeping/{habitacion}/estado', [HousekeepingController::class, 'actualizar'])->name('housekeeping.estado');
            });

            Route::middleware('puede:habitaciones.gestionar')->group(function () {
                Route::resource('habitaciones', HabitacionController::class)->except('show');
            });

            Route::middleware('puede:productos.gestionar')->group(function () {
                Route::resource('productos', ProductoController::class)->except('show');
            });

            Route::middleware('puede:tarifas.gestionar')->group(function () {
                Route::resource('tarifas', TarifaTemporadaController::class)->parameters(['tarifas' => 'tarifa'])->except('show');
            });

            Route::middleware('puede:caja.gestionar')->group(function () {
                Route::get('/caja', [CajaController::class, 'index'])->name('caja');
                Route::post('/caja/abrir', [CajaController::class, 'abrir'])->name('caja.abrir');
                Route::post('/caja/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
            });

            Route::middleware('puede:reportes.ver')->group(function () {
                Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
                Route::get('/reportes/export', [ReporteController::class, 'export'])->name('reportes.export');
                Route::get('/reportes/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
            });

            // Backup & Sistema
            Route::get('/backup', [SistemaController::class, 'index'])->name('backup');
            Route::get('/backup/descargar', [SistemaController::class, 'backup'])->name('sistema.backup');
            Route::post('/backup/cache', [SistemaController::class, 'limpiarCache'])->name('sistema.cache');

            // Sistema (solo administradores)
            Route::middleware('admin')->group(function () {
                Route::resource('usuarios', UsuarioController::class)->except('show');
                Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion');
                Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

                // Facturación Electrónica (SUNAT · Perú)
                Route::get('/facturacion/configuracion', [FacturacionElectronicaController::class, 'edit'])->name('facturacion.config');
                Route::put('/facturacion/configuracion', [FacturacionElectronicaController::class, 'update'])->name('facturacion.config.update');
                Route::post('/facturacion/configuracion/probar', [FacturacionElectronicaController::class, 'probar'])->name('facturacion.config.probar');
                Route::get('/roles', [RolPermisoController::class, 'index'])->name('roles');
                Route::put('/roles', [RolPermisoController::class, 'update'])->name('roles.update');
                Route::get('/mi-plan', [PlanController::class, 'index'])->name('plan');
                Route::post('/mi-plan/cambiar', [PlanController::class, 'cambiar'])->name('plan.cambiar');
            });
        });
    });
});
