<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineOptions({
  layout: AuthLayout,
});

defineProps<{
  status?: string;
  canResetPassword: boolean;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Iniciar sesión" />

  <!-- CONTENEDOR PRINCIPAL -->
  <div
    class="flex flex-col md:flex-row min-h-[calc(100vh-4rem)] md:h-[calc(100vh-4rem)] overflow-auto md:overflow-hidden
           bg-white text-gray-900 dark:bg-[#121212] dark:text-gray-100 transition-colors duration-300"
  >
    <!-- IMAGEN (solo escritorio) -->
    <div
      class="hidden md:flex flex-1 bg-[#fcf8f8] dark:bg-[#1c1c1c] items-center justify-center p-8"
    >
      <img
        src="/images/Login-image1.svg"
        alt="Login Image"
        class="w-full h-full object-contain"
      />
    </div>

    <!-- FORMULARIO -->
      <div
        class="w-full md:w-[30%] bg-white dark:bg-[#1c1c1c]/20 dark:backdrop-blur-md flex items-center justify-center p-8 border-l-0"
      >

      <div class="w-full max-w-sm space-y-8">

        <!-- STATUS -->
        <div v-if="status" class="text-center text-sm font-medium text-green-600 dark:text-green-400">
          {{ status }}
        </div>

        <!-- HEADING -->
        <div class="text-center space-y-2">
          <h1 class="text-2xl font-bold dark:text-white">Iniciar sesión</h1>
          <p class="text-sm text-muted-foreground dark:text-gray-400">
            Ingresa tu correo electrónico y contraseña para continuar
          </p>

          <!-- IMAGEN SOLO MÓVIL -->
          <div class="block md:hidden mt-4">
            <img
              src="/images/Login-image1.svg"
              alt="Login Image"
              class="w-full h-40 object-contain"
            />
          </div>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="flex flex-col gap-6">
          <!-- CORREO ELECTRÓNICO -->
          <div class="flex flex-col gap-3">
            <Label for="email" class="dark:text-gray-200">Correo electrónico</Label>
            <Input
              id="email"
              type="email"
              required
              autofocus
              autocomplete="email"
              v-model="form.email"
              placeholder="correo@ejemplo.com"
              class="py-3 text-base bg-gray-50 dark:bg-[#2a2a2a] dark:text-gray-100 dark:placeholder-gray-400 border dark:border-gray-600"
            />
            <InputError :message="form.errors.email" />
          </div>

          <!-- CONTRASEÑA -->
          <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between">
              <Label for="password" class="dark:text-gray-200">Contraseña</Label>
              <TextLink
                v-if="canResetPassword"
                :href="route('password.request')"
                class="text-sm dark:text-blue-400 hover:underline"
              >
                ¿Olvidaste tu contraseña?
              </TextLink>
            </div>
            <Input
              id="password"
              type="password"
              required
              autocomplete="current-password"
              v-model="form.password"
              placeholder="Contraseña"
              class="py-3 text-base bg-gray-50 dark:bg-[#2a2a2a] dark:text-gray-100 dark:placeholder-gray-400 border dark:border-gray-600"
            />
            <InputError :message="form.errors.password" />
          </div>

          <!-- RECORDARME -->
          <div class="flex items-center space-x-3">
            <Checkbox id="remember" v-model="form.remember" class="dark:border-gray-500" />
            <Label for="remember" class="m-0 dark:text-gray-200">Recuérdame</Label>
          </div>

          <!-- BOTÓN -->
          <Button
            type="submit"
            class="w-full py-3 text-lg mt-3 flex justify-center items-center gap-2 
                  text-white bg-[#c27a7a] hover:bg-[#ab6a6a] dark:bg-[#c27a7a] dark:hover:bg-[#945c5c] transition-colors duration-300"
            :disabled="form.processing"
          >

            <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin" />
            Iniciar sesión
          </Button>
        </form>

        <!-- REGISTRO -->
        <div class="text-center text-sm text-muted-foreground mt-6 dark:text-gray-400">
          ¿No tienes una cuenta?
          <TextLink :href="route('register')" class="dark:text-blue-400 hover:underline">
            Regístrate
          </TextLink>
        </div>
      </div>
    </div>
  </div>
</template>
