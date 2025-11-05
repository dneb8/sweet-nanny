<script setup lang="ts">
import CtaModal from './CtaModal.vue';

defineProps<{
    show: boolean;
    message: string;
    onConfirm: () => void;
    onCancel?: () => void;
}>();

const emit = defineEmits(['update:show']);
</script>

<template>
    <CtaModal
        :show="show"
        type="delete"
        :message="message"
        :onConfirm="onConfirm"
        :onCancel="onCancel"
        @update:show="emit('update:show', $event)"
    />
</template>

