<script setup lang="ts">
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
    <!-- #DEB: LOGO VA AQUI -->
    <Icon icon="ic:twotone-cloud" style="width: 24px; height: 24px;" class="text-primary"/>
</template>
