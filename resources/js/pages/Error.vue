<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ status: number }>();
const messages: Record<number, { title: string; detail: string }> = {
    401: { title: 'Inicia sesión para continuar', detail: 'Necesitas una sesión activa para abrir esta página.' },
    403: { title: 'Acceso restringido', detail: 'Tu cuenta no tiene permiso para abrir esta página.' },
    404: { title: 'Página no encontrada', detail: 'La página pudo cambiar de dirección o dejar de estar disponible.' },
    419: { title: 'Tu sesión caducó', detail: 'Inicia sesión de nuevo antes de continuar.' },
};
const message = computed(
    () =>
        messages[props.status] ?? {
            title: 'No pudimos cargar esta página',
            detail: 'Intenta volver a la tienda y abrir la página de nuevo.',
        },
);
</script>

<template>
    <Head :title="message.title" />
    <main class="grid min-h-svh place-items-center bg-background p-5">
        <div class="max-w-md rounded-xl border bg-card p-6 text-card-foreground sm:p-9">
            <p class="mb-2 text-sm font-bold text-muted-foreground">{{ status }}</p>
            <h1 class="mb-3 text-3xl font-bold tracking-tight">{{ message.title }}</h1>
            <p class="mb-6 text-muted-foreground">{{ message.detail }}</p>
            <div class="flex flex-wrap gap-4">
                <Link href="/" class="font-semibold underline underline-offset-4">Volver a la tienda</Link>
                <Link v-if="status === 401 || status === 419" :href="route('login')" class="font-semibold underline underline-offset-4"
                    >Iniciar sesión</Link
                >
            </div>
        </div>
    </main>
</template>
