<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function challenge(Request $request): JsonResponse
    {
        abort_unless(config('services.google.client_id'), 503, 'Google no está configurado.');

        $nonce = bin2hex(random_bytes(32));
        $request->session()->put('google_login_challenge', [
            'nonce' => $nonce,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);

        return response()->json(['nonce' => $nonce])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, GoogleClient $google): JsonResponse
    {
        $data = $request->validate(['idToken' => ['required', 'string', 'max:16384']]);
        $clientId = config('services.google.client_id');
        abort_unless(is_string($clientId) && $clientId !== '', 503, 'Google no está configurado.');

        $challenge = $request->session()->pull('google_login_challenge');
        if (!is_array($challenge) || ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            $this->reject('La solicitud caducó. Vuelve a cargar el botón de Google.');
        }

        try {
            $google->setClientId($clientId);
            // The official SDK verifies signature, audience, issuer and expiry.
            $claims = $google->verifyIdToken($data['idToken']);
        } catch (Throwable) {
            // Never log the credential or the SDK exception containing it.
            $this->reject('No se pudo verificar Google. Vuelve a intentarlo.');
        }

        if (
            !is_array($claims)
            || ($claims['aud'] ?? null) !== $clientId
            || !in_array($claims['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            || (int) ($claims['exp'] ?? 0) <= now()->timestamp
            || !is_string($claims['nonce'] ?? null)
            || !hash_equals($challenge['nonce'], $claims['nonce'])
            || !is_string($claims['sub'] ?? null)
            || $claims['sub'] === ''
            || strlen($claims['sub']) > 255
            || ($claims['email_verified'] ?? false) !== true
            || !is_string($claims['email'] ?? null)
            || !filter_var($claims['email'], FILTER_VALIDATE_EMAIL)
            || strlen($claims['email']) > 254
        ) {
            $this->reject('La identificación de Google no es válida. Vuelve a cargar el botón.');
        }

        $email = Str::lower($claims['email']);

        try {
            $user = DB::transaction(function () use ($claims, $email) {
                $account = SocialAccount::where('provider', 'google')
                    ->where('provider_user_id', $claims['sub'])
                    ->lockForUpdate()->first();

                if ($account) {
                    $user = $account->user;
                    if (!$user) $this->reject('No se encontró la cuenta vinculada.');
                    return $user;
                }

                // Existing password accounts require proof of ownership before
                // linking another identity. Never grant access just by email.
                if (User::where('email', $email)->exists()) {
                    $this->reject('Ya tienes una cuenta con este correo. Ingresa con tu contraseña; Google todavía no está vinculado a esa cuenta.');
                }

                $authoritativeEmail = str_ends_with($email, '@gmail.com')
                    || (is_string($claims['hd'] ?? null) && $claims['hd'] !== '');

                $user = User::create([
                    'name' => Str::limit(trim((string) ($claims['name'] ?? 'Usuario de Google')) ?: 'Usuario de Google', 255, ''),
                    'email' => $email,
                    // This application requires a non-null password. Generate
                    // an unknown random one; local password reset remains usable.
                    'password' => Str::random(64),
                ]);

                if ($authoritativeEmail) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                SocialAccount::forceCreate([
                    'user_id' => $user->id,
                    'provider' => 'google',
                    'provider_user_id' => $claims['sub'],
                    'provider_email' => $email,
                ]);

                // No admin role or permission is assigned by Google login.
                return $user;
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->reject('La cuenta se actualizó en otra solicitud. Vuelve a intentar el acceso.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $destination = $request->session()->pull('url.intended');
        $root = rtrim($request->getSchemeAndHttpHost(), '/');
        // Only use an intended destination belonging to this origin.
        if (!is_string($destination) || !str_starts_with($destination, $root.'/')) {
            $destination = route('dashboard');
        }

        return response()->json(['redirect_url' => $destination])
            ->header('Cache-Control', 'no-store');
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['google' => $message]);
    }
}
