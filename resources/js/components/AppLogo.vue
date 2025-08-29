<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <Link href="/" class="flex flex-row items-center no-underline hover:opacity-80">
    <div class="flex border-none aspect-square size-10 items-center justify-center rounded-md text-sidebar-primary-foreground">
      <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid text-left text-base">
      <span class="mb-0.5 truncate leading-tight font-semibold">SweetNanny</span>
    </div>
  </Link>
</template>
