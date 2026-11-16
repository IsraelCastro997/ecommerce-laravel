import AppLayout from '@/layouts/AppLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { route } from 'ziggy-js';

const sections = [
    { title: 'Perfil', name: 'profile.edit' },
    { title: 'Contraseña', name: 'password.edit' },
    { title: 'Apariencia', name: 'appearance' },
] as const;

export default function SettingsLayout({ title, children }: { title: string; children: ReactNode }) {
    const { url } = usePage();
    return (
        <AppLayout title={title}>
            <Head title={title} />
            <main className="space-y-5 px-4 py-6 sm:px-6">
                <header>
                    <h1 className="text-2xl font-bold">Configuración</h1>
                    <p className="text-sm text-muted-foreground">Administra tu perfil y tu cuenta.</p>
                </header>
                <div className="grid gap-6 lg:grid-cols-[12rem_minmax(0,1fr)]">
                    <nav aria-label="Configuración de la cuenta" className="flex flex-wrap gap-2 lg:flex-col">
                        {sections.map((section) => {
                            const href = route(section.name, undefined, false);
                            const current = url.split('?')[0] === href;
                            return (
                                <Link
                                    key={section.name}
                                    href={href}
                                    aria-current={current ? 'page' : undefined}
                                    className={`rounded-md px-3 py-2 text-sm hover:bg-muted ${current ? 'bg-muted font-semibold' : ''}`}
                                >
                                    {section.title}
                                </Link>
                            );
                        })}
                    </nav>
                    <div className="min-w-0 max-w-xl space-y-7">{children}</div>
                </div>
            </main>
        </AppLayout>
    );
}
