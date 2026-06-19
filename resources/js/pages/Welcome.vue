<script setup lang="ts">
import { onMounted } from 'vue';
import { router, usePage, Head } from '@inertiajs/vue3';
import { kanban, login } from '@/routes';

const page = usePage();

onMounted(() => {
    if (page.props.auth?.user) {
        router.replace(kanban());
    } else {
        router.replace(login());
    }
});
</script>

<template>
    <Head title="Redirecting..." />
    <div class="flex min-h-screen items-center justify-center bg-background">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
    </div>
</template>
