<template>
  <section class="py-20 bg-[#FFFAFD] relative overflow-hidden">
    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center">
      <div class="md:w-1/2 mb-8 md:mb-0 relative">
        <!-- decorative blobs -->
        <div
          class="absolute -top-12 -right-10 w-48 h-48 bg-[#E5DBFF] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <div
          class="absolute -bottom-14 -left-14 w-56 h-56 bg-[#FFDCEB] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <img
          src="/images/landing/tennis-nanny-landing.jpg"
          alt="Madre y bebé"
          class="w-full h-80 object-cover rounded-lg shadow-lg relative z-10"
        /> 
      </div>
      <div class="md:w-1/2 md:pl-12">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
          Tranquilidad Total
        </h2>
        <p class="text-gray-700 mb-6">
          Sabemos que es difícil dejar a tus pequeños con alguien nuevo. Nuestro objetivo es
          brindar un cuidado confiable y reconfortante para que puedas relajarte y concentrarte en tu día.
        </p>
        <ul class="space-y-3">
          <li class="flex items-start">
            <span
              class="flex-shrink-0 w-6 h-6 mr-3 rounded-full bg-[#CDE8FF] text-blue-600 flex items-center justify-center"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-4 h-4"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </span>
            <span class="text-gray-700">Únicamente hay niñeras con experiencia previa</span>
          </li>
          <li class="flex items-start">
            <span
              class="flex-shrink-0 w-6 h-6 mr-3 rounded-full bg-[#FFDCEB] text-pink-600 flex items-center justify-center"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-4 h-4"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </span>
            <span class="text-gray-700">Niñeras capacitadas para todo tipo de circunstancias</span>
          </li>
          <li class="flex items-start">
            <span
              class="flex-shrink-0 w-6 h-6 mr-3 rounded-full bg-[#FFF5B2] text-yellow-600 flex items-center justify-center"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="w-4 h-4"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </span>
            <span class="text-gray-700">Actualizaciones y comunicación con la niñera elegida en tiempo real</span>
          </li>
        </ul>
      </div>
    </div>
  </section>
</template>
