# Sistema Hospedaje — SaaS (Laravel 11 + MySQL)

Aplicación web para la gestión de un hospedaje/hotel: autenticación, dashboard con KPIs,
y módulos de Habitaciones, Huéspedes y Reservas (con facturación automática en S/ soles).

## Requisitos

- PHP 8.2 o superior
- Composer
- MySQL 5.7+/8.x (XAMPP, Laragon o MySQL nativo)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`

## Instalación (primera vez)

1. **Crear la base de datos** en MySQL:

   ```sql
   CREATE DATABASE saas_hospedaje CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Instalar dependencias** (desde la carpeta del proyecto):

   ```bash
   composer install
   ```

3. **Configurar el entorno.** El archivo `.env` ya viene preparado con:

   ```
   DB_DATABASE=saas_hospedaje
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Si tu MySQL tiene contraseña, edítala en `.env`.

4. **Generar la clave de la aplicación:**

   ```bash
   php artisan key:generate
   ```

5. **Crear las tablas y cargar datos de demostración:**

   ```bash
   php artisan migrate --seed
   ```

6. **Levantar el servidor** (puerto 8010 como en tu diseño):

   ```bash
   php artisan serve --port=8010
   ```

   Abre: http://127.0.0.1:8010

## Credenciales de acceso (demo)

| Rol           | Correo                     | Contraseña |
|---------------|----------------------------|------------|
| Administrador | admin@hospedaje.com        | password   |
| Recepcionista | recepcion@hospedaje.com    | password   |

## Módulos

**Funcionales en esta entrega**

- **Login / Logout** con validación y control de cuentas desactivadas.
- **Dashboard**: habitaciones disponibles/ocupadas/mantenimiento, % de ocupación,
  check-ins/check-outs del día, ingresos del mes, facturas pendientes,
  gráfico de ocupación (7 días), ingresos (6 meses) y acciones rápidas.
- **Habitaciones**: CRUD con tipos, piso, capacidad, precio y estado.
- **Huéspedes**: CRUD con documento (DNI/CE/Pasaporte/RUC), contacto y nacionalidad.
- **Reservas**: CRUD con cálculo automático de noches y total, generación de
  factura (IGV 18%) y cambio de estado (confirmada / check-in / check-out / cancelada)
  que sincroniza el estado de la habitación.
- **Calendario**: vista mensual/semanal/lista (FullCalendar) con las reservas
  coloreadas por estado y enlace directo a cada reserva.
- **Facturación**: listado con filtros y totales, detalle, registro de pago
  (efectivo/tarjeta/Yape/transferencia), anulación y comprobante imprimible.
- **Reportes**: filtro por rango de fechas con ingresos, ocupación, ticket
  promedio, ingresos por día, reservas por estado, habitaciones más rentables
  y exportación a CSV.
- **Usuarios** (solo admin): CRUD con roles (admin / recepcionista / housekeeping)
  y activación de cuentas.
- **Configuración** (solo admin): datos del hotel, RUC, moneda, IGV y horarios
  de check-in/check-out.

**Placeholders (en construcción)**: Housekeeping, Tarifas de Temporada, Backup & Sistema.

## Stack

- **Backend:** Laravel 11, Eloquent ORM
- **Base de datos:** MySQL
- **Frontend:** Blade + Bootstrap 5 + Bootstrap Icons + Chart.js (vía CDN, sin compilación npm)

## Estructura de datos

`users`, `tipo_habitaciones`, `habitaciones`, `huespedes`, `reservas`, `facturas`, `configuracions`.

## Recargar datos de demo

```bash
php artisan migrate:fresh --seed
```

## 🏢 Modo SaaS Multiempresa (multi-tenant)

El sistema ahora es **multi-tenant**: cada hotel/empresa tiene sus datos completamente
aislados mediante una columna `empresa_id` y un *global scope* automático. Un usuario solo
ve y gestiona la información de su propia empresa.

### Registro de nuevas empresas

Cualquiera puede crear su hotel en **`/registro`**: se crea la empresa, su usuario
administrador, la configuración inicial y se asigna un plan. Tras registrarse, el usuario
entra directo a su panel.

### Planes de suscripción

| Plan        | Precio    | Habitaciones | Usuarios | Reportes | Temporadas |
|-------------|-----------|--------------|----------|----------|------------|
| Free        | Gratis    | 10           | 2        | Sí       | No         |
| Pro         | S/ 99/mes | 60           | 10       | Sí       | Sí         |
| Empresarial | S/ 249/mes| Ilimitadas   | Ilimitados | Sí     | Sí         |

Los límites se aplican al crear habitaciones y usuarios. La empresa puede ver su uso y
cambiar de plan en **Mi Plan**.

### ⚠️ Importante: actualizar la base de datos

Esta versión cambia el esquema (agrega `empresa_id` a todas las tablas y crea `planes` y
`empresas`). Ejecuta:

```bash
php artisan migrate:fresh --seed
php artisan optimize:clear
```

> `migrate:fresh` recrea las tablas (borra los datos actuales) y `--seed` recarga la
> empresa demo con sus 42 habitaciones, reservas y planes.

La empresa demo (**Hotel Sistema Hospedaje**) queda en el plan **Pro**. Credenciales:
`admin@hospedaje.com` / `password`.

## 💵 Caja y Pagos parciales (Fase 2)

- **Pagos parciales**: cada factura admite varios pagos (efectivo, tarjeta, Yape,
  transferencia). El sistema lleva el control de **monto pagado** y **saldo**; la factura
  pasa a *pagada* automáticamente al cubrir el total. Desde el detalle de la factura se
  registra cada abono y se ve la barra de progreso del cobro.
- **Caja diaria**: apertura con monto inicial, registro automático de los pagos del turno,
  resumen por método y **arqueo de cierre** (efectivo esperado vs. contado, con diferencia).
  Historial de cierres incluido. Menú: **Caja**.

> Esta fase agrega las tablas `caja_sesiones` y `pagos`. Ejecuta de nuevo:
> ```bash
> php artisan migrate:fresh --seed
> php artisan optimize:clear
> ```

El panel interno fue re-tematizado a la paleta de hospedaje (azul océano + teal + dorado)
para combinar con la landing y el login.

## 🛒 Consumos y productos extra (Fase 3)

- **Catálogo de productos** (menú **Productos**): minibar, restaurante, lavandería,
  servicios y otros, con precio y categoría.
- **Cargos a la cuenta**: desde el detalle de cada reserva se agregan consumos
  (producto + cantidad). El sistema recalcula automáticamente la factura
  (hospedaje + consumos, IGV 18%) y actualiza el saldo. Los consumos también
  aparecen en el comprobante imprimible.
- El resumen de la reserva muestra *Hospedaje + Consumos = Total cuenta*.

> Agrega las tablas `productos` y `consumos`. Ejecuta de nuevo:
> ```bash
> php artisan migrate:fresh --seed
> php artisan optimize:clear
> ```

## 🔐 Roles y permisos finos (Fase 4)

- **Permisos por módulo** asignables a cada rol: ver calendario, gestionar reservas,
  huéspedes, ver/cobrar facturas, **anular facturas** (permiso aparte), caja, housekeeping,
  habitaciones, productos, tarifas y reportes.
- **Pantalla "Roles y permisos"** (solo admin): matriz editable de roles × permisos con
  casillas. El **Administrador** siempre tiene acceso total. Los roles *recepcionista* y
  *housekeeping* se personalizan por empresa.
- Se aplica en tres capas: el **menú** oculta lo no permitido, las **rutas** se protegen con
  el middleware `puede:` y los **botones** sensibles (p. ej. anular factura) se ocultan.
- Cada empresa nueva recibe permisos por defecto al registrarse.

> Agrega la tabla `permiso_rol`. Ejecuta de nuevo:
> ```bash
> php artisan migrate:fresh --seed
> php artisan optimize:clear
> ```

Prueba: inicia sesión como **recepcion@hospedaje.com / password** y verás un menú reducido
(sin Tarifas, Usuarios, Configuración ni anulación de facturas).

## 📊 Reportes PDF y dashboard avanzado (Fase 5)

- **Indicadores hoteleros**: ADR (tarifa media diaria), RevPAR (ingreso por habitación
  disponible) y ocupación, tanto en el **Dashboard** (del mes) como en **Reportes**
  (por rango de fechas).
- **Ocupación e ingresos por tipo de habitación** y ranking de habitaciones más rentables.
- **Exportación a PDF**: botón *PDF* en Reportes que abre una versión imprimible
  (Guardar como PDF desde el navegador), además del CSV existente.

No agrega tablas nuevas; basta con:
```bash
php artisan optimize:clear
```

## ☁️ Funcionalidades SaaS (Fase 6)

- **Panel Super-Admin (plataforma)**: un área aparte en **`/admin`** para el dueño del SaaS.
  Dashboard global (empresas, activas/suspendidas, MRR, totales de uso, ingresos por
  suscripción) y gestión de empresas: ver detalle, **suspender/activar** y **cambiar el plan**
  de cualquier hotel. Acceso con el usuario `superadmin@hospedaje.com / password`.
- **Suscripción y facturación del plan**: período de prueba con cuenta regresiva en la barra
  superior, **vencimiento** que bloquea el acceso (redirige a *Renovar*), pago simulado que
  extiende la vigencia 1 mes y registra el historial de pagos. Las empresas suspendidas no
  pueden operar hasta renovar.
- **Cuenta self-service**: cada usuario edita su **perfil** y **cambia su contraseña** desde
  *Mi cuenta* (menú del usuario).
- **Onboarding**: checklist de *primeros pasos* en el dashboard (crear habitaciones, productos,
  huésped, reserva e invitar al equipo) con barra de progreso; desaparece al completarse.

> Agrega la columna `suscripcion_hasta` y la tabla `suscripcion_pagos`. Ejecuta:
> ```bash
> php artisan migrate:fresh --seed
> php artisan optimize:clear
> ```

Cuentas demo: **superadmin@hospedaje.com** (plataforma), **admin@hospedaje.com** (Hotel
demo, plan Pro), **admin@elviajero.com** (Hostal El Viajero, plan Free en prueba) — todas con
contraseña `password`.
