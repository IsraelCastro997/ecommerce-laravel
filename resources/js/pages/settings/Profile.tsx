import { actionClass, Field, Submit, textLinkClass } from '@/components/FormFields';
import SettingsLayout from '@/layouts/settings/Layout';
import type { SharedData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { useRef } from 'react';
import { route } from 'ziggy-js';

function DeleteAccount() {
    const form = useForm({ password: '' });
    const dialog = useRef<HTMLDialogElement>(null);

    return (
        <section className="space-y-3 rounded-lg border border-red-200 p-4 dark:border-red-800">
            <h2 className="text-lg font-bold">Eliminar cuenta</h2>
            <p className="text-sm text-muted-foreground">La eliminación de la cuenta es permanente.</p>
            <button type="button" className={`${actionClass} bg-destructive text-destructive-foreground`} onClick={() => dialog.current?.showModal()}>
                Eliminar cuenta
            </button>
            <dialog
                ref={dialog}
                aria-labelledby="delete-account-title"
                className="m-auto w-[min(28rem,calc(100%-2rem))] space-y-4 rounded-lg border border-border bg-card p-5 text-card-foreground backdrop:bg-black/60"
                onClose={() => {
                    form.clearErrors();
                    form.reset();
                }}
            >
                <h3 id="delete-account-title" className="text-lg font-bold">
                    ¿Quieres eliminar tu cuenta?
                </h3>
                <p className="text-sm text-muted-foreground">
                    Introduce tu contraseña para confirmar la eliminación permanente de tu cuenta y sus datos.
                </p>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.delete(route('profile.destroy'), {
                            preserveScroll: true,
                            onSuccess: () => dialog.current?.close(),
                            onError: () => dialog.current?.querySelector<HTMLInputElement>('#delete-password')?.focus(),
                            onFinish: () => form.reset(),
                        });
                    }}
                >
                    <Field
                        id="delete-password"
                        name="password"
                        type="password"
                        label="Contraseña"
                        autoComplete="current-password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        error={form.errors.password}
                    />
                    <div className="flex flex-wrap items-center justify-end gap-3">
                        <button type="button" className="rounded-md border border-border px-4 py-2 text-sm" onClick={() => dialog.current?.close()}>
                            Cancelar
                        </button>
                        <Submit processing={form.processing} className="bg-destructive text-destructive-foreground">
                            Eliminar cuenta
                        </Submit>
                    </div>
                </form>
            </dialog>
        </section>
    );
}

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { user } = usePage<SharedData>().props.auth;
    const form = useForm({ name: user?.name ?? '', email: user?.email ?? '' });

    return (
        <SettingsLayout title="Perfil">
            <section className="space-y-4">
                <div>
                    <h2 className="text-lg font-bold">Información del perfil</h2>
                    <p className="text-sm text-muted-foreground">Actualiza tu nombre y correo electrónico.</p>
                </div>
                <form
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.patch(route('profile.update'), { preserveScroll: true });
                    }}
                >
                    <Field
                        id="name"
                        label="Nombre"
                        autoComplete="name"
                        required
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                        error={form.errors.name}
                    />
                    <Field
                        id="email"
                        label="Correo electrónico"
                        type="email"
                        autoComplete="username"
                        required
                        value={form.data.email}
                        onChange={(event) => form.setData('email', event.target.value)}
                        error={form.errors.email}
                    />
                    {mustVerifyEmail && !user?.email_verified_at && (
                        <div className="text-sm">
                            <p>
                                Tu correo todavía está pendiente de verificación.{' '}
                                <Link href={route('verification.send')} method="post" as="button" className={textLinkClass}>
                                    Reenviar el correo de verificación.
                                </Link>
                            </p>
                            {status === 'verification-link-sent' && <p role="status">Enviamos un nuevo enlace de verificación a tu correo.</p>}
                        </div>
                    )}
                    <div className="flex flex-wrap items-center gap-3">
                        <Submit processing={form.processing}>Guardar</Submit>
                        {form.recentlySuccessful && (
                            <p className="text-sm" role="status">
                                Guardado.
                            </p>
                        )}
                    </div>
                </form>
            </section>
            <DeleteAccount />
        </SettingsLayout>
    );
}
