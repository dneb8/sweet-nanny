<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, MailCheck } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <AuthLayout :header="false" title="Verificar correo electrónico" description="Por favor verifica tu correo electrónico haciendo clic en el enlace que te enviamos.">
        <Head title="Verificación de correo electrónico" />
        <div class="flex flex-col items-center justify-center py-12">
            <div class="mb-6 flex flex-col items-center">
                <MailCheck class="h-16 w-16 text-primary mb-4" />
                <h2 class="text-2xl font-bold text-primary mb-2">¡Revisa tu correo!</h2>
                <p class="text-base text-muted-foreground max-w-md text-center">
                    Te hemos enviado un enlace de verificación a tu correo electrónico. Por favor, haz clic en el enlace para activar tu cuenta.<br>
                    Si no lo ves, revisa tu carpeta de spam o promociones.
                </p>
            </div>
            <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
                Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
            </div>
            <form @submit.prevent="submit" class="space-y-6 w-full max-w-xs">
                <Button :disabled="form.processing" variant="secondary" class="w-full">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2 inline-block" />
                    Reenviar correo de verificación
                </Button>
                <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm text-muted-foreground hover:text-primary transition-colors">Cerrar sesión</TextLink>
            </form>
        </div>
    </AuthLayout>
</template>
