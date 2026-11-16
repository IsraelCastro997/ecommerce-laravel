import { useAppearance } from '@/composables/useAppearance';
import SettingsLayout from '@/layouts/settings/Layout';
import { Monitor, Moon, Sun } from 'lucide-react';

const options = [
    { value: 'light', label: 'Claro', icon: Sun },
    { value: 'dark', label: 'Oscuro', icon: Moon },
    { value: 'system', label: 'Sistema', icon: Monitor },
] as const;

export default function Appearance() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <SettingsLayout title="Apariencia">
            <section className="space-y-3">
                <h2 className="text-lg font-bold">Apariencia</h2>
                <p className="text-sm text-muted-foreground">Elige cómo se muestra Refugio Outdoor.</p>
                <div aria-label="Tema" className="flex flex-wrap gap-1 rounded-lg bg-muted p-1">
                    {options.map(({ value, label, icon: Icon }) => (
                        <button
                            key={value}
                            type="button"
                            aria-pressed={appearance === value}
                            className={`inline-flex items-center gap-1 rounded-md px-2 py-2 text-sm ${appearance === value ? 'bg-background shadow-sm' : 'text-muted-foreground'}`}
                            onClick={() => updateAppearance(value)}
                        >
                            <Icon size={16} aria-hidden="true" />
                            {label}
                        </button>
                    ))}
                </div>
            </section>
        </SettingsLayout>
    );
}
