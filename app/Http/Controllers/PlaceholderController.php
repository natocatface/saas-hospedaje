<?php

namespace App\Http\Controllers;

class PlaceholderController extends Controller
{
    public function calendario()   { return $this->vista('Calendario', 'calendar3', 'Vista de calendario de reservas y disponibilidad.'); }
    public function facturacion()  { return $this->vista('Facturación', 'receipt', 'Emisión y control de facturas, boletas y pagos.'); }
    public function housekeeping() { return $this->vista('Housekeeping', 'brush', 'Gestión de limpieza y estado de habitaciones.'); }
    public function tarifas()      { return $this->vista('Tarifas de Temporada', 'tags-fill', 'Configuración de tarifas por temporada alta/baja.'); }
    public function reportes()     { return $this->vista('Reportes', 'bar-chart-fill', 'Reportes de ocupación, ingresos y desempeño.'); }
    public function usuarios()     { return $this->vista('Usuarios', 'person-badge-fill', 'Administración de usuarios y roles del sistema.'); }
    public function configuracion(){ return $this->vista('Configuración', 'gear-fill', 'Parámetros generales del hotel y del sistema.'); }
    public function backup()       { return $this->vista('Backup & Sistema', 'hdd-stack-fill', 'Copias de seguridad y herramientas del sistema.'); }

    private function vista(string $titulo, string $icono, string $desc)
    {
        return view('placeholder.index', compact('titulo', 'icono', 'desc'));
    }
}
