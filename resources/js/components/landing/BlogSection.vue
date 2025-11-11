<template>
  <!-- Blog Section -->
  <section id="blog" class="relative py-20 bg-white overflow-hidden">
    <!-- Circulos decorativos de fondo -->
    <div class="absolute -top-16 -left-10 w-56 h-56 bg-[#f4c2ba]/60 rounded-full animate-pulse"></div>
    <div class="absolute -bottom-24 -right-20 w-64 h-64 bg-[#CDE8FF]/60 rounded-full animate-pulse"></div>
    <div class="absolute top-1/2 left-1/3 w-40 h-40 bg-[#F5A9BC]/60 rounded-full animate-pulse"></div>
    <div class="absolute bottom-10 left-1/4 w-32 h-32 bg-[#A0E7E5]/60 rounded-full animate-pulse"></div>

    <div class="relative max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <!-- Botón como hero -->
      <div class="flex justify-center mb-12">
        <span
          class="relative px-8 py-3 text-base font-semibold text-white uppercase tracking-widest"
          style="background-color: #f4c2ba; border-radius: 1rem;"
        >
          Sobre el cuidado de niñ@s.
        </span>
      </div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Blog card 1 -->
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
          <div class="p-6 text-left flex flex-col h-full">
            <h3 class="text-xl font-semibold mb-2">¿Cómo elegir a la niñera perfecta?</h3>
            <p class="text-sm text-gray-600 mb-4 flex-1">
              Aprende qué cualidades buscar y qué saber para conocer a tu niñera ideal. Siempre básate en tus necesidades y lo que necesitas para tus hij@s.
            </p>
            <a href="https://msolucionaguadalajara.com/como-elegir-a-la-ninera-ideal-para-tu-hijo/" class="text-pink-300 font-medium hover:underline mt-auto">Leer más...</a>
          </div>
        </article>

        <!-- Blog card 2 -->
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
          <div class="p-6 text-left flex flex-col h-full">
            <h3 class="text-xl font-semibold mb-2">Actividades educativas y divertidas para los niños</h3>
            <p class="text-sm text-gray-600 mb-4 flex-1">
              Es importante promover actividades divertidas y educativas, siempre y cuando se priorice la estimulación psicomotriz y del habla. 
            </p>
            <a href="https://www.bayardeducacion.com/lectura/popi/20-actividades-de-estimulacion-para-ninos?srsltid=AfmBOor1tS-hrXeWRnr_l9Hz1EF20r3M0ANBb-gYi6InBvTyNKHA2qVQ" class="text-pink-300 font-medium hover:underline mt-auto">Leer más...</a>
          </div>
        </article>

        <!-- Blog card 3 -->
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
          <div class="p-6 text-left flex flex-col h-full">
            <h3 class="text-xl font-semibold mb-2">Beneficios de tener una niñera en el hogar</h3>
            <p class="text-sm text-gray-600 mb-4 flex-1">
              Hay muchos mitos y realidades sobre el cuidado de los niños ¿Dejarlos al cuidado de una niñera es la mejor opción? Tener un apoyo siempre lo es.
            </p>
            <a href="https://www.goodplayguide.com/parenting-tools-advice/benefits-of-a-childminder/" class="text-pink-300 font-medium hover:underline mt-auto">Leer más...</a>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
