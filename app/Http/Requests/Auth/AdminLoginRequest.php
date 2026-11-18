<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends LoginRequest
{
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $credentials = $this->only('email', 'password');
        $provider = Auth::guard('web')->getProvider();
        $user = $provider->retrieveByCredentials($credentials);

        if (!$user || !$provider->validateCredentials($user, $credentials)
            || !Gate::forUser($user)->allows('access-admin')) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas o cuenta sin acceso administrativo.',
            ]);
        }

        // A session is created only after both password and permission checks.
        Auth::guard('web')->login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    public function throttleKey(): string
    {
        return 'admin|'.parent::throttleKey();
    }
}
