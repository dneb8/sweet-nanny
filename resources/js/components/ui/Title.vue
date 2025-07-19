<script setup lang="ts">
defineProps<{
    icon: string;
}>();
</script>

<template>
    <div class="flex flex-row items-center mb-4">
        <Icon :icon="icon" class="text-primary inline-block mr-3 text-3xl" />
        <h1 class="text-3xl font-bold flex items-center"><slot /></h1>
    </div>
</template>