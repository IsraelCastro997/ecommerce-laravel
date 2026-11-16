import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { House, LayoutGrid, LogOut, Menu, Mountain, Settings } from 'lucide-react';
import { useEffect, useRef, type ReactNode } from 'react';
import { route } from 'ziggy-js';

const navigationItems = [
    { label: 'Mi cuenta', name: 'dashboard', icon: LayoutGrid },
    { label: 'Tienda', name: 'home', icon: House },
    { label: 'Configuración', name: 'profile.edit', icon: Settings },
] as const;

export default function AppLayout({ title, children }: { title: string; children: ReactNode }) {
    const page = usePage<SharedData>();
    const user = page.props.auth.user;
    const navigation = useRef<HTMLDetailsElement>(null);

    useEffect(() => {
        if (navigation.current) navigation.current.open = false;
    }, [page.url]);

    const menu = (
        <>
            <nav aria-label="Navegación de cuenta" className="grid gap-1">
                {navigationItems.map(({ label, name, icon: Icon }) => {
                    const href = route(name, undefined, false);
                    const current = page.url.split('?')[0] === href;
                    return (
                        <Link
                            key={name}
                            href={href}
                            aria-current={current ? 'page' : undefined}
                            className={`flex items-center gap-2 rounded-md px-2 py-2 text-sm hover:bg-sidebar-accent ${current ? 'bg-sidebar-accent font-semibold' : ''}`}
                        >
                            <Icon size={18} aria-hidden="true" /> {label}
                        </Link>
                    );
                })}
            </nav>
            {user && (
                <div className="mt-2 grid gap-1 border-t border-border pt-2 md:mt-auto">
                    <span className="truncate px-2 py-1 text-sm font-semibold">{user.name}</span>
                    <Link
                        href={route('logout')}
                        method="post"
                        as="button"
                        className="flex w-full items-center gap-2 rounded-md px-2 py-2 text-left text-sm hover:bg-sidebar-accent"
                    >
                        <LogOut size={17} aria-hidden="true" /> Cerrar sesión
                    </Link>
                </div>
            )}
        </>
    );

    return (
        <div className="min-h-svh bg-sidebar text-foreground md:grid md:grid-cols-[16rem_minmax(0,1fr)]">
            <aside
                className="border-b border-border p-3 text-sidebar-foreground md:flex md:min-h-svh md:flex-col md:border-b-0 md:px-4 md:py-5"
                aria-label="Cuenta"
            >
                <Link href={route('dashboard')} className="mb-3 hidden items-center gap-3 rounded-md p-2 text-sm font-semibold md:flex">
                    <span className="grid size-8 place-items-center rounded-md bg-[#193d2d] text-white">
                        <Mountain size={20} aria-hidden="true" />
                    </span>
                    Refugio Outdoor
                </Link>
                <div className="hidden flex-1 flex-col md:flex">{menu}</div>
                <details ref={navigation} className="md:hidden">
                    <summary className="flex min-h-10 cursor-pointer list-none items-center gap-2 rounded-md px-2 text-sm font-semibold">
                        <Menu size={18} aria-hidden="true" /> Menú de cuenta
                    </summary>
                    <div className="grid gap-1 py-2">{menu}</div>
                </details>
            </aside>
            <div className="min-w-0 bg-background md:m-2 md:ml-0 md:rounded-xl md:border md:border-border">
                <div className="flex min-h-12 items-center border-b border-border px-4 text-sm md:min-h-16 md:px-6">{title}</div>
                {children}
            </div>
        </div>
    );
}
