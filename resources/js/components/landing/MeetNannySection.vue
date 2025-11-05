<template>
  <!-- Meet Your Babysitter Section -->
  <section class="py-20 bg-white relative overflow-hidden">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      
      <!-- Botón encima del título -->
      <div class="flex justify-center mb-4 relative z-20">
        <span
          class="relative px-8 py-3 text-base font-semibold mb-2 text-white uppercase tracking-widest"
          style="background-color: #f4c2ba; border-radius: 1rem; box-shadow: 0 1px 10px rgba(0,0,0,0.3);"
        >
          Niñeras destacadas
        </span>
      </div>
      <!-- decorative blobs -->
      <div
        class="absolute -top-10 -left-12 w-52 h-52 bg-[#CDE8FF] rounded-full opacity-40 z-10"
        aria-hidden="true"
      ></div>
      <div
        class="absolute -bottom-20 -right-20 w-64 h-64 bg-[#E5DBFF] rounded-full opacity-40"
        aria-hidden="true"
      ></div>

      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 relative z-10">
        <!-- Babysitter card -->
        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-lg hover:shadow-yellow-300/50 transition relative group overflow-hidden">
          <!-- Imagen -->
        <div class="relative w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
        <img
            src="/images/landing/nannyL-1.jpg"
            alt="Childcare illustration"
            class="w-full h-full object-contain rounded-full"
        />
        </div>


          <h3 class="text-xl font-semibold mb-1">Lilia del Carmen González</h3>
          <p class="text-sm text-gray-500 mb-2">
            Destacada por la promoción de matemáticas divertidas, amable, paciente, fan de caricaturas &amp; siempre puntual
          </p>
          <p class="text-sm text-gray-500 italic mb-2">Guadalajara, 24 años</p>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-lg hover:shadow-yellow-300/50 transition relative group overflow-hidden">
        <div class="relative w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
        <img
            src="/images/landing/nannyA-1.jpg"
            alt="Childcare illustration"
            class="w-full h-full object-contain rounded-full"
        />
        </div>

          <h3 class="text-xl font-semibold mb-1">Alexia Patricia García</h3>
          <p class="text-sm text-gray-500 mb-2">
            Top 1 de niñeras destacadas, la mejor para el cuidado de bebés paciente, divertida &amp; muy responsable
          </p>
          <p class="text-sm text-gray-500 italic mb-2">Zapopan, 22 años</p>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-lg hover:shadow-yellow-300/50 transition relative group overflow-hidden">
        <div class="relative w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
        <img
            src="/images/landing/nannyD-1.jpg"
            alt="Childcare illustration"
            class="w-full h-full object-contain rounded-full"
        />
        </div>

          <h3 class="text-xl font-semibold mb-1">Deneb Rivera</h3>
          <p class="text-sm text-gray-500 mb-2">
            Muy disciplinada, siempre destacada por cuidar a niños de entre 10 a 13 años promueve danza &amp; actividades creativas
          </p>
          <p class="text-sm text-gray-500 italic mb-2">Guadalajara, 22 años</p>
        </div>
      </div>
    </div>
  </section>
</template>
