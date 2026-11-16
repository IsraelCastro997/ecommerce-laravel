import { Field, Submit } from '@/components/FormFields';
import AuthLayout from '@/layouts/AuthLayout';
import { useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function ResetPassword({ email, token }: { email: string; token: string }) {
    const form = useForm({ email, token, password: '', password_confirmation: '' });
    return (
        <AuthLayout title="Restablecer contraseña" description="Introduce tu nueva contraseña.">
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
                }}
            >
                <Field
                    id="email"
                    label="Correo electrónico"
                    type="email"
                    autoComplete="email"
                    readOnly
                    value={form.data.email}
                    error={form.errors.email}
                />
                <Field
                    id="password"
                    label="Contraseña"
                    type="password"
                    autoComplete="new-password"
                    autoFocus
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
                <Submit processing={form.processing}>Restablecer contraseña</Submit>
            </form>
        </AuthLayout>
    );
}
