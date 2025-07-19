<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex flex-row">
        <div class="flex border-none aspect-square size-10 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
        </div>
        <div class="ml-1 grid flex-1 text-left text-base">
            <span class="mb-0.5 truncate leading-tight font-semibold">SweetNanny</span>
        </div>
    </div>
</template>
