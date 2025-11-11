<template>
  <section
    id="home"
    class="relative h-screen flex items-center justify-center text-center overflow-hidden"
  >
    <!-- Carrusel de imágenes -->
    <div class="absolute inset-0">
      <Swiper
        :modules="[Autoplay, EffectFade]"
        effect="fade"
        :fade-effect="{ crossFade: true }"
        :slides-per-view="1"
        :loop="false"
        :allow-touch-move="true"
        :autoplay="{ delay: 5000, disableOnInteraction: false }"
        class="h-full w-full"
      >
        <SwiperSlide v-for="(image, i) in images" :key="i">
          <div class="relative w-full h-full">
            <img
              :src="image"
              alt="SweetNanny fondo"
              class="absolute inset-0 w-full h-full object-cover object-center"
            />
            <div class="absolute inset-0 bg-black opacity-40"></div>
          </div>
        </SwiperSlide>
      </Swiper>
    </div>

    <!-- Contenido -->
    <div
      class="relative z-10 text-center text-[#f4c2ba] drop-shadow-[0_0_10px_rgba(0,0,0,0.8)] px-6"
    >
      <h1
        class="text-3xl sm:text-4xl md:text-6xl font-extrabold leading-tight mb-4 text-white break-words"
      >
        SweetNanny: Tu mejor opción
      </h1>

      <p
        class="text-base sm:text-lg md:text-2xl max-w-2xl mx-auto text-white/90 leading-relaxed break-words"
      >
        ¡Permítenos apoyarte en tus días más ocupados!
      </p>

      <!-- Botones -->
      <div
        class="mt-8 flex flex-col sm:flex-row justify-center items-center gap-4 flex-wrap"
      >
        <!-- Lógica de autenticación -->
        <template v-if="$page.props.auth && $page.props.auth.user">
          <Link
            :href="route('dashboard')"
            class="inline-block px-5 py-2.5 sm:px-6 sm:py-3 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-lg transition w-4/5 sm:w-auto text-sm sm:text-base"
          >
            Dashboard
          </Link>
        </template>
        <template v-else>
          <Link
            :href="route('login')"
            class="inline-block px-5 py-2.5 sm:px-6 sm:py-3 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-lg transition w-4/5 sm:w-auto text-sm sm:text-base"
          >
            Inicia sesión
          </Link>
          <Link
            :href="route('register')"
            class="inline-block px-5 py-2.5 sm:px-6 sm:py-3 border border-[#f4c2ba] text-[#f4c2ba] hover:bg-[#f4c2ba] hover:text-white font-medium rounded-full shadow-lg transition w-4/5 sm:w-auto text-sm sm:text-base"
          >
            Regístrate
          </Link>
        </template>
      </div>

      <!-- Texto adicional -->
      <p
        class="mt-16 text-sm sm:text-base text-white/70 max-w-xs sm:max-w-md mx-auto leading-relaxed px-2 sm:px-0 break-words"
      >
        Cuidado y confianza en un solo click
        <span class="text-[#f4c2ba] font-semibold"> SweetNanny</span>.<br />
        siempre estará para ti.
        <span class="block mt-2">¡Registrate para descubrir la magia!</span>
      </p>
    </div>

    <!-- Degradado inferior -->
    <div
      class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-black/70 to-transparent"
    ></div>
  </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectFade } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/effect-fade'

// Lista de imágenes tipada
const images: string[] = [
  '/images/landing/babysitter1-landing.jpg',
  '/images/landing/babysitter2-landing.jpg',
  '/images/landing/babysitter3-landing.jpg',
  '/images/landing/babysitter4-landing.jpg',
  '/images/landing/babysitter5-landing.jpg',
]
</script>

<style scoped>
.swiper-slide {
  transition: opacity 4s ease-in-out;
}

/* Texto sin cortes */
h1,
p {
  word-break: break-word;
  overflow-wrap: break-word;
  white-space: normal;
}

/* Ajuste de tipografía móvil */
@media (max-width: 640px) {
  h1 {
    font-size: 1.7rem;
    line-height: 1.3;
    padding: 0 1rem;
  }

  p {
    font-size: 0.95rem;
    line-height: 1.1;
  }

  .mt-16 {
    margin-top: 2.5rem !important;
  }
}
</style>
