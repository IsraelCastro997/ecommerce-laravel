import { useState } from 'react';

type Appearance = 'light' | 'dark' | 'system';

function savedAppearance(): Appearance {
    const saved = localStorage.getItem('appearance');
    return saved === 'light' || saved === 'dark' ? saved : 'system';
}

const scheme = window.matchMedia('(prefers-color-scheme: dark)');

function applyAppearance(value: Appearance) {
    document.documentElement.classList.toggle('dark', value === 'dark' || (value === 'system' && scheme.matches));
}

export function initializeTheme() {
    applyAppearance(savedAppearance());
    scheme.addEventListener('change', () => applyAppearance(savedAppearance()));
}

export function useAppearance() {
    const [appearance, setAppearance] = useState(savedAppearance);

    function updateAppearance(value: Appearance) {
        setAppearance(value);
        localStorage.setItem('appearance', value);
        applyAppearance(value);
    }

    return { appearance, updateAppearance };
}
