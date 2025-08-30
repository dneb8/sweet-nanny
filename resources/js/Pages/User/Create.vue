<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserForm from './partials/UserForm.vue';
import Heading from '@/components/Heading.vue';


defineProps<{
    roles: Array<string>;
}>();
</script>

<template>
    <Head title="Crear Usuario" />

    <Heading icon='ri:user-add-line' title="Crear Usuario"/>

    <UserForm :roles/>  
</template>
