<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary text-primary-foreground shadow-2xs"
    >
        <AppLogoIcon class="size-5 text-primary-foreground" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-base">
        <span class="truncate font-semibold tracking-tight text-foreground"
            >Streamline CRM</span
        >
    </div>
</template>
