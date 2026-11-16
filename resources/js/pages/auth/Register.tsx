import { Field, Submit, textLinkClass } from '@/components/FormFields';
import AuthLayout, { GooglePending } from '@/layouts/AuthLayout';
import { Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function Register() {
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

    return (
        <AuthLayout title="Crea tu cuenta" description="Ingresa tus datos para registrarte en Refugio.">
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
                <Field
                    id="name"
                    label="Nombre"
                    autoComplete="name"
                    autoFocus
                    required
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    error={form.errors.name}
                />
                <Field
                    id="email"
                    label="Correo electrónico"
                    type="email"
                    autoComplete="email"
                    required
                    value={form.data.email}
                    onChange={(event) => form.setData('email', event.target.value)}
                    error={form.errors.email}
                />
                <Field
                    id="password"
                    label="Contraseña"
                    type="password"
                    autoComplete="new-password"
                    required
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    error={form.errors.password}
                />
                <Field
                    id="password_confirmation"
                    label="Confirmar contraseña"
                    type="password"
                    autoComplete="new-password"
                    required
                    value={form.data.password_confirmation}
                    onChange={(event) => form.setData('password_confirmation', event.target.value)}
                    error={form.errors.password_confirmation}
                />
                <Submit processing={form.processing}>Crear cuenta</Submit>
                <p className="text-center text-sm text-muted-foreground">
                    ¿Ya tienes una cuenta?{' '}
                    <Link className={textLinkClass} href={route('login')}>
                        Iniciar sesión
                    </Link>
                </p>
            </form>
            <GooglePending />
        </AuthLayout>
    );
}
