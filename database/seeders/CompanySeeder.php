<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear empresa principal Minduval
        $minduval = Company::create([
            'name' => 'MINDUVAL',
            'legal_name' => 'MINDUVAL S.A.S.',
            'nit' => '900123456-1',
            'email' => 'info@minduval.com',
            'phone' => '+57 300 123 4567',
            'address' => 'Carrera 123 # 45-67, Bogotá D.C., Colombia',
            'primary_color' => '#1f2937',
            'secondary_color' => '#3b82f6',
            'theme_settings' => [
                'sidebar_color' => 'dark',
                'navbar_color' => 'white',
                'font_family' => 'Inter',
                'logo_position' => 'left'
            ],
            'is_active' => true,
            'license_expires_at' => Carbon::now()->addYear(), // Licencia válida por 1 año
            'license_type' => 'premium',
            'max_users' => 50,
            'features' => [
                'cotizaciones',
                'inventario',
                'produccion',
                'terceros',
                'reportes',
                'multiempresa',
                'api_access'
            ],
            'settings' => [
                'timezone' => 'America/Bogota',
                'currency' => 'COP',
                'date_format' => 'Y-m-d',
                'decimal_places' => 2,
                'show_prices_with_tax' => true
            ],
            'notes' => 'Empresa principal del sistema - Configuración completa'
        ]);

        // Crear empresa de prueba para demostrar multiempresa
        $empresaDemo = Company::create([
            'name' => 'EMPRESA DEMO',
            'legal_name' => 'Empresa Demo S.A.S.',
            'nit' => '900654321-9',
            'email' => 'demo@empresa.com',
            'phone' => '+57 301 987 6543',
            'address' => 'Avenida Demo 456, Medellín, Colombia',
            'primary_color' => '#059669',
            'secondary_color' => '#fbbf24',
            'theme_settings' => [
                'sidebar_color' => 'light',
                'navbar_color' => 'green',
                'font_family' => 'Roboto',
                'logo_position' => 'center'
            ],
            'is_active' => true,
            'license_expires_at' => Carbon::now()->addMonths(6), // Licencia válida por 6 meses
            'license_type' => 'standard',
            'max_users' => 10,
            'features' => [
                'cotizaciones',
                'inventario',
                'terceros',
                'reportes'
            ],
            'settings' => [
                'timezone' => 'America/Bogota',
                'currency' => 'COP',
                'date_format' => 'd/m/Y',
                'decimal_places' => 2,
                'show_prices_with_tax' => false
            ],
            'notes' => 'Empresa de prueba para demostrar funcionalidades multi-empresa'
        ]);

        // Asignar usuarios existentes a Minduval (migración de datos existentes)
        User::whereNull('company_id')->update(['company_id' => $minduval->id]);

        echo "✅ Empresas creadas:\n";
        echo "   - MINDUVAL (ID: {$minduval->id}) - Licencia válida hasta: {$minduval->license_expires_at->format('d/m/Y')}\n";
        echo "   - EMPRESA DEMO (ID: {$empresaDemo->id}) - Licencia válida hasta: {$empresaDemo->license_expires_at->format('d/m/Y')}\n";
        echo "✅ Usuarios existentes asignados a MINDUVAL\n";
    }
}
