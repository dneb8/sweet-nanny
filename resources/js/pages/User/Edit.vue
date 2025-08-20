<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserForm from './components/UserForm.vue';
import Heading from '@/components/Heading.vue';
import type { User } from '@/types/User';

defineProps<{
    roles: Array<string>;
    user: User;
}>();
</script>

<template>
    <Head title="Editar Usuario" />

    <Heading icon='ri:user-add-line' title="Editar Usuario"/>

    <UserForm :roles :user/>  
</template>
