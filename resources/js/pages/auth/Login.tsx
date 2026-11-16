import { Field, Submit, textLinkClass } from '@/components/FormFields';
import AuthLayout, { GooglePending } from '@/layouts/AuthLayout';
import { Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function Login({ canResetPassword, status }: { canResetPassword: boolean; status?: string }) {
    const form = useForm({ email: '', password: '', remember: false });

    return (
        <AuthLayout title="Inicia sesión en Refugio" description="Ingresa tu correo y contraseña para continuar.">
            {status && (
                <p className="mb-4 text-center text-sm" role="status">
                    {status}
                </p>
            )}
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('login'), { onFinish: () => form.reset('password') });
                }}
            >
                <Field
                    id="email"
                    label="Correo electrónico"
                    type="email"
                    autoComplete="email"
                    autoFocus
                    required
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    error={form.errors.email}
                />
                <Field
                    id="password"
                    label="Contraseña"
                    type="password"
                    autoComplete="current-password"
                    required
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    error={form.errors.password}
                />
                <div className="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="remember"
                            checked={form.data.remember}
                            onChange={(event) => form.setData('remember', event.target.checked)}
                        />{' '}
                        Recordar sesión
                    </label>
                    {canResetPassword && (
                        <Link className={textLinkClass} href={route('password.request')}>
                            ¿Olvidaste tu contraseña?
                        </Link>
                    )}
                </div>
                <Submit processing={form.processing}>Iniciar sesión</Submit>
                <p className="text-center text-sm text-muted-foreground">
                    ¿Todavía no tienes cuenta?{' '}
                    <Link className={textLinkClass} href={route('register')}>
                        Crear cuenta
                    </Link>
                </p>
            </form>
            <GooglePending />
        </AuthLayout>
    );
}
