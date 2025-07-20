<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
    icon?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="mb-8 space-y-0.5">
        <div class="flex flex-row items-center gap-1">
            <Icon v-if="icon" :icon="icon" class="text-primary inline-block text-2xl" />
            <h2 class="text-xl font-semibold tracking-tight">{{ title }}</h2>
        </div>
        <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
        </p>
    </div>
</template>
