<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class ConfigureCompanyBranding
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->company) {
            $company = Auth::user()->company;

            // Configurar AdminLTE dinámicamente
            Config::set([
                'adminlte.title' => $company->name,
                'adminlte.title_prefix' => '',
                'adminlte.title_postfix' => '',
                'adminlte.logo' => '<b>' . $company->name . '</b>',
                'adminlte.logo_img_alt' => $company->name . ' Logo',
                'adminlte.usermenu_header_class' => 'text-white',
            ]);

            // Si tiene logo, configurarlo
            if ($company->getLogoUrl()) {
                Config::set('adminlte.logo_img', $company->getLogoUrl());
            }

            // Compartir datos de empresa con todas las vistas
            View::share('currentCompany', $company);

            // Preparar CSS dinámico con colores de la empresa
            if ($company->primary_color) {
                $primaryColor = $company->primary_color;
                $rgbValues = $this->hexToRgb($primaryColor);

                $dynamicCSS = "
                <style id='company-branding-css'>
                :root {
                    --company-primary: {$primaryColor} !important;
                    --company-primary-rgb: {$rgbValues['r']}, {$rgbValues['g']}, {$rgbValues['b']} !important;" .
                    ($company->secondary_color ? "--company-secondary: {$company->secondary_color} !important;" : "") . "
                }

                /* Personalizar user menu header con color de empresa */
                .user-header {
                    background: linear-gradient(135deg, {$primaryColor}, rgba({$rgbValues['r']}, {$rgbValues['g']}, {$rgbValues['b']}, 0.8)) !important;
                    color: white !important;
                }

                .user-header p {
                    color: white !important;
                }

                .user-header small {
                    color: rgba(255, 255, 255, 0.9) !important;
                    font-weight: 500 !important;
                }
                </style>";

                View::share('companyDynamicCSS', $dynamicCSS);
            }
        }

        return $next($request);
    }

    /**
     * Convert hex color to RGB values
     */
    private function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }
}
