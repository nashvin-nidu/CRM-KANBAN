<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';
import { Sun, Moon } from '@lucide/vue';
import { useAppearance } from '@/composables/useAppearance';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const { updateAppearance, resolvedAppearance } = useAppearance();
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        
        <!-- Theme Switcher -->
        <div class="ml-auto flex items-center p-0.5 rounded-lg bg-muted/50 border border-sidebar-border">
            <button 
                class="px-2.5 py-1 text-xs font-medium rounded-md flex items-center gap-1.5 transition-colors cursor-pointer"
                :class="[resolvedAppearance === 'light' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground']"
                @click="updateAppearance('light')"
            >
                <Sun class="w-3.5 h-3.5" />
                Light
            </button>
            <button 
                class="px-2.5 py-1 text-xs font-medium rounded-md flex items-center gap-1.5 transition-colors cursor-pointer"
                :class="[resolvedAppearance === 'dark' ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground']"
                @click="updateAppearance('dark')"
            >
                <Moon class="w-3.5 h-3.5" />
                Dark
            </button>
        </div>
    </header>
</template>
