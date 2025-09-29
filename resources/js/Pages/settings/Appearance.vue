<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

import SettingsLayout from '@/layouts/settings/Layout.vue';
</script>

<template>
    <Head title="Configuración de apariencia" />

    <SettingsLayout>
        <div class="space-y-6">
            <HeadingSmall title="Configuración de apariencia" description="Actualiza la configuración de apariencia de tu cuenta" />
            <AppearanceTabs />
        </div>
    </SettingsLayout>
</template>
