<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import ReviewForm from './ReviewForm.vue';

defineProps<{
    open: boolean;
    reviewableType: 'tutor' | 'nanny';
    reviewableId: number | string;
    reviewableName?: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', v: boolean): void;
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

function handleSaved() {
    emit('saved');
    emit('update:open', false);
    emit('close');
}

function handleCancel() {
    emit('update:open', false);
    emit('close');
}
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle class="text-2xl">
                    Calificar {{ reviewableType === 'tutor' ? 'Tutor' : 'Niñera' }}
                </DialogTitle>
                <DialogDescription v-if="reviewableName">
                    Deja tu reseña para <span class="font-semibold">{{ reviewableName }}</span>
                </DialogDescription>
            </DialogHeader>

            <ReviewForm
                :reviewable-type="reviewableType"
                :reviewable-id="reviewableId"
                @saved="handleSaved"
                @cancel="handleCancel"
            />
        </DialogContent>
    </Dialog>
</template>
