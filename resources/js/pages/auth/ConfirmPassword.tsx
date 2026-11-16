import { Field, Submit } from '@/components/FormFields';
import AuthLayout from '@/layouts/AuthLayout';
import { useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function ConfirmPassword() {
    const form = useForm({ password: '' });
    return (
        <AuthLayout title="Confirmar contraseña" description="Confirma tu contraseña para continuar.">
            <form
                className="grid gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('password.confirm'), { onFinish: () => form.reset() });
                }}
            >
                <Field
                    id="password"
                    label="Contraseña"
                    type="password"
                    autoComplete="current-password"
                    autoFocus
                    required
                    value={form.data.password}
                    onChange={(event) => form.setData('password', event.target.value)}
                    error={form.errors.password}
                />
                <Submit processing={form.processing}>Confirmar contraseña</Submit>
            </form>
        </AuthLayout>
    );
}
