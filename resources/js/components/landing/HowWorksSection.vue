<template>
  <section class="relative py-20 pb-8 bg-[#2A3786] text-white wave-bottom overflow-hidden">
    <!-- decorative blobs -->
    <div class="absolute -top-16 -left-30 w-56 h-56 bg-[#fff1d6] rounded-full opacity-60" aria-hidden="true"></div>
    <div class="absolute -bottom-24 -right-40 w-64 h-64 bg-[#CDE8FF] rounded-full opacity-60" aria-hidden="true"></div>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <h2 class="text-4xl sm:text-5xl font-extrabold text-center mb-12">
        ¿Cómo solicitar un servicio?
      </h2>
 <p class="text-xs text-white/50 text-center mb-12">
  Click en los textos para más ejemplos<span class="text-[#f4c2ba] font-semibold"> SweetNanny</span> siempre tu apoyo.
</p>
      <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4 items-start relative">
        <!-- Step 1 -->
        <div class="relative flex flex-col items-center text-center">
          <!-- Logo fuera del cuadro -->
          <div class="w-16 h-16 flex items-center justify-center rounded-full bg-white/20 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />        
            </svg>
          </div>
          <div
            class="relative flex flex-col items-center text-center cursor-pointer perspective hover:shadow-xl transition-shadow duration-300"
            @click="flipped[0] = !flipped[0]"
          >
            <div :class="['w-48 h-48 relative transition-transform duration-500', flipped[0] ? 'rotate-y-180' : '']" style="transform-style: preserve-3d;">
              <!-- Front -->
              <div class="absolute w-full h-full flex flex-col items-center justify-center text-center p-4 bg-white/10 rounded-lg backface-hidden">
                <h3 class="text-xl font-semibold mb-2">Regístrate / Inicia sesión</h3>
                <p class="text-sm">Verificamos tus datos para tener la seguridad de que eres tutor/tutora dentro de nuestra plataforma.</p>
              </div>
              <!-- Back -->
              <div class="absolute w-full h-full rounded-lg overflow-hidden backface-hidden rotate-y-180">
                <img src="/images/landing/tutorial1.png" class="w-full h-full object-cover" alt="Tutorial Registro" />
              </div>
            </div>
          </div>
          <!-- Arrow -->
          <div class="hidden lg:block absolute right-[-40px] top-[65%] transform -translate-y-1/2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="relative flex flex-col items-center text-center">
          <div class="w-16 h-16 flex items-center justify-center rounded-full bg-white/20 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
            </svg>
          </div>
          <div
            class="relative flex flex-col items-center text-center cursor-pointer perspective hover:shadow-xl transition-shadow duration-300"
            @click="flipped[1] = !flipped[1]"
          >
            <div :class="['w-48 h-48 relative transition-transform duration-500', flipped[1] ? 'rotate-y-180' : '']" style="transform-style: preserve-3d;">
              <div class="absolute w-full h-full flex flex-col items-center justify-center text-center p-4 bg-white/10 rounded-lg backface-hidden">
                <h3 class="text-xl font-semibold mb-2">Crea el servicio</h3>
                <p class="text-sm">Describe el tipo de servicio en un formulario para informarnos todas tus necesidades y todas tus preferencias.</p>
              </div>
              <div class="absolute w-full h-full rounded-lg overflow-hidden backface-hidden rotate-y-180">
                <img src="/images/landing/tutorial2.png" class="w-full h-full object-cover" alt="Tutorial Crear Servicio" />
              </div>
            </div>
          </div>
          <div class="hidden lg:block absolute right-[-40px] top-[65%] transform -translate-y-1/2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />

      
            </svg>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="relative flex flex-col items-center text-center">
          <div class="w-16 h-16 flex items-center justify-center rounded-full bg-white/20 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
            </svg>
          </div>
          <div
            class="relative flex flex-col items-center text-center cursor-pointer perspective hover:shadow-xl transition-shadow duration-300"
            @click="flipped[2] = !flipped[2]"
          >
            <div :class="['w-48 h-48 relative transition-transform duration-500', flipped[2] ? 'rotate-y-180' : '']" style="transform-style: preserve-3d;">
              <div class="absolute w-full h-full flex flex-col items-center justify-center text-center p-4 bg-white/10 rounded-lg backface-hidden">
                <h3 class="text-xl font-semibold mb-2">Elige tu niñera</h3>
                <p class="text-sm">A partir de tus preferencias nuestro sistema se especializa en encontrar las mejores niñeras para ti.</p>
              </div>
              <div class="absolute w-full h-full rounded-lg overflow-hidden backface-hidden rotate-y-180">
                <img src="/images/landing/tutorial3.png" class="w-full h-full object-cover" alt="Tutorial Elegir Niñera" />
              </div>
            </div>
          </div>
          <div class="hidden lg:block absolute right-[-40px] top-[65%] transform -translate-y-1/2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="relative flex flex-col items-center text-center">
          <div class="w-16 h-16 flex items-center justify-center rounded-full bg-white/20 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div
            class="relative flex flex-col items-center text-center cursor-pointer perspective hover:shadow-xl transition-shadow duration-300"
            @click="flipped[3] = !flipped[3]"
          >
            <div :class="['w-48 h-48 relative transition-transform duration-500', flipped[3] ? 'rotate-y-180' : '']" style="transform-style: preserve-3d;">
              <div class="absolute w-full h-full flex flex-col items-center justify-center text-center p-4 bg-white/10 rounded-lg backface-hidden">
                <h3 class="text-xl font-semibold mb-2">Espera la confirmación</h3>
                <p class="text-sm">Nuestros administradores confirmarán el servicio ¡Descubre la magia de <span class="text-[#f4c2ba] font-semibold"> SweetNanny</span> ahora!</p>
              </div>
              <div class="absolute w-full h-full rounded-lg overflow-hidden backface-hidden rotate-y-180 flex items-center justify-center">
                <img src="/images/Logo-SweetNanny-Claro.svg" class="w-50 h-auto object-contain" alt="Tutorial Confirmación" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- TEXTO-->

    </div>
  </section>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
const flipped = reactive([false, false, false, false])
</script>

<style scoped>
.perspective {
  perspective: 1000px;
}
.backface-hidden {
  backface-visibility: hidden;
}
.rotate-y-180 {
  transform: rotateY(180deg);
}
</style>
