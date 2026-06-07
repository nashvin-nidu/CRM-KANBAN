<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { kanban } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kanban',
                href: kanban(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Kanban" />

    <div class="p-4">
        <h1>Hello</h1>
    </div>
</template>
