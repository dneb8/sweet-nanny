<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import { Icon } from "@iconify/vue"
</script>

<template>
  <Head title="Admin Dashboard" />

  <section class="min-h-screen flex flex-col items-center justify-center px-6 py-12 bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Encabezado con icono -->
    <div class="flex items-center gap-3 mb-6">
      <Icon icon="mdi:shield-crown" class="w-10 h-10 text-emerald-600 dark:text-emerald-400" />
      <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-gray-100">
        Bienvenido al Panel de Administración
      </h1>
    </div>

    <!-- Subtítulo -->
    <p class="text-lg text-center text-gray-600 dark:text-gray-300 max-w-2xl mb-8">
      Aquí puedes gestionar usuarios, niñeras, tutores y toda la configuración de la plataforma. 
      Usa el menú lateral para navegar entre las secciones.
    </p>

    <!-- Tarjetas de acceso rápido -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full max-w-5xl">
      <div
        class="flex flex-col items-center gap-3 p-6 rounded-2xl shadow-md bg-white dark:bg-gray-800 transition hover:shadow-lg"
      >
        <Icon icon="mdi:account-multiple" class="w-8 h-8 text-sky-600 dark:text-sky-400" />
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Usuarios</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
          Administra cuentas de tutores, niñeras y otros administradores.
        </p>
      </div>

      <div
        class="flex flex-col items-center gap-3 p-6 rounded-2xl shadow-md bg-white dark:bg-gray-800 transition hover:shadow-lg"
      >
        <Icon icon="mdi:calendar-clock" class="w-8 h-8 text-purple-600 dark:text-purple-400" />
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Bookings</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
          Supervisa y gestiona todas las reservas y citas de la plataforma.
        </p>
      </div>

      <div
        class="flex flex-col items-center gap-3 p-6 rounded-2xl shadow-md bg-white dark:bg-gray-800 transition hover:shadow-lg"
      >
        <Icon icon="mdi:cog-outline" class="w-8 h-8 text-amber-600 dark:text-amber-400" />
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Configuración</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
          Personaliza ajustes, roles y permisos de la aplicación.
        </p>
      </div>
    </div>
  </section>
</template>
