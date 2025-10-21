<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { LoaderCircle, KeyRound } from 'lucide-vue-next'

defineOptions({ layout: AuthLayout })

defineProps<{ status?: string }>()

const form = useForm({ email: '' })

const submit = () => {
  form.post(route('password.email'))
}
</script>

<template>
  <AuthLayout
    :header="false"
    title="Restablecer contraseña"
    description="Te enviaremos un enlace para crear una nueva contraseña."
  >
    <Head title="Olvidaste tu contraseña" />

    <div class="flex flex-col items-center justify-center py-12">
      <!-- Hero / encabezado -->
      <div class="mb-6 flex flex-col items-center">
        <KeyRound class="mb-4 h-16 w-16 text-primary" />
        <h2 class="mb-2 text-2xl font-bold text-primary">¿Olvidaste tu contraseña?</h2>
        <p class="max-w-md text-center text-base text-muted-foreground">
          Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
          Si no lo ves en unos minutos, revisa tu carpeta de spam o promociones.
        </p>
      </div>

      <!-- Mensaje de estado -->
      <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
      >
        {{ status }}
      </div>

      <!-- Formulario -->
      <form @submit.prevent="submit" class="w-full max-w-xs space-y-6">
        <div class="grid gap-2">
          <Label for="email">Correo electrónico</Label>
          <Input
            id="email"
            type="email"
            name="email"
            inputmode="email"
            autocomplete="email"
            v-model="form.email"
            autofocus
            placeholder="correo@ejemplo.com"
          />
          <InputError :message="form.errors.email" />
        </div>

        <Button class="w-full" :disabled="form.processing || !form.email">
          <LoaderCircle
            v-if="form.processing"
            class="mr-2 inline-block h-4 w-4 animate-spin"
          />
          Enviar enlace de restablecimiento
        </Button>

        <TextLink
          :href="route('login')"
          class="mx-auto block text-center text-sm text-muted-foreground hover:text-primary transition-colors"
        >
          Volver a iniciar sesión
        </TextLink>
      </form>
    </div>
  </AuthLayout>
</template>
