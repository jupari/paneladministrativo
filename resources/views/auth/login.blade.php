<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Iniciar sesión</title>

    <!-- Fuentes y estilos de la plantilla -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('login-form-08/fonts/icomoon/style.css') }}">
    <link rel="stylesheet" href="{{ asset('login-form-08/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login-form-08/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('login-form-08/css/style.css') }}">
    <style>
        /* Override plantilla con paleta corporativa */
        :root {
            --color-primario: rgb(0, 32, 40);
            --color-primario-oscuro: rgba(7, 57, 60, 0.9);
            --color-primario-claro: rgba(149, 213, 178, 0.12);
            --color-blanco: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--color-primario-claro), var(--color-blanco)) !important;
            color: var(--color-primario);
        }

        .content {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        h3, .caption, .forgot-pass, .social-login a {
            color: var(--color-primario);
        }

        .btn-primary {
            background: var(--color-primario) !important;
            border-color: var(--color-primario) !important;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {
            background: var(--color-primario-oscuro) !important;
            border-color: var(--color-primario-oscuro) !important;
        }

        .form-control:focus {
            border-color: var(--color-primario) !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 32, 40, 0.25) !important;
        }

        .control__indicator {
            border: 2px solid var(--color-primario);
        }

        .control input:checked ~ .control__indicator {
            background: var(--color-primario);
            border-color: var(--color-primario);
        }

        .social-login a {
            border-color: var(--color-primario);
            color: var(--color-primario);
        }

        .social-login a:hover {
            background: var(--color-primario);
            color: var(--color-blanco);
        }

        .text-muted { color: rgba(0, 32, 40, 0.7) !important; }
    </style>
</head>
<body>
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-md-6 order-md-2">
                <img src="{{ asset('login-form-08/images/LOGO_SYSWORKS_VERTICAL_COLOR.svg') }}" alt="Ilustración" class="img-fluid">
            </div>
            <div class="col-md-6 contents">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <h3>Iniciar sesión</h3>
                            <p class="mb-4">Accede al panel administrativo con tus credenciales.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf

                            <div class="form-group first">
                                <label for="email">Correo electrónico</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                                @error('email')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group last mb-4">
                                <label for="password">Contraseña</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="d-flex mb-4 align-items-center">
                                <label class="control control--checkbox mb-0">
                                    <span class="caption">Recordarme</span>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <div class="control__indicator"></div>
                                </label>
                                @if (Route::has('password.request'))
                                    <span class="ml-auto"><a href="{{ route('password.request') }}" class="forgot-pass">¿Olvidaste tu contraseña?</a></span>
                                @endif
                            </div>

                            <input type="submit" value="Ingresar" class="btn text-white btn-block btn-primary">

                            <span class="d-block text-left my-4 text-muted">O ingresa con</span>

                            <div class="social-login">
                                <a href="#" class="facebook" aria-label="Facebook">
                                    <span class="icon-facebook mr-3"></span>
                                </a>
                                {{-- <a href="#" class="twitter" aria-label="Twitter">
                                    <span class="icon-twitter mr-3"></span>
                                </a>
                                <a href="#" class="google" aria-label="Google">
                                    <span class="icon-google mr-3"></span>
                                </a> --}}
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="{{ asset('login-form-08/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('login-form-08/js/popper.min.js') }}"></script>
<script src="{{ asset('login-form-08/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('login-form-08/js/main.js') }}"></script>
</body>
</html>
