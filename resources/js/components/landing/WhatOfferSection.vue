<template>
  <section id="services" class="relative py-24 bg-white overflow-hidden">
    <!-- Manchas decorativas -->
    <div class="absolute -top-16 -left-10 w-60 h-60 bg-[#f4c2ba]/60 blur-1xl rounded-full"></div>
    <div class="absolute top-20 right-0 w-72 h-72 bg-[#dcecff]/60 blur-1xl rounded-full"></div>
    <div class="absolute bottom-0 left-16 w-64 h-64 bg-[#fff1d6]/60 blur-1xl rounded-full"></div>

    <div class="relative max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Subtítulo tipo “mancha” -->
    <div class="flex justify-center mb-4">
      <span
        class="relative px-8 py-3 text-base font-semibold mb-2 text-white uppercase tracking-widest"
        style="background-color: #f4c2ba; border-radius: 1rem; box-shadow: 0 1px 10px rgba(0,0,0,0.3); "
      >
        Nuestros servicios
      </span>
    </div>



      <!-- Título principal -->
      <h2 class="text-3xl sm:text-4xl font-extrabold text-center mb-16 text-gray-800">
        
      </h2>

      <!-- Grid de servicios -->
      <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="(service, index) in services"
          :key="index"
          class="relative group bg-gray-50 rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition"
        >
          <!-- Imagen con profundidad -->
          <div class="relative overflow-hidden">
            <img
              :src="service.image"
              :alt="service.title"
              class="w-full h-48 object-cover transform group-hover:scale-110 transition duration-500"
            />
            <!-- Overlay al pasar el cursor -->
            <div
             class="absolute inset-0 opacity-0 group-hover:opacity-100 transition duration-500 flex items-center justify-center text-center px-4 rounded-2xl"
             :style="{ backgroundColor: service.overlay }"
            >
             <p class="text-white text-sm" v-html="service.details"></p>

            </div>
          </div>

          <!-- Contenido de la tarjeta -->
          <div class="p-6 text-center">
            <div
              class="w-16 h-16 flex items-center justify-center rounded-full mx-auto mb-4"
              :style="{ backgroundColor: service.bg }"
            >
              <component :is="service.icon" class="w-8 h-8" :class="service.color"></component>
            </div>
            <h3 class="text-xl font-semibold mb-2 text-gray-800">{{ service.title }}</h3>
            <p class="text-gray-500 text-sm">{{ service.text }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { h } from 'vue'

// Usamos iconos SVG como componentes
const HeartIcon = {
  render() {
    return h('svg', {
      xmlns: 'http://www.w3.org/2000/svg',
      fill: 'none',
      viewBox: '0 0 24 24',
      strokeWidth: 1.5,
      stroke: 'currentColor',
      class: 'text-pink-500',
      innerHTML: ` <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />`,
    })
  },
}



const HomeIcon = {
  render() {
    return h('svg', {
      xmlns: 'http://www.w3.org/2000/svg',
      fill: 'none',
      viewBox: '0 0 24 24',
      strokeWidth: 1.5,
      stroke: 'currentColor',
      class: 'text-blue-600',
      innerHTML: `  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />`,
    })
  },
}

const PuzzleIcon = {
  render() {
    return h('svg', {
      xmlns: 'http://www.w3.org/2000/svg',
      fill: 'none',
      viewBox: '0 0 24 24',
      strokeWidth: 1.5,
      stroke: 'currentColor',
      class: 'text-purple-500',
      innerHTML: ` <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />`,
    })
  },
}

const services = [
  {
    title: 'Todo tipo de niñeras',
    text: 'Contamos con niñeras capacitadas para el cuidado de tus pequeñ@s a cualquier hora del día.',
    details: '<span style="text-shadow: 0 1px 3px rgba(0,0,0,0.6);">Niñeras pacientes, cariñosas, creativas, responsable y cuidadosas para tus pequeñ@s.</span>',
    image: '/images/landing/drawing-nanny-landing.jpg',
    icon: HeartIcon,
    bg: '#FFE3EF',
    color: 'text-pink-500',
    overlay: 'rgba(244, 194, 186, 0.4)',
    colorHover: 'text-[#f4c2ba]'
  },
  {
    title: 'Servicios adecuados',
    text: 'Planificación personalizada para cada familia, podemos asistir a tu hogar o a cualquier sitio.',
    details: '<span style="text-shadow: 0 1px 3px rgba(0,0,0,0.6);">Asistimos a eventos, hogares, oficinas, parques... Te apoyamos en cualquier circunstancia</span>',
    image: '/images/landing/house-nanny-landing.jpg',
    icon: HomeIcon,
    overlay: 'rgba(220, 236, 255, 0.4)',
    bg: '#DCEBFF',
    color: 'text-blue-500',
    colorHover: 'text-[#dcecff]'
  },
  {
    title: 'Actividades variadas',
    text: 'Nos empeñamos en ofrecer a tus pequeños un ambiente educativo y creativo priorizando tu tranquilidad',
    details: '<span style="text-shadow: 0 1px 3px rgba(0,0,0,0.6);">Priorizamos formas educativas de juevo, creatividad, danza y actividades a tu gusto</span>',
    image: '/images/landing/two-nanny-landing.jpg',
    icon: PuzzleIcon,
    overlay: 'rgba(255, 241, 214, 0.4)',
    bg: '#FFF1D6',
    color: 'text-yellow-500',
    colorHover: 'text-[#fff1d6]'
  },
]


</script>
