<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Streamline CRM Icon: Triple Stream Flow -->
        <g 
            stroke="currentColor" 
            stroke-width="2.5" 
            stroke-linecap="round" 
            stroke-linejoin="round"
            style="fill: none;"
        >
            <path d="M2 8c4-3 7-3 11 0s6 3 9 0" opacity="0.4" />
            <path d="M2 12c4-3 7-3 11 0s6 3 9 0" />
            <path d="M2 16c4-3 7-3 11 0s6 3 9 0" opacity="0.4" />
        </g>
    </svg>
</template>
