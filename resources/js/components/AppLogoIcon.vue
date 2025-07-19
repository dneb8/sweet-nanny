<script setup lang="ts">
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <SidebarTrigger />
</template>
