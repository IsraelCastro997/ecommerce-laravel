import { LoaderCircle } from 'lucide-react';
import type { ComponentPropsWithoutRef } from 'react';

export const actionClass =
    'inline-flex min-h-10 items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
export const textLinkClass =
    'font-semibold underline underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2';

export function Field({ id, label, error, ...input }: ComponentPropsWithoutRef<'input'> & { id: string; label: string; error?: string }) {
    return (
        <div className="grid gap-2">
            <label htmlFor={id} className="text-sm font-semibold">
                {label}
            </label>
            <input
                {...input}
                id={id}
                name={input.name ?? id}
                aria-invalid={Boolean(error)}
                aria-describedby={error ? `${id}-error` : undefined}
                className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            />
            <div id={`${id}-error`} aria-live="polite" aria-atomic="true">
                {error && <p className="text-sm text-red-700 dark:text-red-400">{error}</p>}
            </div>
        </div>
    );
}

export function Submit({ processing, children, className = '' }: { processing: boolean; children: React.ReactNode; className?: string }) {
    return (
        <button type="submit" className={`${actionClass} ${className}`} disabled={processing}>
            {processing && <LoaderCircle className="size-4 animate-spin" aria-hidden="true" />}
            {children}
        </button>
    );
}
