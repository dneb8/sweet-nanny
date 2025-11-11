<template>
  <section class="py-20 bg-[#EAF4FF] relative overflow-hidden">
    <!-- decorative blobs with animation -->
    <div
      class="absolute -top-20 -left-20 w-52 h-52 bg-[#FFDCEB] rounded-full opacity-40 animate-blob"
      aria-hidden="true"
    ></div>
    <div
      class="absolute -bottom-24 -right-24 w-64 h-64 bg-[#E5DBFF] rounded-full opacity-40 animate-blob animation-delay-2000"
      aria-hidden="true"
    ></div>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 text-center mb-12">
        Lo que dicen nuestros clientes
      </h2>
      <div class="grid gap-8 sm:grid-cols-2">
        <!-- Card 1 -->
        <div class="p-8 bg-white rounded-xl shadow-md hover:glow transition-all duration-500">
          <p class="text-gray-700 italic mb-4">
            "Nuestra niñera es maravillosa. Trata a nuestros hijos como si fueran suyos y siempre nos mantiene informados. Siempre la tenemos en nuestros servicios reservados"
          </p>
          <div class="flex items-center">
            <img
              src="/images/landing/madreF-1.jpg"
              alt="Cliente 1"
              class="w-12 h-12 rounded-full object-cover object-center mr-3"
            /> 
            <div>
              <p class="font-semibold">María G.</p>
              <p class="text-sm text-gray-500">Madre de dos</p>
              <p class="text-yellow-400">★★★★★</p>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="p-8 bg-white rounded-xl shadow-md hover:glow transition-all duration-500">
          <p class="text-gray-700 italic mb-4">
            "Contraté a una niñera para una fiesta. El proceso fue muy fácil y la niñera fue excelente. ¡Recomiendo mucho sus servicios!"
          </p>
          <div class="flex items-center">
            <img
              src="/images/landing/hair-nanny-landing.jpg"
              alt="Cliente 2"
              class="w-12 h-12 rounded-full object-cover object-center mr-3"
            />
            <div>
              <p class="font-semibold">José R.</p>
              <p class="text-sm text-gray-500">Profesional </p>
              <p class="text-yellow-400">★★★★☆</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style>
@keyframes blob {
  0%, 100% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(20px, -10px) scale(1.05); }
  66% { transform: translate(-15px, 15px) scale(0.95); }
}

.animate-blob {
  animation: blob 10s infinite ease-in-out;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

/* Glow effect for cards on hover */
.hover\:glow:hover {
  box-shadow: 0 0 20px 5px #f4c2ba;
}
</style>
