<template>

      <footer id="contact" class="pt-20 pb-12 bg-[#1E2F72] text-white">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col md:flex-row justify-between mb-12">
            <div class="mb-8 md:mb-0">
              <h3 class="text-2xl font-extrabold mb-3">Los mejores servicios de niñera solo aquí en <span class="text-[#f4c2ba] font-semibold"> SweetNanny</span></h3>
              <p class="text-sm text-purple-200 max-w-xs">
                La seguridad, confianza, responsabilidad y la facilidad de nuestros servicios 
                son completamente 
              </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
              <div>
                <h4 class="text-lg font-semibold mb-3">Contáctanos</h4>
                <ul class="space-y-2 text-sm text-purple-200">
                  <li class="flex items-start">
                    <!-- Map pin icon -->
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="w-5 h-5 mr-2 text-pink-300"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 2.25c4.97 0 9 3.76 9 8.4 0 5.11-4.8 9.52-8.4 11.52a1.3 1.3 0 01-1.2 0C7.8 20.17 3 15.76 3 10.65c0-4.64 4.03-8.4 9-8.4z"
                      />
                      <circle cx="12" cy="10.5" r="2.5" />
                    </svg>
                    Guadalajara, Jalisco, México
                  </li>
                  <li class="flex items-start">
                    <!-- Phone icon -->
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="w-5 h-5 mr-2 text-pink-300"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2 6.5C2 5.12 3.12 4 4.5 4h15c1.38 0 2.5 1.12 2.5 2.5V17.5c0 1.38-1.12 2.5-2.5 2.5h-15C3.12 20 2 18.88 2 17.5V6.5z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 8h.01M6 12h.01M6 16h.01M10 16h8M10 12h8M10 8h8"
                      />
                    </svg>
                    +52 33 3175 8808
                  </li>
                  <li class="flex items-start">
                    <!-- Mail icon -->
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke-width="1.5"
                      stroke="currentColor"
                      class="w-5 h-5 mr-2 text-pink-300"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 6.75l9.735 6.49a.75.75 0 00.83 0l9.735-6.49M3.75 5.25h16.5c.967 0 1.75.783 1.75 1.75v10.5c0 .967-.783 1.75-1.75 1.75H3.75A1.75 1.75 0 012 17.5V7c0-.967.783-1.75 1.75-1.75z"
                      />
                    </svg>
                    SweetnannyMail@gmail.com
                  </li>
                </ul>
              </div>
              <div>
                <h4 class="text-lg font-semibold mb-3">Links rápidos</h4>
                <ul class="space-y-2 text-sm text-purple-200">
                  <li><a href="#services" class="hover:text-white">Nuestros servicio</a></li>
                  <li><a href="#about" class="hover:text-white">Sobre nosotras</a></li>
                  <li><a href="#rates" class="hover:text-white">Precios</a></li>
                  <li><a href="#blog" class="hover:text-white">Blogs</a></li>
                </ul>
              </div>
              <div>
                <h4 class="text-lg font-semibold mb-3">Síguenos</h4>
                <div class="flex space-x-4 text-purple-200">
                <a href="https://www.instagram.com/sweet_nanny10/?hl=es" class="hover:text-white">
                    <!-- Instagram Icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-6 h-6">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.334 3.608 1.31.975.975 1.247 2.242 1.31 3.608.058 1.266.069 1.646.069 4.849s-.012 3.584-.07 4.85c-.062 1.366-.334 2.633-1.31 3.608-.975.975-2.242 1.247-3.608 1.31-1.266.058-1.646.069-4.849.069s-3.584-.012-4.85-.07c-1.366-.062-2.633-.334-3.608-1.31-.975-.975-1.247-2.242-1.31-3.608-.058-1.266-.069-1.646-.069-4.849s.012-3.584.07-4.85c.062-1.366.334-2.633 1.31-3.608.975-.975 2.242-1.247 3.608-1.31 1.266-.058 1.646-.069 4.849-.069zm0-2.163C8.737 0 8.332.012 7.052.07 5.78.128 4.605.428 3.635 1.398 2.666 2.367 2.366 3.542 2.308 4.814.012 8.332 0 8.737 0 12c0 3.263.012 3.668.07 4.948.058 1.272.358 2.447 1.328 3.416.969.969 2.144 1.269 3.416 1.328 1.28.058 1.685.07 4.948.07s3.668-.012 4.948-.07c1.272-.058 2.447-.358 3.416-1.328.969-.969 1.269-2.144 1.328-3.416.058-1.28.07-1.685.07-4.948s-.012-3.668-.07-4.948c-.058-1.272-.358-2.447-1.328-3.416-.969-.969-2.144-1.269-3.416-1.328C15.668.012 15.263 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a3.999 3.999 0 110-7.998 3.999 3.999 0 010 7.998zm6.406-11.845a1.44 1.44 0 11-2.88 0 1.44 1.44 0 012.88 0z"/>
                    </svg>
                </a>
                </div>


              </div>
            </div>
          </div>
          <p class="text-center text-purple-300 text-sm mt-12">
            © 2025 SweetNanny todos los derechos reservados. 
          </p>
        </div>
      </footer>
</template>