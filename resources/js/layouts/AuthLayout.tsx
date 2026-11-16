import { Head, Link } from '@inertiajs/react';
import { Mountain } from 'lucide-react';
import type { ReactNode } from 'react';
import { route } from 'ziggy-js';

export default function AuthLayout({ title, description, children }: { title: string; description: string; children: ReactNode }) {
    return (
        <main className="flex min-h-svh items-center justify-center bg-background px-6 py-10 text-foreground">
            <Head title={title} />
            <div className="w-full max-w-sm">
                <div className="mb-7 space-y-2 text-center">
                    <Link
                        href={route('home')}
                        className="mx-auto mb-3 inline-flex rounded-md p-1 text-[#193d2d] focus-visible:outline dark:text-white"
                        aria-label="Volver a Refugio Outdoor"
                    >
                        <Mountain size={36} aria-hidden="true" />
                    </Link>
                    <h1 className="text-xl font-bold">{title}</h1>
                    <p className="text-sm text-muted-foreground">{description}</p>
                </div>
                {children}
            </div>
        </main>
    );
}

export function GooglePending() {
    return (
        <div className="mt-4 grid gap-2 text-center">
            <button className="rounded-md border border-input px-4 py-2 text-sm font-medium opacity-60" type="button" disabled>
                Continuar con Google
            </button>
            <p className="text-xs text-muted-foreground">Google está pendiente de integración.</p>
        </div>
    );
}
