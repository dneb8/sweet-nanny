<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { Review } from '@/types/Review';
import Heading from '@/components/Heading.vue';
import ReviewTable from './components/ReviewTable.vue';

defineProps<{
    reviews: FetcherResponse<Review>;
}>();
</script>

<template>
    <Head title="Reviews" />

    <div class="mb-4 flex items-center justify-between">
        <Heading icon="mdi:star-outline" title="Listado de Reviews" />
    </div>

    <ReviewTable :resource="reviews" />
</template>
