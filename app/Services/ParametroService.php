<?php

namespace App\Services;

use App\Models\Elemento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de parámetros del sistema.
 *
 * Resuelve parámetros desde la tabla `elementos` con soporte multi-empresa:
 *  1. Busca el parámetro con company_id del usuario activo.
 *  2. Si no existe, usa el parámetro global (company_id = NULL).
 *
 * El campo `valor_texto` tiene precedencia sobre `valor` (numérico) para
 * parámetros que contienen cadenas (hosts, contraseñas, correos, etc.).
 *
 * Uso:
 *   app(ParametroService::class)->get('COT_TOKEN_DIAS')
 *   app(ParametroService::class)->getInt('NOM_HORAS_DIARIAS', 8)
 *   app(ParametroService::class)->configurarMailer($companyId)
 */
class ParametroService
{
    private const CACHE_TTL = 600; // 10 minutos

    // ─────────────────────────────────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────────────────────────────────

    /** Valor crudo (string). Usa company_id de sesión/auth si no se especifica. */
    public function get(string $codigo, mixed $default = null, ?int $companyId = null): mixed
    {
        $valor = $this->cargar($codigo, $companyId ?? $this->resolverCompanyId());
        return $valor !== null ? $valor : $default;
    }

    public function getInt(string $codigo, int $default = 0, ?int $companyId = null): int
    {
        return (int) $this->get($codigo, $default, $companyId);
    }

    public function getFloat(string $codigo, float $default = 0.0, ?int $companyId = null): float
    {
        return (float) $this->get($codigo, $default, $companyId);
    }

    public function getStr(string $codigo, string $default = '', ?int $companyId = null): string
    {
        return (string) $this->get($codigo, $default, $companyId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea o actualiza un parámetro.
     *
     * @param string      $codigo
     * @param mixed       $valor    Valor numérico → guardado en `valor`.
     *                              Valor string no numérico → guardado en `valor_texto`.
     * @param string      $nombre   Descripción legible del parámetro.
     * @param int|null    $companyId NULL = global.
     */
    public function set(string $codigo, mixed $valor, string $nombre = '', ?int $companyId = null): void
    {
        $esNumerico = is_numeric($valor);

        Elemento::updateOrCreate(
            ['codigo' => $codigo, 'company_id' => $companyId],
            [
                'nombre'      => $nombre ?: $codigo,
                'valor'       => $esNumerico ? (float) $valor : 0,
                'valor_texto' => !$esNumerico ? (string) $valor : null,
                'active'      => 1,
            ]
        );

        Cache::forget($this->cacheKey($codigo, $companyId));
        Cache::forget($this->cacheKey($codigo, null)); // invalida también la global
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIGURACIÓN DE CORREO POR EMPRESA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Aplica la configuración de correo de la empresa al mailer activo.
     * Debe llamarse antes de Mail::send() para correos multi-empresa.
     *
     * Parámetros leídos de `elementos`:
     *   MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD,
     *   MAIL_ENCRYPTION, MAIL_FROM_ADDRESS, MAIL_FROM_NAME
     */
    public function configurarMailer(?int $companyId): void
    {
        if (!$companyId) {
            return; // Usa config de .env por defecto
        }

        $map = [
            'MAIL_HOST'         => ['mail.mailers.smtp.host',        null],
            'MAIL_PORT'         => ['mail.mailers.smtp.port',        null],
            'MAIL_USERNAME'     => ['mail.mailers.smtp.username',    null],
            'MAIL_PASSWORD'     => ['mail.mailers.smtp.password',    null],
            'MAIL_ENCRYPTION'   => ['mail.mailers.smtp.encryption',  null],
            'MAIL_MAILER'       => ['mail.default',                   null],
            'MAIL_FROM_ADDRESS' => ['mail.from.address',              null],
            'MAIL_FROM_NAME'    => ['mail.from.name',                 null],
        ];

        $applied = [];
        foreach ($map as $codigo => [$configKey, $_]) {
            $valor = $this->cargar($codigo, $companyId);
            if ($valor !== null) {
                Config::set($configKey, $valor);
                $applied[] = $codigo;
            }
        }

        if (!empty($applied)) {
            Log::debug('[ParametroService] Mailer configurado para empresa', [
                'company_id' => $companyId,
                'params'     => $applied,
            ]);
        }
    }

    /**
     * Retorna la configuración de correo de una empresa como array,
     * para visualización (oculta la contraseña).
     */
    public function getMailConfig(?int $companyId): array
    {
        $codigos = [
            'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT',
            'MAIL_USERNAME', 'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME',
        ];

        $config = [];
        foreach ($codigos as $codigo) {
            $config[$codigo] = $this->get($codigo, null, $companyId);
        }

        // Indica si hay contraseña configurada (sin exponerla)
        $config['MAIL_PASSWORD'] = $this->get('MAIL_PASSWORD', null, $companyId) !== null
            ? '••••••••'
            : null;

        return $config;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Carga el valor: primero busca por company_id, luego fallback a global (NULL).
     */
    private function cargar(string $codigo, ?int $companyId): ?string
    {
        // Específico de empresa
        if ($companyId) {
            $val = Cache::remember(
                $this->cacheKey($codigo, $companyId),
                self::CACHE_TTL,
                fn () => $this->leerBD($codigo, $companyId)
            );
            if ($val !== null) {
                return $val;
            }
        }

        // Fallback global (company_id IS NULL)
        return Cache::remember(
            $this->cacheKey($codigo, null),
            self::CACHE_TTL,
            fn () => $this->leerBD($codigo, null)
        );
    }

    /**
     * Consulta directa a BD: prefiere valor_texto, luego valor numérico.
     */
    private function leerBD(string $codigo, ?int $companyId): ?string
    {
        $query = Elemento::where('codigo', $codigo)
            ->where('active', 1);

        if ($companyId === null) {
            $query->whereNull('company_id');
        } else {
            $query->where('company_id', $companyId);
        }

        $row = $query->first(['valor', 'valor_texto']);

        if (!$row) {
            return null;
        }

        // valor_texto tiene prioridad (para strings como correos, hosts, etc.)
        if ($row->valor_texto !== null && $row->valor_texto !== '') {
            return $row->valor_texto;
        }

        return (string) $row->valor;
    }

    /** Resuelve company_id del contexto actual (sesión o usuario autenticado). */
    private function resolverCompanyId(): ?int
    {
        $id = session('company_id') ?? auth()->user()?->company_id;
        return $id ? (int) $id : null;
    }

    private function cacheKey(string $codigo, ?int $companyId): string
    {
        return 'parametro.' . ($companyId ?? 'global') . '.' . strtolower($codigo);
    }
}

