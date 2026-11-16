import { Submit, textLinkClass } from '@/components/FormFields';
import AuthLayout from '@/layouts/AuthLayout';
import { Link, useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';

export default function VerifyEmail({ status }: { status?: string }) {
    const form = useForm({});
    return (
        <AuthLayout title="Verifica tu correo" description="Abre el enlace que enviamos a tu correo para verificar tu cuenta.">
            {status === 'verification-link-sent' && (
                <p className="mb-4 text-center text-sm" role="status">
                    Enviamos un nuevo enlace de verificación al correo con el que te registraste.
                </p>
            )}
            <form
                className="grid justify-items-center gap-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    form.post(route('verification.send'));
                }}
            >
                <Submit processing={form.processing}>Reenviar correo de verificación</Submit>
            </form>
            <div className="mt-5 text-center text-sm">
                <Link href={route('logout')} method="post" as="button" className={textLinkClass}>
                    Cerrar sesión
                </Link>
            </div>
        </AuthLayout>
    );
}
