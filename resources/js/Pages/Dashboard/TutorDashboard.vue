<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import { Icon } from "@iconify/vue"
</script>

<template>
  <Head title="Tutor Dashboard" />

  <div class="space-y-8 flex justify-center flex-col mr-auto">
    <!-- Encabezado -->
    <div class="flex items-center gap-3">
      <Icon
        icon="mdi:human-male-female-child"
        class="w-10 h-10 text-primary dark:text-primary-400"
      />
      <h1 class="text-2xl md:text-4xl font-bold text-gray-800 dark:text-gray-100">
        Bienvenido a SweetNanny
      </h1>
    </div>

    <!-- Subtítulo -->
    <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl">
      Administra tus reservas y tu perfil de manera sencilla, con todas las herramientas al alcance de tu mano.
    </p>

    <!-- Tarjetas de acceso rápido -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-4xl">
      <!-- Reservas -->
      <div
        class="flex flex-col items-center gap-3 p-6 rounded-2xl bg-white/30 dark:bg-gray-800/30 backdrop-blur-md border border-white/40 dark:border-gray-700/40 shadow-md transition hover:shadow-lg"
      >
        <Icon
          icon="mdi:calendar-account"
          class="w-8 h-8 text-primary-600 dark:text-primary-400"
        />
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Mis Reservas</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300 text-center">
          Consulta y administra todas tus reservas activas.
        </p>
      </div>

      <!-- Perfil -->
      <div
        class="flex flex-col items-center gap-3 p-6 rounded-2xl bg-white/30 dark:bg-gray-800/30 backdrop-blur-md border border-white/40 dark:border-gray-700/40 shadow-md transition hover:shadow-lg"
      >
        <Icon
          icon="mdi:account-cog"
          class="w-8 h-8 text-primary-600 dark:text-primary-400"
        />
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Perfil</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300 text-center">
          Edita la información de tu cuenta y preferencias como tutor.
        </p>
      </div>
    </div>
  </div>
</template>
