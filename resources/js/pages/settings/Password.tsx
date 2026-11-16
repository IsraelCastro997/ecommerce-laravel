import { Field, Submit } from '@/components/FormFields';
import SettingsLayout from '@/layouts/settings/Layout';
import { useForm } from '@inertiajs/react';
import { useRef } from 'react';
import { route } from 'ziggy-js';

export default function Password() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const formElement = useRef<HTMLFormElement>(null);

    return (
        <SettingsLayout title="Contraseña">
            <section className="space-y-4">
                <div>
                    <h2 className="text-lg font-bold">Actualizar contraseña</h2>
                    <p className="text-sm text-muted-foreground">Elige una contraseña larga y difícil de adivinar.</p>
                </div>
                <form
                    ref={formElement}
                    className="grid gap-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(route('password.update'), {
                            preserveScroll: true,
                            onSuccess: () => form.reset(),
                            onError: (errors) => {
                                if (errors.password || errors.password_confirmation) {
                                    form.reset('password', 'password_confirmation');
                                    formElement.current?.querySelector<HTMLInputElement>('#password')?.focus();
                                }
                                if (errors.current_password) {
                                    form.reset('current_password');
                                    formElement.current?.querySelector<HTMLInputElement>('#current_password')?.focus();
                                }
                            },
                        });
                    }}
                >
                    <Field
                        id="current_password"
                        label="Contraseña actual"
                        type="password"
                        autoComplete="current-password"
                        value={form.data.current_password}
                        onChange={(event) => form.setData('current_password', event.target.value)}
                        error={form.errors.current_password}
                    />
                    <Field
                        id="password"
                        label="Nueva contraseña"
                        type="password"
                        autoComplete="new-password"
                        value={form.data.password}
                        onChange={(event) => form.setData('password', event.target.value)}
                        error={form.errors.password}
                    />
                    <Field
                        id="password_confirmation"
                        label="Confirmar contraseña"
                        type="password"
                        autoComplete="new-password"
                        value={form.data.password_confirmation}
                        onChange={(event) => form.setData('password_confirmation', event.target.value)}
                        error={form.errors.password_confirmation}
                    />
                    <div className="flex flex-wrap items-center gap-3">
                        <Submit processing={form.processing}>Guardar contraseña</Submit>
                        {form.recentlySuccessful && (
                            <p className="text-sm" role="status">
                                Guardada.
                            </p>
                        )}
                    </div>
                </form>
            </section>
        </SettingsLayout>
    );
}
