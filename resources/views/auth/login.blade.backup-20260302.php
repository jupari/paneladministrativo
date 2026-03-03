{{-- Backup del login anterior (2026-03-02) --}}
{{-- Contenido original de resources/views/auth/login.blade.php --}}
{{--
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
--}}

@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header', __('adminlte::adminlte.login_message'))

@section('auth_body')
    <div class="container-fluid">
        <p class="text-muted mb-3" style="font-size: .92rem;">
            Inicia sesión para continuar al panel administrativo.
        </p>

        <form action="{{ $login_url }}" method="post" id="login-form">
         @csrf
            {{-- Email field --}}
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus autocomplete="username">

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            {{-- Password field --}}
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    placeholder="{{ __('adminlte::adminlte.password') }}" id="password" autocomplete="current-password">

                <div class="input-group-append">
                    <button type="button" class="btn btn-outline-secondary" id="toggle-password" title="Mostrar u ocultar contraseña">
                        <span class="fas fa-eye"></span>
                    </button>
                </div>

                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock {{ config('adminlte.classes_auth_icon', '') }}"></span>
                    </div>
                </div>

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <small id="caps-lock-warning" class="text-warning d-none mb-3 d-block">
                Bloq Mayús está activado.
            </small>
            {{-- Login field --}}
            <div class="row">
                <div class="col-7">
                    <div class="icheck-primary" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label for="remember">
                            {{ __('adminlte::adminlte.remember_me') }}
                        </label>
                    </div>
                </div>

                <div class="col-5">
                    <button type="submit" id="login-submit" class="btn btn-block {{ config('adminlte.classes_auth_btn', 'btn-flat btn-primary') }}">
                        <span class="fas fa-sign-in-alt"></span>
                        <span id="login-submit-text">{{ __('adminlte::adminlte.sign_in') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('auth_footer')
    {{-- Password reset link --}}
    @if($password_reset_url)
        <p class="my-0">
            <a href="{{ $password_reset_url }}">
                {{ __('adminlte::adminlte.i_forgot_my_password') }}
            </a>
        </p>
    @endif

    {{-- Register link --}}
    {{-- @if($register_url)
        <p class="my-0">
            <a href="{{ $register_url }}">
                {{ __('adminlte::adminlte.register_a_new_membership') }}
            </a>
        </p>
    @endif --}}
@stop

@section('css')
<style>
    .login-page {
        background:
            radial-gradient(circle at 15% 15%, rgba(0, 123, 255, .18), transparent 35%),
            radial-gradient(circle at 85% 80%, rgba(40, 167, 69, .16), transparent 30%),
            #f4f6f9;
    }
</style>
@stop

@section('js')
<script>
    (function () {
        const pass = document.getElementById('password');
        const toggle = document.getElementById('toggle-password');
        const form = document.getElementById('login-form');
        const submit = document.getElementById('login-submit');
        const submitText = document.getElementById('login-submit-text');
        const capsWarning = document.getElementById('caps-lock-warning');

        if (toggle && pass) {
            toggle.addEventListener('click', function () {
                const isPassword = pass.getAttribute('type') === 'password';
                pass.setAttribute('type', isPassword ? 'text' : 'password');
                toggle.innerHTML = isPassword
                    ? '<span class="fas fa-eye-slash"></span>'
                    : '<span class="fas fa-eye"></span>';
            });

            pass.addEventListener('keyup', function (e) {
                if (!capsWarning) return;
                capsWarning.classList.toggle('d-none', !e.getModifierState('CapsLock'));
            });
        }

        if (form && submit && submitText) {
            form.addEventListener('submit', function () {
                submit.setAttribute('disabled', 'disabled');
                submitText.textContent = 'Ingresando...';
            });
        }
    })();
</script>
@stop
