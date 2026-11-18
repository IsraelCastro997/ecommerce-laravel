import '../css/google-sign-in.css';

let scriptPromise;
function loadGoogle() {
 if (window.google?.accounts?.id) return Promise.resolve();
 if (scriptPromise) return scriptPromise;
 scriptPromise = new Promise((resolve, reject) => {
  const script = document.createElement('script');
  const timer = window.setTimeout(() => {
   script.remove();
   reject(new Error('Google tardó demasiado en cargar. Revisa tu conexión y vuelve a intentar.'));
  }, 12000);
  script.src = 'https://accounts.google.com/gsi/client?hl=es';
  script.async = true;
  script.onload = () => {
   window.clearTimeout(timer);
   if (window.google?.accounts?.id) resolve();
   else reject(new Error('No se pudo cargar el acceso con Google.'));
  };
  script.onerror = () => {
   window.clearTimeout(timer);
   script.remove();
   reject(new Error('No se pudo conectar con Google. Revisa tu conexión o el bloqueo de ventanas.'));
  };
  document.head.appendChild(script);
 }).catch(error => { scriptPromise = null; throw error; });
 return scriptPromise;
}

document.addEventListener('alpine:init', () => {
 window.Alpine.data('googleSignIn', options => {
  let active = true;
  let controller;
  return {
   loading: true, busy: false, error: '',
   init() { this.start(); },
   async start() {
    if (this.busy) return;
    this.loading = true;
    this.error = '';
    try {
     if (!options.clientId) throw new Error('El acceso con Google todavía no está configurado. Puedes usar tu correo y contraseña.');
     await loadGoogle();
     if (!active) return;
     controller?.abort();
     controller = new window.AbortController();
     const result = await fetch(options.challengeUrl, {
      credentials: 'same-origin', headers: { Accept: 'application/json' },
      cache: 'no-store', signal: controller.signal,
     });
     if (!result.ok) throw new Error('No se pudo preparar el acceso. Recarga la página y vuelve a intentar.');
     const { nonce } = await result.json();
     if (!active) return;
     window.google.accounts.id.initialize({
      client_id: options.clientId, nonce, auto_select: false,
      callback: response => { if (active) this.signIn(response.credential); },
     });
     this.$refs.button.replaceChildren();
     window.google.accounts.id.renderButton(this.$refs.button, {
      type: 'standard', theme: 'filled_blue', text: 'signin_with',
      shape: 'pill', size: 'large', locale: 'es',
      width: Math.min(320, this.$refs.button.clientWidth || 240),
     });
     window.google.accounts.id.prompt();
    } catch (error) {
     if (active && error.name !== 'AbortError') this.error = error.message;
    } finally { if (active) this.loading = false; }
   },
   async signIn(idToken) {
    if (this.busy || !idToken) return;
    this.busy = true;
    this.error = '';
    try {
     const response = await fetch(options.loginUrl, {
      method: 'POST', credentials: 'same-origin',
      headers: {
       'Content-Type': 'application/json', Accept: 'application/json',
       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({ idToken }),
     });
     const data = await response.json().catch(() => ({}));
     if (!active) return;
     if (!response.ok) {
      if (response.status === 419) throw new Error('Tu sesión caducó. Recarga la página.');
      if (response.status === 429) throw new Error('Demasiados intentos. Espera un minuto antes de volver a intentar.');
      throw new Error(data.errors?.google?.[0] || data.errors?.idToken?.[0] || 'No se pudo iniciar sesión con Google. Vuelve a cargar el botón.');
     }
     const destination = new URL(data.redirect_url, window.location.origin);
     if (destination.origin !== window.location.origin) throw new Error('La dirección de regreso no es válida.');
     window.location.assign(destination.href);
    } catch (error) { if (active) this.error = error.message; }
    finally { if (active) this.busy = false; }
   },
   destroy() { active = false; controller?.abort(); window.google?.accounts?.id?.cancel(); },
  };
 });
});
