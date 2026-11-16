import { Field, Submit, textLinkClass } from '@/components/FormFields';
import AuthLayout from '@/layouts/AuthLayout';
import { Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function ForgotPassword({ status }: { status?: string }) {
    const form = useForm({ email: '' });
    return (
        <AuthLayout title="Recuperar contraseña" description="Escribe tu correo para recibir un enlace de recuperación.">
            {status && (
                <p className="mb-4 text-center text-sm" role="status">
                    {status}
                </p>
            )}
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.email'));
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
                <Submit processing={form.processing}>Enviar enlace de recuperación</Submit>
            </form>
            <p className="mt-5 text-center text-sm text-muted-foreground">
                Volver a{' '}
                <Link className={textLinkClass} href={route('login')}>
                    iniciar sesión
                </Link>
            </p>
        </AuthLayout>
    );
}
