<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\ParametroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        protected ParametroService $parametros
    ) {}

    /**
     * Display the two-factor authentication verification form.
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Check if there's a pending 2FA verification
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesión expirada. Por favor, inicie sesión nuevamente.',
            ]);
        }

        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);
            return redirect()->route('login')->withErrors([
                'email' => 'Usuario no encontrado.',
            ]);
        }

        // Mask email for security
        $maskedEmail = $this->maskEmail($user->email);

        return view('auth.two-factor-verify', [
            'email' => $maskedEmail,
        ]);
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function verify(Request $request): RedirectResponse
    {
        // Check if there's a pending 2FA verification
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesión expirada. Por favor, inicie sesión nuevamente.',
            ]);
        }

        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);
            return redirect()->route('login')->withErrors([
                'email' => 'Usuario no encontrado.',
            ]);
        }

        // Rate limiting: max 5 attempts per user
        $key = 'two-factor-verify:' . $userId;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'code' => "Demasiados intentos. Por favor, intente de nuevo en {$seconds} segundos.",
            ]);
        }

        // Validate input
        $request->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ], [
            'code.required' => 'El código es requerido.',
            'code.size' => 'El código debe tener 6 dígitos.',
            'code.regex' => 'El código debe contener solo números.',
        ]);

        $code = $request->input('code');

        // Verify the code
        if (!$user->verifyTwoFactorCode($code)) {
            RateLimiter::hit($key, 300); // 5 minutes decay

            throw ValidationException::withMessages([
                'code' => 'El código es inválido o ha expirado.',
            ]);
        }

        // Clear rate limiter
        RateLimiter::clear($key);

        // Clear 2FA session data
        $remember = $request->session()->get('2fa_remember', false);
        $request->session()->forget(['2fa_user_id', '2fa_remember']);

        // Complete the login
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Resend the two-factor authentication code.
     */
    public function resend(Request $request): RedirectResponse
    {
        // Check if there's a pending 2FA verification
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesión expirada. Por favor, inicie sesión nuevamente.',
            ]);
        }

        $userId = $request->session()->get('2fa_user_id');
        $user = User::find($userId);

        if (!$user) {
            $request->session()->forget(['2fa_user_id', '2fa_remember']);
            return redirect()->route('login')->withErrors([
                'email' => 'Usuario no encontrado.',
            ]);
        }

        // Rate limiting: max 3 resend attempts every 5 minutes
        $key = 'two-factor-resend:' . $userId;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return back()->withErrors([
                'code' => "Ha alcanzado el límite de reenvíos. Por favor, espere {$minutes} minuto(s).",
            ]);
        }

        // Generate and send new code
        $code = $user->generateTwoFactorCode();

        // Configure mailer for user's company
        $this->parametros->configurarMailer($user->company_id);

        // Send email with new code
        Mail::to($user->email)->send(new TwoFactorCode($user, $code));

        // Hit rate limiter
        RateLimiter::hit($key, 300); // 5 minutes decay

        return back()->with('status', 'Se ha enviado un nuevo código a tu correo electrónico.');
    }

    /**
     * Mask email address for security.
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (strlen($name) <= 2) {
            $maskedName = $name[0] . '***';
        } else {
            $maskedName = $name[0] . str_repeat('*', min(strlen($name) - 2, 3)) . substr($name, -1);
        }

        return $maskedName . '@' . $domain;
    }
}
