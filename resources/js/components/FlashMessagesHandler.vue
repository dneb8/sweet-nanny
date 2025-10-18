<script setup lang="ts">
import { useFlashMessages } from '@/composables/useFlashMessages';

// This component is mounted at the root level to handle flash messages globally
useFlashMessages();
</script>

<template>
    <div style="display: none;"></div>
</template>
