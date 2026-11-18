<section class="google-login" aria-label="Acceso con Google" x-data="googleSignIn({ clientId: @js(config('services.google.client_id')), challengeUrl: @js(route('auth.google.challenge')), loginUrl: @js(route('auth.google.store')) })">
 <div class="google-login-divider"><span>o continúa con Google</span></div>
 <p class="page-note" role="status" x-show="loading">Cargando acceso con Google…</p>
 <div class="google-login-button" x-ref="button" wire:ignore :inert="busy" :aria-busy="busy"></div>
 <p class="page-note" role="status" x-show="busy" x-cloak>Verificando tu cuenta…</p>
 <p class="blade-error" role="alert" x-show="error" x-text="error" x-cloak></p>
 <button type="button" class="text-button" @click="start()" x-show="error && !busy" x-cloak>Volver a cargar Google</button>
 <noscript><p>Activa JavaScript para usar Google. También puedes ingresar con correo y contraseña.</p></noscript>
</section>
