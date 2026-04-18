<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametrosSeeder extends Seeder
{
    /**
     * Parámetros del sistema almacenados en la tabla `elementos`.
     *
     * company_id = null  →  Parámetro GLOBAL (aplica a todas las empresas si no
     *                        tienen su propio valor configurado).
     * company_id = X     →  Parámetro ESPECÍFICO de la empresa X (tiene prioridad).
     *
     * valor       → campo numérico decimal
     * valor_texto → campo texto (para hosts, correos, contraseñas, etc.)
     *
     * Para insertar parámetros de una empresa concreta, duplique el registro
     * cambiando company_id al ID de la empresa.
     */
    public function run(): void
    {
        $parametros = [

            // ── COTIZACIONES ─────────────────────────────────────────────────
            [
                'codigo'      => 'COT_TOKEN_DIAS',
                'nombre'      => 'Días de validez del token de aprobación (cotización)',
                'valor'       => 30,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'COT_DIAS_VENCIMIENTO',
                'nombre'      => 'Días de vencimiento de cotización cuando no se especifica fecha',
                'valor'       => 30,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],

            // ── NÓMINA / TABLA DE PRECIOS ─────────────────────────────────────
            [
                'codigo'      => 'NOM_HORAS_DIARIAS',
                'nombre'      => 'Horas laborables por día',
                'valor'       => 8,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'NOM_UTILIDAD_PCT',
                'nombre'      => 'Porcentaje de utilidad (ej: 0.315 = 31.5%)',
                'valor'       => 0.315,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'NOM_DIAS_MES',
                'nombre'      => 'Días laborables por mes',
                'valor'       => 26,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],

            // ── CORREO (SMTP) ─────────────────────────────────────────────────
            // Valores por defecto globales. Para personalizar por empresa,
            // agregue registros con el company_id correspondiente.
            [
                'codigo'      => 'MAIL_MAILER',
                'nombre'      => 'Driver de correo (smtp, sendmail, log, etc.)',
                'valor'       => 0,
                'valor_texto' => env('MAIL_MAILER', 'smtp'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_HOST',
                'nombre'      => 'Servidor SMTP (host)',
                'valor'       => 0,
                'valor_texto' => env('MAIL_HOST', 'sandbox.smtp.mailtrap.io'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_PORT',
                'nombre'      => 'Puerto SMTP',
                'valor'       => (int) env('MAIL_PORT', 2525),
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_USERNAME',
                'nombre'      => 'Usuario SMTP',
                'valor'       => 0,
                'valor_texto' => env('MAIL_USERNAME'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_PASSWORD',
                'nombre'      => 'Contraseña SMTP',
                'valor'       => 0,
                'valor_texto' => env('MAIL_PASSWORD'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_ENCRYPTION',
                'nombre'      => 'Cifrado SMTP (tls, ssl o vacío)',
                'valor'       => 0,
                'valor_texto' => env('MAIL_ENCRYPTION', 'tls'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_FROM_ADDRESS',
                'nombre'      => 'Correo remitente (From)',
                'valor'       => 0,
                'valor_texto' => env('MAIL_FROM_ADDRESS', 'noreply@paneladministrativo.local'),
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'MAIL_FROM_NAME',
                'nombre'      => 'Nombre remitente (From Name)',
                'valor'       => 0,
                'valor_texto' => env('MAIL_FROM_NAME', env('APP_NAME', 'Panel Administrativo')),
                'active'      => 1,
                'company_id'  => null,
            ],
            // --- Turnos ---
            [
                'codigo'      => 'TURNO_MAX_ORD',
                'nombre'      => 'Máximo de horas ordinarias por día',
                'valor'       => 7,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            [
                'codigo'      => 'TURNO_MAX_EXTRA',
                'nombre'      => 'Máximo de horas extra por día',
                'valor'       => 2,
                'valor_texto' => null,
                'active'      => 1,
                'company_id'  => null,
            ],
            // --- Seguridad Social ---
            ['codigo'=>'NOM_PCT_SALUD_EMP',    'nombre'=>'% Salud empleado',              'valor'=>0.04,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_PENSION_EMP',   'nombre'=>'% Pensión empleado',            'valor'=>0.04,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_SALUD_EMPR',    'nombre'=>'% Salud empleador',             'valor'=>0.085,   'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_PENSION_EMPR',  'nombre'=>'% Pensión empleador',           'valor'=>0.12,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_SENA',          'nombre'=>'% SENA empleador',              'valor'=>0.02,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_ICBF',          'nombre'=>'% ICBF empleador',              'valor'=>0.03,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_CAJA',          'nombre'=>'% Caja Compensación',           'valor'=>0.04,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            // --- Prestaciones Sociales ---
            ['codigo'=>'NOM_PCT_PRIMA',         'nombre'=>'% Prima servicios (mensual)',   'valor'=>0.0833,  'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_CESANTIAS',     'nombre'=>'% Cesantías (mensual)',         'valor'=>0.0833,  'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_INT_CES', 'nombre'=>'% Intereses cesantías',        'valor'=>0.01,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_PCT_VACACIONES',    'nombre'=>'% Vacaciones',                  'valor'=>0.0417,  'valor_texto'=>null,'active'=>1,'company_id'=>null],
            // --- Factores CST ---
            ['codigo'=>'NOM_FACTOR_RN',         'nombre'=>'Factor recargo nocturno',       'valor'=>1.35,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_HED',        'nombre'=>'Factor hora extra diurna',      'valor'=>1.25,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_HEN',        'nombre'=>'Factor hora extra nocturna',    'valor'=>1.75,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_TDD',        'nombre'=>'Factor trab. dominical diurno', 'valor'=>1.75,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_TDN',        'nombre'=>'Factor trab. dominical nocturno','valor'=>2.10,   'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_HEDD',       'nombre'=>'Factor HE dominical diurna',   'valor'=>2.00,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_FACTOR_HEDN',       'nombre'=>'Factor HE dominical nocturna', 'valor'=>2.50,    'valor_texto'=>null,'active'=>1,'company_id'=>null],
            // --- Topes ---
            ['codigo'=>'NOM_TOPE_AUX_SMLV',    'nombre'=>'Tope aux. transporte (SMLV)',   'valor'=>2,       'valor_texto'=>null,'active'=>1,'company_id'=>null],
            ['codigo'=>'NOM_TOPE_IBC_SMLV',     'nombre'=>'Tope IBC (SMLV)',               'valor'=>25,      'valor_texto'=>null,'active'=>1,'company_id'=>null],
        ];

        foreach ($parametros as $param) {
            DB::table('elementos')->updateOrInsert(
                [
                    'codigo'     => $param['codigo'],
                    'company_id' => $param['company_id'],
                ],
                $param
            );
        }
    }
}
