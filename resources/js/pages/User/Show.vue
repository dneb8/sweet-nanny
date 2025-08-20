<script lang="ts" setup>

import type { User } from '@/types/User'
import Heading from '@/components/Heading.vue';
const props = defineProps<{
    user: User
    columns: string[]
    }>();   
</script>

<template>
     <Heading icon='proicons:person-multiple' :title=props.user.name />
</template>