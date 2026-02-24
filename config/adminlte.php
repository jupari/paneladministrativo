<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Sysworks',
    'title_prefix' => 'Sysworks',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => env('APP_LOGO', '<b>Sysworks</b>'),
    'logo_img' => env('APP_LOGO_IMG', 'vendor/adminlte/dist/img/logoappac.png'),
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => env('APP_LOGO_ALT', 'Sysworks logo'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logoappac.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/logoappac.png',
            'alt' => 'administrdor_cuentas Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */
    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    // 'classes_auth_card' => 'card-outline card-primary',
    // 'classes_auth_header' => '',
    // 'classes_auth_body' => '',
    // 'classes_auth_footer' => '',
    // 'classes_auth_icon' => '',
    // 'classes_auth_btn' => 'btn-flat btn-primary',

    'classes_auth_card' => 'bg-gradient-dark',
    'classes_auth_header' => '',
    'classes_auth_body' => 'bg-gradient-dark',
    'classes_auth_footer' => 'text-center',
    'classes_auth_icon' => 'fa-fw text-light',
    'classes_auth_btn' => 'btn-flat btn-light',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => false,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // ========================================
        // MENÚ PRINCIPAL
        // ========================================

        // Dashboard - Elemento destacado
        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-tachometer-alt text-primary',
            'can' => 'admin.dashboard'
        ],

        // ========================================
        // GESTIÓN DE TERCEROS - SUBMENÚ
        // ========================================
        [
            'text' => 'Terceros',
            'icon' => 'fas fa-handshake text-success',
            'can' => ['terceros.index','users.index'],
            'submenu' => [
                [
                    'text' => 'Clientes',
                    'route' => 'admin.clientes.index',
                    'icon' => 'fas fa-users text-success',
                    'can' => 'terceros.index',
                ],
                [
                    'text' => 'Vendedores',
                    'route' => 'admin.vendedores.index',
                    'icon' => 'fas fa-user-tie text-info',
                    'can' => 'terceros.index',
                ],
                [
                    'text' => 'Ciudades',
                    'route' => 'admin.ubicaciones.index',
                    'icon' => 'fas fa-map-marker-alt text-warning',
                    'can' => 'configuracion.index',
                ],
            ]
        ],

        // ========================================
        // RECURSOS HUMANOS - SUBMENÚ
        // ========================================
        [
            'text' => 'Recursos Humanos',
            'icon' => 'fas fa-user-friends text-primary',
            'can' => ['empleados.index','configuracion.index'],
            'submenu' => [
                [
                    'text' => 'Empleados',
                    'route' => 'admin.empleados.index',
                    'icon' => 'fas fa-user-friends text-primary',
                    'can' => 'empleados.index',
                ],
                [
                    'text' => 'Cargos',
                    'route' => 'admin.cargos.index',
                    'icon' => 'fas fa-user-graduate text-success',
                    'can' => 'empleados.index',
                ],
                [
                    'text' => 'Contratos',
                    'route' => 'admin.contratos.index',
                    'icon' => 'fas fa-file-contract text-info',
                    'can' => 'empleados.index',
                ],
                [
                    'text' => 'Plantillas',
                    'route' => 'admin.plantillas.index',
                    'icon' => 'fas fa-file-alt text-secondary',
                    'can' => 'configuracion.index',
                ],
            ]
        ],

        // ========================================
        // COTIZACIONES - SUBMENÚ
        // ========================================
        [
            'text' => 'Cotizaciones',
            'icon' => 'fas fa-calculator text-warning',
            'can' => ['cotizaciones.index'],
            'submenu' => [
                [
                    'text' => 'Nueva Cotización',
                    'route' => 'admin.cotizaciones.index',
                    'icon' => 'fas fa-plus-circle text-success',
                    'can' => 'cotizaciones.index',
                ],
                [
                    'text' => 'Solicitudes Pendientes',
                    'route' => 'admin.cotizaciones.solicitudes.index',
                    'icon' => 'fas fa-clock text-warning',
                    'can' => 'cotizaciones.index',
                ],
            ]
        ],

        // ========================================
        // CONFIGURACIÓN DEL SISTEMA - SUBMENÚ UNIFICADO
        // ========================================
        [
            'text' => 'Configuración',
            'icon' => 'fas fa-cogs text-secondary',
            'can' => ['users.index','roles.index','configuracion.index'],
            'submenu' => [
                // Subgrupo: Parametrización
                [
                    'text' => 'Parametrización',
                    'icon' => 'fas fa-cogs text-primary',
                    'submenu' => [
                        [
                            'text' => 'Parámetros de Liquidación',
                            'icon' => 'fas fa-calculator text-info',
                            'route' => 'admin.parametrizacion.index',
                        ],
                        [
                            'text' => 'Categorías',
                            'icon' => 'fas fa-tags text-success',
                            'route' => 'admin.categoria.index',
                        ],
                        [
                            'text' => 'Novedades',
                            'icon' => 'fas fa-exclamation-triangle text-warning',
                            'route' => 'admin.novedad.index',
                        ],
                        [
                            'text' => 'Items Propios',
                            'icon' => 'fas fa-boxes text-secondary',
                            'route' => 'admin.items-propios.index',
                        ]
                    ]
                ],
                // Gestión de usuarios
                [
                    'text' => 'Usuarios',
                    'route' => 'admin.users.index',
                    'icon' => 'fas fa-users text-primary',
                    'can' => 'users.index',
                ],
                // Roles y permisos
                [
                    'text' => 'Roles y Permisos',
                    'icon' => 'fas fa-shield-alt text-danger',
                    'submenu' => [
                        [
                            'text' => 'Roles',
                            'route' => 'admin.roles.index',
                            'icon' => 'fas fa-user-tag text-info',
                            'can' => 'roles.index'
                        ],
                        [
                            'text' => 'Permisos',
                            'route' => 'admin.permission.index',
                            'icon' => 'fas fa-key text-warning',
                            'can' => 'configuracion.index',
                        ],
                    ]
                ],
                // Parámetros generales
                [
                    'text' => 'Parámetros Generales',
                    'route' => 'admin.elementos.index',
                    'icon' => 'fas fa-sliders-h text-secondary',
                    'can' => 'configuracion.index',
                ],
            ]
        ],

        // ========================================
        // GESTIÓN EMPRESARIAL - SUBMENÚ
        // ========================================
        [
            'text' => 'Empresas',
            'icon' => 'fas fa-building text-primary',
            'can' => 'companies.index',
            'submenu' => [
                [
                    'text' => 'Gestión de Empresas',
                    'route' => 'admin.companies.index',
                    'icon' => 'fas fa-building text-primary',
                    'can' => 'companies.index',
                ],
            ]
        ],
        // ['header' => 'Producción', 'can'=>['users.index','roles.index','permisos.index']],
        // ========================================
        // MÓDULOS EN DESARROLLO (COMENTADOS)
        // ========================================
        // Los siguientes módulos están preparados para activación futura

        // ['header' => '🏭 PRODUCCIÓN', 'can'=>['users.index','roles.index','permisos.index']],
        // [
        //     'text' => 'Fichas de Producción',
        //     'route' => 'admin.fichas-tecnicas.index',
        //     'icon' => 'fas fa-clipboard-list text-primary',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Órdenes de Producción',
        //     'route' => 'admin.materiales.index',
        //     'icon' => 'fas fa-tasks text-info',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Materiales',
        //     'route' => 'admin.materiales.index',
        //     'icon' => 'fas fa-boxes text-success',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Procesos',
        //     'route' => 'admin.procesos.index',
        //     'icon' => 'fas fa-cogs text-warning',
        //     'can'=>'users.index',
        // ],

        // ['header' => '📦 INVENTARIOS', 'can'=>['users.index','roles.index','permisos.index']],
        // [
        //     'text' => 'Productos',
        //     'route' => 'admin.productos.index',
        //     'icon' => 'fas fa-cubes text-primary',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Bodegas',
        //     'route' => 'admin.bodegas.index',
        //     'icon' => 'fas fa-warehouse text-info',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Movimientos',
        //     'route' => 'admin.movimientos.index',
        //     'icon' => 'fas fa-exchange-alt text-success',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Saldos',
        //     'route' => 'admin.saldos.index',
        //     'icon' => 'fas fa-balance-scale text-warning',
        //     'can'=>'users.index',
        // ],

        // ['header' => '🛒 COMPRAS', 'can'=>['users.index','roles.index','permisos.index']],
        // [
        //     'text' => 'Órdenes de Compra',
        //     'route' => 'admin.fichas-tecnicas.index',
        //     'icon' => 'fas fa-shopping-cart text-primary',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Proveedores',
        //     'route' => 'admin.proveedores.index',
        //     'icon' => 'fas fa-truck text-info',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Compras Generales',
        //     'route' => 'admin.materiales.index',
        //     'icon' => 'fas fa-money-bill-wave text-success',
        //     'can'=>'users.index',
        // ],

        // ['header' => '📊 REPORTES', 'can'=>['users.index']],
        // [
        //     'text' => 'Dashboard Ejecutivo',
        //     'route' => 'admin.reportes.index',
        //     'icon' => 'fas fa-chart-line text-primary',
        //     'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Reportes Financieros',
        //     'route' => 'admin.reportes.financieros',
        //     'icon' => 'fas fa-chart-bar text-success',
        //     'can'=>'users.index',
        // ],

        // ['header' => '📧 COMUNICACIONES', 'can'=>['configEmail.index']],
        // [
        //     'text' => 'Configuración de Correos',
        //     'route' => 'admin.configemail.index',
        //     'icon' => 'fas fa-cog text-primary',
        //     'can'=>'configEmail.index',
        // ],
        // [
        //     'text' => 'Gestión de Emails',
        //     'route' => 'admin.emails.index',
        //     'icon' => 'fas fa-envelope text-info',
        // ],

        // ========================================
        // SEPARADOR FINAL
        // ========================================
        ['type' => 'sidebar-custom', 'content' => '<hr class="sidebar-divider">']
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Bootstrap' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
                ],
            ],
        ],
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/2.1.4/js/dataTables.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/2.1.4/js/dataTables.bootstrap5.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/3.1.1/js/dataTables.buttons.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/3.1.1/js/buttons.bootstrap5.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/3.1.1/js/buttons.html5.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/jeditable.js/2.0.15/jquery.jeditable.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/2.1.4/css/dataTables.bootstrap5.css',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/buttons/3.1.1/css/buttons.bootstrap5.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'Toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'assets/AdminLTE-3.2.0/plugins/toastr/toastr.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'assets/AdminLTE-3.2.0/plugins/toastr/toastr.min.js',
                ],
            ],
        ],
        'CompanyBrandingVerify' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'assets/css/company-branding.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'assets/js/company-branding-verify.js',
                ],
            ],
        ],
        'AutoNumeric' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => '//cdn.jsdelivr.net/npm/autonumeric@4.6.0/dist/autoNumeric.min.js',
                ],
            ],
        ],
        'JqueryValidation' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/localization/messages_es.min.js',
                ],
            ],
        ],
        'Tabulator' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'assets/plugins/tabulator-master/dist/css/tabulator.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'assets/plugins/tabulator-master/dist/js/tabulator.min.js',
                ],

            ],
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
