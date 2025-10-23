<script setup>
const props = defineProps({
    value: Boolean,
});
</script>

<template>
    <input
        type="checkbox"
        class="form-check-input"
        :checked="value"
        @input.stop="$emit('selected', $event.target.checked)"
        @change.stop
    />
</template>
