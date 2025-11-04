<script setup lang="ts">
import type { Tutor } from '@/types/Tutor';

// Import cards
import ProfileCard from './information/Profile.vue';
import AddressCard from './information/Address.vue';

const props = defineProps<{ tutor: Tutor }>();
</script>

<template>
    <div class="grid grid-cols-1 gap-5">
        <!-- Upper column -->
        <div class="space-y-5">
            <ProfileCard :tutor="props.tutor" />
            <div class="grid md:grid-cols-2 gap-5">
                <AddressCard :tutor="props.tutor" />
            </div>
        </div>
    </div>
</template>
