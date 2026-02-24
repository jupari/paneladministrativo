<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class CompanyConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Compartir datos de empresa con todas las vistas
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->company) {
                $company = Auth::user()->company;

                // Configurar datos dinámicos de la empresa
                $companyConfig = [
                    'name' => $company->name,
                    'logo_url' => $company->getLogoUrl(),
                    'primary_color' => $company->primary_color,
                    'secondary_color' => $company->secondary_color,
                    'theme_settings' => $company->theme_settings ?? [],
                    'is_license_valid' => $company->isLicenseValid(),
                    'license_expires_at' => $company->license_expires_at,
                    'days_until_expiration' => $company->daysUntilExpiration(),
                    'is_expiring_soon' => $company->isLicenseExpiringSoon(),
                    'features' => $company->features ?? [],
                    'settings' => $company->settings ?? []
                ];

                // Compartir con todas las vistas
                $view->with('companyConfig', $companyConfig);

                // Configurar AdminLTE dinámicamente
                $this->configureAdminLTE($company);
            } else {
                // Configuración por defecto cuando no hay empresa
                $view->with('companyConfig', [
                    'name' => 'Sistema Administrativo',
                    'logo_url' => null,
                    'primary_color' => '#007bff',
                    'secondary_color' => '#6c757d',
                    'theme_settings' => [],
                    'is_license_valid' => false,
                    'license_expires_at' => null,
                    'days_until_expiration' => null,
                    'is_expiring_soon' => false,
                    'features' => [],
                    'settings' => []
                ]);
            }
        });
    }

    /**
     * Configurar AdminLTE dinámicamente según la empresa
     */
    private function configureAdminLTE($company)
    {
        // Personalizar título
        Config::set('adminlte.title', $company->name);
        Config::set('adminlte.title_prefix', '');
        Config::set('adminlte.title_postfix', '');

        // Logo personalizado
        if ($company->getLogoUrl()) {
            // Usar logo de la empresa
            Config::set('adminlte.logo', '<b>' . $company->name . '</b>');
            Config::set('adminlte.logo_img', $company->getLogoUrl());
            Config::set('adminlte.logo_img_class', 'brand-image img-circle elevation-3');
            Config::set('adminlte.logo_img_alt', $company->name . ' logo');
            
            // Logo para autenticación
            Config::set('adminlte.auth_logo.enabled', true);
            Config::set('adminlte.auth_logo.img.path', $company->getLogoUrl());
            Config::set('adminlte.auth_logo.img.alt', $company->name . ' logo');
            Config::set('adminlte.auth_logo.img.class', 'elevation-3');
            Config::set('adminlte.auth_logo.img.width', 50);
            Config::set('adminlte.auth_logo.img.height', 50);
        } else {
            // Solo texto cuando no hay logo
            Config::set('adminlte.logo', '<b>' . $company->name . '</b>');
            Config::set('adminlte.logo_img', null);
            Config::set('adminlte.logo_img_alt', $company->name);
        }

        // CSS personalizado
        $this->injectCustomCSS($company);
    }

    /**
     * Inyectar CSS personalizado para la empresa
     */
    private function injectCustomCSS($company)
    {
        $customCSS = "
            <style>
                :root {
                    --company-primary: {$company->primary_color};
                    --company-secondary: {$company->secondary_color};
                }

                /* Logo personalizado en sidebar */
                .brand-link .brand-image {
                    max-height: 35px !important;
                    width: auto !important;
                    margin-top: -3px !important;
                }

                .brand-link .brand-text {
                    color: #c2c7d0 !important;
                    font-weight: 600 !important;
                }

                /* Colores primarios de la empresa */
                .btn-primary {
                    background-color: var(--company-primary) !important;
                    border-color: var(--company-primary) !important;
                }

                .btn-primary:hover {
                    background-color: color-mix(in srgb, var(--company-primary) 85%, black) !important;
                    border-color: color-mix(in srgb, var(--company-primary) 85%, black) !important;
                }

                .main-header .navbar-nav .nav-link:hover {
                    background-color: color-mix(in srgb, var(--company-primary) 10%, white) !important;
                }

                .card-header {
                    background-color: var(--company-primary) !important;
                    color: white !important;
                }

                .breadcrumb-item.active {
                    color: var(--company-primary) !important;
                }

                /* Enlaces y elementos con color primario */
                .text-primary {
                    color: var(--company-primary) !important;
                }

                .bg-primary {
                    background-color: var(--company-primary) !important;
                }

                /* Botones y elementos interactivos */
                .nav-pills .nav-link.active {
                    background-color: var(--company-primary) !important;
                }

                /* DataTables botones */
                .dt-buttons .btn {
                    margin-right: 5px !important;
                }

                .dt-buttons .btn-primary {
                    background-color: var(--company-primary) !important;
                    border-color: var(--company-primary) !important;
                }
            </style>
        ";

        // Agregar CSS específico para el logo si existe
        if ($company->getLogoUrl()) {
            $logoUrl = $company->getLogoUrl();
            $customCSS .= "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Actualizar logo en el sidebar
                        const brandImage = document.querySelector('.brand-link .brand-image');
                        if (brandImage) {
                            brandImage.src = '{$logoUrl}';
                            brandImage.alt = '{$company->name} Logo';
                        }
                        
                        // Actualizar texto del brand si no existe imagen
                        const brandText = document.querySelector('.brand-link .brand-text');
                        if (brandText) {
                            const logoImg = document.querySelector('.brand-link .brand-image');
                            if (logoImg && logoImg.src !== '{$logoUrl}') {
                                brandText.innerHTML = '<b>{$company->name}</b>';
                            }
                        }
                    });
                </script>
            ";
        }

        // Agregar al head de la página
        View::startPush('css');
        echo $customCSS;
        View::stopPush();
    }
}
