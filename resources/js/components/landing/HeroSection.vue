<template>
  <section
    id="home"
    class="relative h-screen flex items-center justify-center text-center overflow-hidden"
  >
    <!-- Carrusel de imágenes -->
    <div class="absolute inset-0">
      <swiper
        :modules="[Autoplay, EffectFade]"
        effect="fade"
        :loop="true"
        :autoplay="{ delay: 5000, disableOnInteraction: false }"
        class="h-full w-full"
      >
        <swiper-slide v-for="(image, i) in images" :key="i">
          <div class="relative w-full h-full">
            <img
              :src="image"
              alt="SweetNanny fondo"
              class="absolute inset-0 w-full h-full object-cover object-center"
            />
            <!-- Oscurecer un poco las imágenes -->
            <div class="absolute inset-0 bg-black opacity-40"></div>
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <!-- Contenido -->
    <div
      class="relative z-10 text-center text-[#f4c2ba] drop-shadow-[0_0_10px_rgba(0,0,0,0.8)] px-6"
    >
      <h1
        class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-4 text-white"
      >
        SweetNanny: Tu mejor opción
      </h1>
      <p class="text-lg sm:text-xl md:text-2xl max-w-2xl mx-auto text-white/90">
        ¡Permítenos apoyarte en tus días más ocupados!
      </p>

      <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
        <Link
          :href="route('login')"
          class="inline-block px-6 py-3 bg-[#f4c2ba] hover:bg-[#e9a7a0] text-white font-medium rounded-full shadow-lg transition"
        >
          Inicia sesión
        </Link>
        <Link
          :href="route('register')"
          class="inline-block px-6 py-3 border border-[#f4c2ba] text-[#f4c2ba] hover:bg-[#f4c2ba] hover:text-white font-medium rounded-full shadow-lg transition"
        >
          Regístrate
        </Link>
      </div>

    <!-- Texto adicional sutil -->
    <p
    class="mt-25 text-sm sm:text-base text-white/70 max-w-md mx-auto leading-relaxed"
    >
    Para solicitar un servicio es necesario iniciar sesión y así comenzar con la
    magia de <span class="text-[#f4c2ba] font-semibold">SweetNanny</span>.<br />
    <span class="block mt-1">Si no tienes cuenta, regístrate.</span>
    </p>


    </div>

    <!-- Degradado sutil inferior -->
    <div
      class="absolute bottom-0 left-0 right-0 h-40 bg-gradient-to-t from-black/70 to-transparent"
    ></div>
  </section>
</template>

<script setup>
import { Link } from "@inertiajs/vue3"
import { Swiper, SwiperSlide } from "swiper/vue"
import { Autoplay, EffectFade } from "swiper/modules"
import "swiper/css"
import "swiper/css/effect-fade"

// Aquí puedes poner tus imágenes HD (debes guardarlas en /public o importar)
const images = [
  "/images/landing/babysitter1-landing.jpg",
  "/images/landing/babysitter2-landing.jpg",
  "/images/landing/babysitter3-landing.jpg",
  "/images/landing/babysitter4-landing.jpg",
  "/images/landing/babysitter5-landing.jpg",
]
</script>

<style scoped>
.swiper-slide {
  transition: opacity 4s ease-in-out;
}
</style>
