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
        [
            'text'=>'Dashboard',
            'route'=>'dashboard',
            'icon'=>' fas fa-tachometer-alt',
            'can'=>'admin.dashboard'
        ],
        // [
        //     'text' => 'Cuentas',
        //     'icon' => 'fas fa-fw fa-lock',

        //     'submenu' => [
        //         [
        //             'text' => 'Cuentas principal',
        //             'route' => 'admin.cuentappal.index',
        //             'can'=>'cuentappal.index',
        //         ],
        //         [
        //             'text' => 'Cuentas distribuidores',
        //             'route' => 'admin.cuenta.index',
        //             'can'=>'cuentaabonado.index'
        //         ],
        //         [
        //             'text' => 'Estados(cuentas)',
        //             'route' => 'admin.estado.index',
        //             'can'=>'estados.index'
        //         ],
        //     ]
        // ],
        ['header' => 'Terceros', 'can'=>['terceros.index','users.index','configuracion.index']],
        [
            'text' => 'Clientes',
            'route' => 'admin.clientes.index',
            'icon' => 'fas fa-users fa-fw',
            'can'=>'terceros.index',
        ],
        [
            'text' => 'Vendedores',
            'route' => 'admin.vendedores.index',
            'icon' => 'fas fa-male fa-fw',
            'can'=>'terceros.index',
        ],
        [
            'text' => 'Ciudades',
            'route' => 'admin.ubicaciones.index',
            'icon' => 'fas fa-city fa-fw',
            'can'=>'configuracion.index',
        ],
        ['header' => 'Contratos', 'can'=>['empleados.index','configuracion.index']],
        [
            'text' => 'Contratos',
            'route' => 'admin.contratos.index',
            'icon' => 'fa fa-archive fa-fw',
            'can'=>'empleados.index',
        ],
        [
            'text' => 'Cargos',
            'route' => 'admin.cargos.index',
            'icon' => 'fa fa-folder-open fa-fw',
            'can'=>'empleados.index',
        ],
        [
            'text' => 'Empleados',
            'route' => 'admin.empleados.index',
            'icon' => 'fa fa-address-card fa-fw',
            'can'=>'empleados.index',
        ],
        [
            'text' => 'Plantillas',
            'route' => 'admin.plantillas.index',
            'icon' => 'fa fa-file fa-fw',
            'can'=>'configuracion.index',
        ],
        [
            'text' => 'Parametrización',
            // 'route' => 'admin.parametrizacion.index',
            'icon' => 'fa fa-cog fa-fw',
            // 'can'=>'permisos.index',
            'submenu' => [
                [
                    'text' => 'Parametros Liqui.',
                    'icon' => 'fa fa-check fa-fw',
                    'route' => 'admin.parametrizacion.index',
                ],
                [
                    'text' => 'Categorias',
                    'icon' => 'fa fa-check fa-fw',
                    'route' => 'admin.categoria.index',
                ],
                [
                    'text' => 'Novedades',
                    'icon' => 'fa fa-check fa-fw',
                    'route' => 'admin.novedad.index',
                ],
                [
                    'text' => 'Items',
                    'icon' => 'fa fa-check fa-fw',
                    'route' => 'admin.items-propios.index',
                ]
            ]
        ],
        ['header' => 'Cotizar', 'can'=>['cotizaciones.index']],
        [
            'text' => 'Cotización',
            'route' => 'admin.cotizaciones.index',
            'icon' => 'fa fa-file fa-fw',
            'can'=>'cotizaciones.index',
        ],
        [
            'text' => 'Solicitudes de aprobación',
            'route' => 'admin.cotizaciones.solicitudes.index',
            'icon' => 'fa fa-lock fa-fw',
            'can'=>'cotizaciones.index',
        ],
        ['header' => 'Configuración', 'can'=>['users.index','roles.index','configuracion.index','permission.index']],
        [
                'text' => 'Usuarios',
                'route' => 'admin.users.index',
                'icon' => 'fas fa-users fa-fw',
                'can'=>'users.index',
        ],
        [
            'text' => 'Roles',
            'route' => 'admin.roles.index',
            'icon' => 'fas fa-object-group fa-fw',
            'can'=>'roles.index'
        ],
        [
            'text' => 'Permisos',
            'route' => 'admin.permission.index',
            'icon' => 'fas fa-address-card fa-fw',
            'can'=>'permission.index',
        ],
        [
            'text' => 'Parametros',
            'route' => 'admin.elementos.index',
            'icon' => 'fas fa-address-card fa-fw',
            'can'=>'configuracion.index',
        ],
        ['header' => 'Producción', 'can'=>['users.index','roles.index','permisos.index']],
        [
                'text' => 'Fichas producción',
                'route' => 'admin.fichas-tecnicas.index',
                'icon' => 'fas fa-address-card fa-fw',
                'can'=>'users.index',
        ],
        [
                'text' => 'Ordenes producción',
                'route' => 'admin.materiales.index',
                'icon' => 'fas fa-tasks fa-fw',
                'can'=>'users.index',
        ],
        [
                'text' => 'Materiales',
                'route' => 'admin.materiales.index',
                'icon' => 'fas fa-building fa-fw',
                'can'=>'users.index',
        ],
        [
                'text' => 'Procesos',
                'route' => 'admin.procesos.index',
                'icon' => 'fas fa-cogs fa-fw',
                'can'=>'users.index',
        ],
        // ['header' => 'Inventarios', 'can'=>['users.index','roles.index','permisos.index']],
        // [
        //         'text' => 'Productos',
        //         'route' => 'admin.productos.index',
        //         'icon' => 'fas fa-address-card fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Bodegas',
        //         'route' => 'admin.bodegas.index',
        //         'icon' => 'fas fa-warehouse fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Movimientos de inventario',
        //         'route' => 'admin.movimientos.index',
        //         'icon' => 'fas fa-exchange-alt fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Saldos de inventario',
        //         'route' => 'admin.saldos.index',
        //         'icon' => 'fas fa-folder-open fa-fw',
        //         'can'=>'users.index',
        // ],
        // ['header' => 'Compras', 'can'=>['users.index','roles.index','permisos.index']],
        // [
        //         'text' => 'Ordenes de compra',
        //         'route' => 'admin.fichas-tecnicas.index',
        //         'icon' => 'fas fa-shopping-cart fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Compra generales',
        //         'route' => 'admin.materiales.index',
        //         'icon' => 'fas fa-money-bill fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Proveedores',
        //         'route' => 'admin.proveedores.index',
        //         'icon' => 'fas fa-money-bill fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Actividades',
        //         'route' => 'admin.procesosdet.index',
        //         'icon' => 'fas fa-tasks fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Maquinas',
        //         'route' => 'admin.maquinas.index',
        //         'icon' => 'fas fa-industry fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //         'text' => 'Insumos',
        //         'route' => 'admin.insumos.index',
        //         'icon' => 'fas fa-boxes fa-fw',
        //         'can'=>'users.index',
        // ],
        // [
        //     'text' => 'Reportes',
        //     'route' => 'admin.reportes.index',
        //     'icon' => 'fas fa-chart-bar fa-fw',
        //     'can'=>'users.index',
        // ],
        // ['header' => 'Emails', 'can'=>['configEmail.index']],
        // [
        //     'text' => 'Configuración correos',
        //     'route' => 'admin.configemail.index',
        //     'icon' => 'fas fa-cogs fa-fw',
        //     'can'=>'configEmail.index',
        // ],
        // [
        //     'text' => 'Email',
        //     'route' => 'admin.emails.index',
        //     'icon' => 'fas fa-envelope fa-fw',
        // ],


        // Sidebar items:
        // [
        //     'type' => 'sidebar-menu-search',
        //     'text' => 'search',
        // ],
        // [
        //     'text' => 'blog',
        //     'url' => 'admin/blog',
        //     'can' => 'manage-blog',
        // ],
        // [
        //     'text' => 'pages',
        //     'url' => 'admin/pages',
        //     'icon' => 'far fa-fw fa-file',
        //     'label' => 4,
        //     'label_color' => 'success',
        // ],
        // ['header' => 'account_settings'],
        // [
        //     'text' => 'profile',
        //     'url' => 'admin/settings',
        //     'icon' => 'fas fa-fw fa-user',
        // ],
        // [
        //     'text' => 'change_password',
        //     'url' => 'admin/settings',
        //     'icon' => 'fas fa-fw fa-lock',
        // ],
        // [
        //     'text' => 'multilevel',
        //     'icon' => 'fas fa-fw fa-share',
        //     'submenu' => [
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //             'submenu' => [
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                 ],
        //                 [
        //                     'text' => 'level_two',
        //                     'url' => '#',
        //                     'submenu' => [
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                         [
        //                             'text' => 'level_three',
        //                             'url' => '#',
        //                         ],
        //                     ],
        //                 ],
        //             ],
        //         ],
        //         [
        //             'text' => 'level_one',
        //             'url' => '#',
        //         ],
        //     ],
        // ],
        // ['header' => 'labels'],
        // [
        //     'text' => 'important',
        //     'icon_color' => 'red',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'warning',
        //     'icon_color' => 'yellow',
        //     'url' => '#',
        // ],
        // [
        //     'text' => 'information',
        //     'icon_color' => 'cyan',
        //     'url' => '#',
        // ],
        ['header' => 'Empresas', 'can'=>'companies.index'],
        [
            'text' => 'Empresa',
            'route' => 'admin.companies.index',
            'icon' => 'fa fa-archive fa-fw',
            'can'=>'companies.index',
        ],
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
