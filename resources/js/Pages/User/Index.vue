<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { FetcherResponse } from '@/types/FetcherResponse';
import type { User } from '@/types/User';
import Heading from '@/components/Heading.vue';
import UserDataTable from '@/components/data/UserDataTable.vue';
import { Button } from '@/components/ui/button';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    users: FetcherResponse<User>;
    roles: Array<string>;
    searchables: string[];
    sortables: string[];
}>();
</script>

<template>
    <Head title="Usuarios" />
    <div class="flex flex-row justify-between mb-4">
        <Heading icon="proicons:person-multiple" title="Listado de Usuarios" />
        <Link :href="route('users.create')">
            <Button>
                <Icon icon="ri:user-add-line" width="24" height="24" />
                Crear Usuario
            </Button>
        </Link>
    </div>
    <UserDataTable :users="users" />
</template>
