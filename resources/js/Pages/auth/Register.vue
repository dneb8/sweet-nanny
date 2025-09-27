<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineOptions({
  layout: AuthLayout,
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Registro" />

  <!-- CONTENEDOR PRINCIPAL -->
  <div class="flex flex-col md:flex-row min-h-[calc(100vh-4rem)] md:h-[calc(100vh-4rem)] overflow-auto md:overflow-hidden">

    <!-- IMAGEN (solo escritorio) -->
    <div class="hidden md:flex flex-1 bg-[#fcf8f8] items-center justify-center p-8">
      <img
        src="/images/Login-image1.svg"
        alt="Register Image"
        class="w-full h-full object-contain"
      />
    </div>

    <!-- FORMULARIO -->
    <div class="w-full md:w-[35%] bg-white flex items-start md:items-center justify-center p-8 md:pt-16">
      <!-- md:pt-16 → empuja el contenido solo en escritorio para que no quede tapado por el header -->
      <div class="w-full max-w-sm space-y-8">

        <!-- HEADING -->
        <div class="text-center space-y-2">
          <h1 class="text-2xl font-bold">Crear una cuenta</h1>
          <p class="text-sm text-muted-foreground">
            Ingresa tus datos a continuación para crear tu cuenta
          </p>

          <!-- IMAGEN SOLO MÓVIL -->
          <div class="block md:hidden mt-4">
            <img
              src="/images/Login-image1.svg"
              alt="Register Image"
              class="w-full h-40 object-contain"
            />
          </div>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="flex flex-col gap-6">

          <!-- NOMBRE -->
          <div class="flex flex-col gap-3">
            <Label for="name">Nombre</Label>
            <Input
              id="name"
              type="text"
              required
              autofocus
              autocomplete="name"
              v-model="form.name"
              placeholder="Nombre completo"
              class="py-3 text-base"
            />
            <InputError :message="form.errors.name" />
          </div>

          <!-- CORREO -->
          <div class="flex flex-col gap-3">
            <Label for="email">Correo electrónico</Label>
            <Input
              id="email"
              type="email"
              required
              autocomplete="email"
              v-model="form.email"
              placeholder="correo@ejemplo.com"
              class="py-3 text-base"
            />
            <InputError :message="form.errors.email" />
          </div>

          <!-- CONTRASEÑA -->
          <div class="flex flex-col gap-3">
            <Label for="password">Contraseña</Label>
            <Input
              id="password"
              type="password"
              required
              autocomplete="new-password"
              v-model="form.password"
              placeholder="Contraseña"
              class="py-3 text-base"
            />
            <InputError :message="form.errors.password" />
          </div>

          <!-- CONFIRMAR CONTRASEÑA -->
          <div class="flex flex-col gap-3">
            <Label for="password_confirmation">Confirmar contraseña</Label>
            <Input
              id="password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              v-model="form.password_confirmation"
              placeholder="Confirmar contraseña"
              class="py-3 text-base"
            />
            <InputError :message="form.errors.password_confirmation" />
          </div>

          <!-- BOTÓN -->
          <Button type="submit" class="w-full py-3 text-lg mt-4 flex justify-center items-center gap-2" :disabled="form.processing">
            <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin" />
            Crear cuenta
          </Button>
        </form>

        <!-- INICIAR SESIÓN -->
        <div class="text-center text-sm text-muted-foreground mt-6">
          ¿Ya tienes una cuenta?
          <TextLink :href="route('login')">Iniciar sesión</TextLink>
        </div>

      </div>
    </div>

  </div>
</template>
