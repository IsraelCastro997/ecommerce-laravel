import { Head, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';

const messages: Record<number, { title: string; detail: string }> = {
    401: { title: 'Inicia sesión para continuar', detail: 'Necesitas una sesión activa para abrir esta página.' },
    403: { title: 'Acceso restringido', detail: 'Tu cuenta no tiene permiso para abrir esta página.' },
    404: { title: 'Página no encontrada', detail: 'La página pudo cambiar de dirección o dejar de estar disponible.' },
    419: { title: 'Tu sesión caducó', detail: 'Inicia sesión de nuevo antes de continuar.' },
};

export default function ErrorPage({ status }: { status: number }) {
    const message = messages[status] ?? { title: 'No pudimos cargar esta página', detail: 'Intenta volver a la tienda y abrir la página de nuevo.' };

    return (
        <main className="grid min-h-svh place-items-center bg-background p-5">
            <Head title={message.title} />
            <div className="max-w-md rounded-xl border bg-card p-6 text-card-foreground sm:p-9">
                <p className="mb-2 text-sm font-bold text-muted-foreground">{status}</p>
                <h1 className="mb-3 text-3xl font-bold tracking-tight">{message.title}</h1>
                <p className="mb-6 text-muted-foreground">{message.detail}</p>
                <div className="flex flex-wrap gap-4">
                    <Link href={route('home')} className="font-semibold underline underline-offset-4">
                        Volver a la tienda
                    </Link>
                    {(status === 401 || status === 419) && (
                        <Link href={route('login')} className="font-semibold underline underline-offset-4">
                            Iniciar sesión
                        </Link>
                    )}
                </div>
            </div>
        </main>
    );
}
