<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
        <!-- Left Side: Image (Desktop only) -->
        <div class="relative hidden bg-muted lg:block">
            <img
                src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80"
                alt="Authentication Background"
                class="absolute inset-0 h-full w-full object-cover dark:brightness-75"
            />
            <!-- Overlay visual description -->
            <div
                class="absolute inset-0 flex flex-col justify-end bg-gradient-to-t from-black/60 to-transparent p-12 text-white"
            >
                <div class="mb-3 flex items-center gap-2">
                    <AppLogoIcon class="size-8 fill-current text-white" />
                    <span class="text-xl font-bold tracking-tight"
                        >Streamline CRM</span
                    >
                </div>
                <p class="max-w-sm text-sm text-white/80">
                    Manage your sales pipeline, track deals, and optimize your
                    conversions with our intuitive visual Kanban interface.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Content -->
        <div
            class="flex flex-col items-center justify-center p-6 sm:p-10 lg:p-16"
        >
            <div class="w-full max-w-sm space-y-6">
                <div class="flex flex-col items-center gap-4 text-center">
                    <Link
                        :href="home()"
                        class="flex items-center gap-2 font-medium"
                    >
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-foreground dark:text-white"
                            />
                        </div>
                        <span
                            class="text-xl font-bold tracking-tight text-foreground"
                            >Streamline CRM</span
                        >
                    </Link>
                    <div class="space-y-2">
                        <h1
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            {{ title }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
