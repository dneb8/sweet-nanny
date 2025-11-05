<template>
  <section id="rates" class="py-20 bg-[#2A3786] text-white relative overflow-hidden">
    <!-- decorative blobs -->
    <div
      class="absolute -top-24 -left-20 w-60 h-60 bg-[#CDE8FF] rounded-full opacity-40"
      aria-hidden="true"
    ></div>
    <div
      class="absolute -bottom-28 -right-28 w-72 h-72 bg-[#FFDCEB] rounded-full opacity-40"
      aria-hidden="true"
    ></div>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
      <h2 class="text-3xl sm:text-4xl font-extrabold mb-12">Tarifas de Servicios</h2>
      
      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Por Hora -->
        <div class="bg-white/10 p-8 rounded-xl flex flex-col items-center">
          <h3 class="text-xl font-semibold mb-2">Por Hora</h3>
          <p class="text-4xl font-extrabold mb-4">$168 MXN</p>
          
          <!-- Imagen debajo del precio -->
          <div class="mb-4">
            <img src="/images/landing/beis-nanny-landing.jpg" alt="Por Hora" class="w-32 h-32 rounded-lg object-cover" />
          </div>
          
          <p class="text-sm">
            Ideal para recados cortos o una salida por la tarde.
          </p>
        </div>

        <!-- Medio Día -->
        <div class="bg-white/10 p-8 rounded-xl flex flex-col items-center">
          <h3 class="text-xl font-semibold mb-2">Medio Día</h3>
          <p class="text-4xl font-extrabold mb-4">$1,999 MXN</p>
          
          <!-- Imagen debajo del precio -->
          <div class="mb-4">
            <img src="/images/landing/car-nanny-landing.jpg" alt="Medio Día" class="w-32 h-32 rounded-lg object-cover" />
          </div>
          
          <p class="text-sm">
            12 horas de cuidado dedicado. Perfecto para una mañana o tarde ocupada.
          </p>
        </div>

        <!-- Día Completo -->
        <div class="bg-white/10 p-8 rounded-xl flex flex-col items-center">
          <h3 class="text-xl font-semibold mb-2">Día Completo</h3>
          <p class="text-4xl font-extrabold mb-4">$3,800 MXN</p>
          
          <!-- Imagen debajo del precio -->
          <div class="mb-4">
            <img src="/images/landing/fut-nanny-landing.jpg" alt="Día Completo" class="w-32 h-32 rounded-lg object-cover" />
          </div>
          
          <p class="text-sm">
            24 horas completas de cuidado incluyendo comidas, juegos y actividades de aprendizaje.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
