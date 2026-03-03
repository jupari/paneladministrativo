<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Sysworks</title>

    <!-- Bootstrap 4.6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- (Opcional) Font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <style>
        :root{
            --brand-primary: #1F3B73; /* ajusta si tienes color corporativo */
            --brand-dark: #0B1220;
            --text-muted-custom: #6c757d;
        }

        html, body { height: 100%; }
        body{
            font-family: Figtree, Arial, sans-serif;
            background: #f6f7fb;
        }

        /* Navbar */
        .navbar-syswork{
            background: rgba(255,255,255,.9);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0,0,0,.06);
        }

        /* Hero */
        .hero-wrap{
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-bg{
            position: absolute;
            inset: 0;
            background:
              linear-gradient(135deg, rgba(31,59,115,.92), rgba(11,18,32,.92)),
              url("{{ asset('assets/img/fondo_syswork.png') }}");
            background-size: cover;
            background-position: center;
            filter: saturate(1.05);
        }

        .hero-overlay{
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 10%, rgba(255,255,255,.10), transparent 40%),
                        radial-gradient(circle at 80% 30%, rgba(255,255,255,.08), transparent 45%);
        }

        .hero-content{
            position: relative;
            z-index: 2;
            padding-top: 90px;
            padding-bottom: 60px;
        }

        .hero-card{
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,.20);
        }

        .hero-title{
            color: #fff;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .hero-subtitle{
            color: rgba(255,255,255,.85);
            max-width: 52ch;
        }

        .btn-syswork{
            background: #fff;
            color: var(--brand-dark);
            border: 0;
            font-weight: 600;
            border-radius: 10px;
            padding: 12px 18px;
        }
        .btn-syswork:hover{
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0,0,0,.18);
        }

        .btn-outline-syswork{
            border: 1px solid rgba(255,255,255,.75);
            color: #fff;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: 600;
        }
        .btn-outline-syswork:hover{
            background: rgba(255,255,255,.10);
            color: #fff;
        }

        .logo-hero{
            width: 170px;
            max-width: 100%;
            height: auto;
        }

        .feature{
            color: rgba(255,255,255,.9);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 14px;
            padding: 18px;
            background: rgba(255,255,255,.06);
        }
        .feature small{
            color: rgba(255,255,255,.75);
        }

        @media (max-width: 575.98px){
            .hero-card{ padding: 22px; }
            .hero-title{ font-size: 1.6rem; }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-light navbar-syswork fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <!-- Usa logo oscuro para navbar clara -->
                <img
                    src="{{ asset('assets/img/LOGO SYSWORKS HORIZONTAL NEGRO.svg') }}"
                    alt="Sysworks"
                    style="height:40px;width:auto;"
                >
            </a>

            @if (Route::has('login'))
                <div class="ml-auto d-flex align-items-center">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-primary">
                            Ir a la aplicación
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">
                            Entrar
                        </a>
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <!-- Hero -->
    <main class="hero-wrap">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>

        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="hero-card">
                        <!-- Logo blanco para fondo oscuro -->
                        <img
                            class="logo-hero mb-3"
                            src="{{ asset('assets/img/LOGO SYSWORKS HORIZONTAL BLANCO.svg') }}"
                            alt="Sysworks"
                        >

                        <h1 class="hero-title mb-3">
                            Soluciones tecnológicas para empresas que necesitan operar sin fricciones.
                        </h1>

                        <p class="hero-subtitle mb-4">
                            Infraestructura, soporte HW/SW, redes y servidores, administración de recursos y directorio activo,
                            y desarrollo a la medida enfocado en pequeñas y medianas empresas.
                        </p>

                        <div class="d-flex flex-wrap" style="gap: 12px;">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn btn-syswork">
                                    Ir a la aplicación
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-syswork">
                                    Entrar
                                </a>

                                {{-- Si quieres CTA alterna (contacto/whatsapp) déjalo listo --}}
                                <a href="#servicios" class="btn btn-outline-syswork">
                                    Ver servicios
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div id="servicios" class="feature mb-3">
                        <h5 class="mb-1">Soporte e Infraestructura</h5>
                        <small>Redes, servidores, mantenimiento preventivo/correctivo, directorio activo y administración.</small>
                    </div>

                    <div class="feature mb-3">
                        <h5 class="mb-1">Desarrollo a la medida</h5>
                        <small>Aplicaciones web y APIs para automatizar procesos y mejorar productividad.</small>
                    </div>

                    <div class="feature">
                        <h5 class="mb-1">Seguridad y control</h5>
                        <small>Acceso por usuarios/roles, trazabilidad, notificaciones y buenas prácticas.</small>
                    </div>
                </div>
            </div>

            <hr class="my-5" style="border-color: rgba(255,255,255,.15);">

            <div class="text-center" style="color: rgba(255,255,255,.75);">
                <small>© {{ date('Y') }} Sysworks. Todos los derechos reservados.</small>
            </div>
        </div>
    </main>

    <!-- Bootstrap 4.6 JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
