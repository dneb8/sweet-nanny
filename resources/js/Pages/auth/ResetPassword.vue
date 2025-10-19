<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { LoaderCircle, LockKeyhole } from 'lucide-vue-next'
import { computed } from 'vue'

defineOptions({ layout: AuthLayout })

interface Props {
  token: string
  email: string
}
const props = defineProps<Props>()

const form = useForm({
  token: props.token,
  email: props.email, // seguimos enviando el email, pero SIN mostrarlo
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}

const canSubmit = computed(() => {
  const okLength = (form.password?.length ?? 0) >= 8
  const match = form.password === form.password_confirmation
  return okLength && match && !form.processing
})
</script>

<template>
  <AuthLayout
    :header="false"
    title="Restablecer contraseña"
    description="Crea una nueva contraseña para continuar"
  >
    <Head title="Restablecer contraseña" />

    <div class="flex flex-col items-center justify-center py-12">
        <div class="mb-6 flex flex-col items-center">
            <LockKeyhole class="mb-4 h-16 w-16 text-primary" />
            <h2 class="mb-2 text-2xl font-bold text-primary">
            Crea una nueva contraseña
            </h2>
            <p class="max-w-md text-center text-base text-muted-foreground">
            Estás restableciendo la contraseña de
            <strong>{{ form.email }}</strong>. El enlace es válido por
            <strong>60 minutos</strong>.
            </p>
        </div>

      <form @submit.prevent="submit" class="w-full max-w-xs space-y-6">
        <input type="hidden" name="email" :value="form.email" />
        <input type="hidden" name="token" :value="form.token" />

        <div class="grid gap-2">
          <Label for="password">Nueva contraseña</Label>
          <Input
            id="password"
            type="password"
            name="password"
            autocomplete="new-password"
            v-model="form.password"
            placeholder="••••••••"
            autofocus
          />
          <InputError :message="form.errors.password" />
          <p class="text-xs text-muted-foreground">
            Mínimo 8 caracteres. Usa mayúsculas, minúsculas y números si es posible.
          </p>
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation">Confirmar contraseña</Label>
          <Input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            autocomplete="new-password"
            v-model="form.password_confirmation"
            placeholder="••••••••"
          />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <Button type="submit" class="w-full" :disabled="!canSubmit">
          <LoaderCircle
            v-if="form.processing"
            class="mr-2 inline-block h-4 w-4 animate-spin"
          />
          Restablecer contraseña
        </Button>
      </form>
    </div>
  </AuthLayout>
</template>
