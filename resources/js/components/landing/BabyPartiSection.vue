<template>
  <!-- Event Services Section -->
  <section class="py-20 bg-[#FFF0F8] relative overflow-hidden">
    <!-- animated decorative blobs -->
    <div
      class="absolute -top-16 -left-20 w-56 h-56 bg-[#CDE8FF] rounded-full opacity-40 animate-blob"
      aria-hidden="true"
    ></div>
    <div
      class="absolute -bottom-20 -right-24 w-64 h-64 bg-[#E5DBFF] rounded-full opacity-40 animate-blob animation-delay-2000"
      aria-hidden="true"
    ></div>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center relative z-10">
      <div class="md:w-1/2 mb-8 md:mb-0">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
          Estamos listas para todos tus eventos
        </h2>
        <p class="text-gray-700 mb-6">
          Nuestras niñeras profesionales están preparadas para acompañarte en fiestas infantiles, cumpleaños y
          eventos especiales. Decoración, juegos y entretenimiento garantizado para los más pequeños.
        </p>
        <!-- Botón funcional -->
        <Link
          :href="route('login')"
          class="inline-block px-4 py-1.5 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-md transition text-xs sm:text-sm"
        >
          Inicia sesión para reservar
        </Link>
      </div>
      <div class="md:w-1/2 flex justify-center">
        <img
          src="/images/landing/pink-nanny-landing.jpg"
          alt="Niñera en eventos infantiles"
          class="w-80 h-60 rounded-lg object-cover shadow-lg"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<style scoped>
/* Animación de blobs */
@keyframes blob {
  0%, 100% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(20px, -10px) scale(1.05);
  }
  66% {
    transform: translate(-15px, 10px) scale(0.95);
  }
}

.animate-blob {
  animation: blob 8s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
