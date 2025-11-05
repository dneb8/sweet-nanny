<template>
  <!-- Navigation bar -->
  <header
    class="fixed top-0 left-0 right-0 z-30 bg-[#f4c2ba]/80 backdrop-blur-sm shadow-[0_4px_20px_rgba(255,255,255,0.2)] transition-all duration-700 ease-in-out"
  >
    <nav
      class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between"
    >
      <!-- LOGO -->
      <div class="flex items-center space-x-2">
        <img src="/images/Logo-SweetNanny-Claro.svg" alt="Logo" class="h-10" />
      </div>

      <!-- LINKS DE ESCRITORIO -->
      <ul class="hidden md:flex items-center space-x-6 text-sm font-medium">
        <li><a href="#" class="text-white hover:text-pink-400">Inicio</a></li>
        <li><a href="#services" class="text-white hover:text-pink-400">Servicios</a></li>
        <li><a href="#about" class="text-white hover:text-pink-400">Acerca de</a></li>
        <li><a href="#rates" class="text-white hover:text-pink-400">Tarifas</a></li>

        <!-- Lógica de autenticación -->
        <template v-if="$page.props.auth.user">
          <li>
            <Link
              :href="route('dashboard')"
              class="inline-block px-4 py-1.5 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-md transition text-xs sm:text-sm"
            >
              Dashboard
            </Link>
          </li>
        </template>
        <template v-else>
          <li>
            <Link
              :href="route('login')"
              class="inline-block px-4 py-1.5 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-md transition text-xs sm:text-sm"
            >
              Iniciar sesión
            </Link>
          </li>
          <li>
            <Link
              :href="route('register')"
              class="inline-block px-4 py-1.5 bg-white text-[#f4c2ba] hover:bg-[#e9a7a0] hover:text-white border border-white font-medium rounded-full shadow-md transition text-xs sm:text-sm"
            >
              Registrarse
            </Link>
          </li>
        </template>
      </ul>

      <!-- BOTÓN MENÚ MÓVIL -->
      <button
        class="md:hidden flex items-center justify-center w-10 h-10 rounded-md focus:outline-none focus:ring-2 focus:ring-white"
        @click="mobileMenuOpen = !mobileMenuOpen"
        aria-label="Toggle menu"
      >
        <svg
          v-if="!mobileMenuOpen"
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6 text-white"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg
          v-else
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6 text-white"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </nav>

    <!-- MENÚ MÓVIL -->
    <transition name="fade">
      <div
        v-show="mobileMenuOpen"
        class="md:hidden absolute left-0 right-0 mt-2 bg-[#f4c2ba]/95 shadow-lg rounded-b-lg py-4 px-6 space-y-4 text-center"
      >
        <a href="#" class="block text-white hover:text-pink-400">Inicio</a>
        <a href="#services" class="block text-white hover:text-pink-400">Servicios</a>
        <a href="#about" class="block text-white hover:text-pink-400">Acerca de</a>
        <a href="#rates" class="block text-white hover:text-pink-400">Tarifas</a>

        <!-- Lógica de autenticación -->
        <template v-if="$page.props.auth.user">
          <Link
            :href="route('dashboard')"
            class="block px-4 py-2 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-md transition"
          >
            Dashboard
          </Link>
        </template>
        <template v-else>
          <Link
            :href="route('login')"
            class="block px-4 py-2 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-md transition"
          >
            Iniciar sesión
          </Link>
          <Link
            :href="route('register')"
            class="block px-4 py-2 border border-white text-white hover:bg-white hover:text-[#f4c2ba] font-medium rounded-full shadow-md transition"
          >
            Registrarse
          </Link>
        </template>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { Link } from "@inertiajs/vue3"

const mobileMenuOpen = ref(false)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.8s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
