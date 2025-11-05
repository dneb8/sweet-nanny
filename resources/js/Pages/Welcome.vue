<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AuthLayout from "@/layouts/AuthLayout.vue";
import HeaderLanding from '@/components/landing/HeaderLanding.vue'
import HeroSection from '@/components/landing/HeroSection.vue'
import WhatOfferSection from '@/components/landing/WhatOfferSection.vue'
import HowWorksSection from "@/components/landing/HowWorksSection.vue";
import AboutSection from '@/components/landing/AboutSection.vue';
import MeetNannySection from '@/components/landing/MeetNannySection.vue';

defineOptions({
  layout: false,
});
</script>

<template>
  <Head title="Bienvenidos">
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

   <body >
    <div class="relative overflow-x-hidden">

      <!--HEADER DE LANDING-->
      <HeaderLanding />
      <!--HOME DE LANDING-->
      <HeroSection />
      <!--WHATOFFER DE LANDING-->
      <WhatOfferSection />
      <!--HOWWORKS DE LANDING-->
      <HowWorksSection />
      <!--ABOUT DE LANDING-->
      <AboutSection />
      <!--MEET NANNYS DE LANDING-->
      <MeetNannySection />
      



     
      <!-- Evening Babysitting CTA -->
      <!--
        Lighten the evening section with a delicate pink (#FFF0F8) similar to the
        reference site.  The previous pink-100 class was a bit too stark.
      -->
      <section class="py-20 bg-[#FFF0F8] relative overflow-hidden">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between">
          <div class="md:w-1/2 mb-8 md:mb-0">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
              Evening Babysitting
            </h2>
            <p class="text-gray-700 mb-6">
              Need a break? Plan a night out while our trusted babysitters look after your
              children. We provide flexible evening care to suit your schedule.
            </p>
            <a
              href="#contact"
              class="inline-block px-6 py-3 bg-[#FFBC5B] text-[#2A3786] font-medium rounded-full shadow-md hover:bg-[#FFAA3A] transition"
              >Learn More</a
            >
          </div>
          <div class="md:w-1/2 flex justify-center relative">
            <!-- decorative blob -->
            <div
              class="absolute -top-12 -left-16 w-56 h-56 bg-[#CDE8FF] rounded-full opacity-40"
              aria-hidden="true"
            ></div>
            <!-- <img
              src="./ai_evening.png"
              alt="Evening babysitting illustration"
              class="w-80 h-60 rounded-lg object-cover shadow-lg relative z-10"
            /> -->
          </div>
        </div>
      </section>

      <!-- Total Peace of Mind Section -->
      <!--
        Keep a light background but introduce a whisper of pink (#FFFAFD) to warm the
        section and harmonise with adjacent panels.
      -->
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
            <!-- <img
              src="./ai_hero.png"
              alt="Mother and baby"
              class="w-full h-80 object-cover rounded-lg shadow-lg relative z-10"
            /> -->
          </div>
          <div class="md:w-1/2 md:pl-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
              Total Peace of Mind
            </h2>
            <p class="text-gray-700 mb-6">
              We know it’s hard to leave your little ones with someone new. Our goal is to
              provide reassuring, trustworthy care so you can relax and focus on your day.
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
                <span class="text-gray-700">Background‑checked caregivers</span>
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
                <span class="text-gray-700">First‑aid and CPR trained staff</span>
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
                <span class="text-gray-700">Real‑time updates and messaging</span>
              </li>
            </ul>
          </div>
        </div>
      </section>

      
      <!-- Testimonials Section -->
      <!--
        A pale powder-blue (#EAF4FF) sets off the testimonial cards.  This matches
        the blog section below and creates consistent banding across the page.
      -->
      <section class="py-20 bg-[#EAF4FF] relative overflow-hidden">
        <!-- decorative blob -->
        <div
          class="absolute -top-20 -left-20 w-52 h-52 bg-[#FFDCEB] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <div
          class="absolute -bottom-24 -right-24 w-64 h-64 bg-[#E5DBFF] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 text-center mb-12">
            What Clients Say
          </h2>
          <div class="grid gap-8 sm:grid-cols-2">
            <div class="p-8 bg-white rounded-xl shadow-md">
              <p class="text-gray-700 italic mb-4">
                "Our nanny from Happy Baby is wonderful. She treats our children like her own and
                always keeps us updated. We never worry when she’s here!"
              </p>
              <div class="flex items-center">
                <!-- <img
                  src="./ai_hero.png"
                  alt="Client 1"
                  class="w-12 h-12 rounded-full object-cover object-center mr-3"
                /> -->
                <div>
                  <p class="font-semibold">Sarah K.</p>
                  <p class="text-sm text-gray-500">Mother of two</p>
                </div>
              </div>
            </div>
            <div class="p-8 bg-white rounded-xl shadow-md">
              <p class="text-gray-700 italic mb-4">
                "I booked an evening sitter through Happy Baby. The process was easy and the sitter
                was excellent. Highly recommend their services!"
              </p>
              <div class="flex items-center">
                <!-- <img
                  src="./ai_childcare.png"
                  alt="Client 2"
                  class="w-12 h-12 rounded-full object-cover object-center mr-3"
                /> -->
                <div>
                  <p class="font-semibold">David L.</p>
                  <p class="text-sm text-gray-500">Busy professional</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Rates Section -->
      <!--
        Match the dark pricing panel to the original's rich indigo.  We reuse the same
        custom colour as the “How It Works” section (#2A3786) for consistency.
      -->
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
          <h2 class="text-3xl sm:text-4xl font-extrabold mb-12">Babysitting Rates</h2>
          <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white/10 p-8 rounded-xl flex flex-col">
              <h3 class="text-xl font-semibold mb-2">Hourly</h3>
              <p class="text-4xl font-extrabold mb-4">$25</p>
              <p class="flex-grow text-sm">
                Ideal for short errands or an evening out. Minimum 3 hours per booking.
              </p>
              <button
                class="mt-6 inline-block px-6 py-3 bg-[#FFBC5B] text-[#2A3786] font-medium rounded-full hover:bg-[#FFAA3A] transition"
              >Book Now</button>
            </div>
            <div class="bg-white/10 p-8 rounded-xl flex flex-col">
              <h3 class="text-xl font-semibold mb-2">Half Day</h3>
              <p class="text-4xl font-extrabold mb-4">$90</p>
              <p class="flex-grow text-sm">
                Four hours of dedicated care. Perfect for a busy morning or afternoon.
              </p>
              <button
                class="mt-6 inline-block px-6 py-3 bg-[#FFBC5B] text-[#2A3786] font-medium rounded-full hover:bg-[#FFAA3A] transition"
              >Book Now</button>
            </div>
            <div class="bg-white/10 p-8 rounded-xl flex flex-col">
              <h3 class="text-xl font-semibold mb-2">Full Day</h3>
              <p class="text-4xl font-extrabold mb-4">$160</p>
              <p class="flex-grow text-sm">
                Up to eight hours of care including meals, playtime and learning activities.
              </p>
              <button
                class="mt-6 inline-block px-6 py-3 bg-[#FFBC5B] text-[#2A3786] font-medium rounded-full hover:bg-[#FFAA3A] transition"
              >Book Now</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Baby Shower Party Section -->
      <!--
        Lighten the baby‑shower section using the same delicate pink as the evening CTA (#FFF0F8).
      -->
      <section class="py-20 bg-[#FFF0F8] relative overflow-hidden">
        <!-- decorative blob -->
        <div
          class="absolute -top-16 -left-20 w-56 h-56 bg-[#CDE8FF] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <div
          class="absolute -bottom-20 -right-24 w-64 h-64 bg-[#E5DBFF] rounded-full opacity-40"
          aria-hidden="true"
        ></div>
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center relative z-10">
          <div class="md:w-1/2 mb-8 md:mb-0">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
              Baby Shower Party
            </h2>
            <p class="text-gray-700 mb-6">
              Celebrate your bundle of joy with a memorable shower planned by our team. We provide
              decorations, games and delicious treats.
            </p>
            <a
              href="#contact"
              class="inline-block px-6 py-3 bg-[#FFBC5B] text-[#2A3786] font-medium rounded-full shadow-md hover:bg-[#FFAA3A] transition"
              >Start Planning</a
            >
          </div>
          <div class="md:w-1/2 flex justify-center">
            <!-- <img
              src="./ai_baby_shower.png"
              alt="Baby shower party illustration"
              class="w-80 h-60 rounded-lg object-cover shadow-lg"
            /> -->
          </div>
        </div>
      </section>

      <!-- Mobile App Section -->
      <!--
        Introduce a very light pink tint (#FFFAFD) behind the mobile app content to avoid
        harsh transitions between sections.
      -->
      <section class="py-20 bg-[#FFFAFD] relative overflow-hidden">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center">
          <div class="md:w-1/2 mb-8 md:mb-0 flex justify-center relative">
            <!-- decorative blob -->
            <div
              class="absolute -top-16 -right-12 w-48 h-48 bg-[#FFDCEB] rounded-full opacity-40"
              aria-hidden="true"
            ></div>
            <!-- <img
              src="./ai_mobile_app.png"
              alt="Mobile application illustration"
              class="w-40 h-80 object-cover rounded-lg shadow-xl relative z-10"
            /> -->
          </div>
          <div class="md:w-1/2 md:pl-12">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-4">
              Check Our Mobile App
            </h2>
            <p class="text-gray-700 mb-6">
              Book sitters, track your schedule and get real‑time updates – all from the palm
              of your hand.
            </p>
            <ul class="space-y-3 mb-6">
              <li class="flex items-start">
                <span
                  class="flex-shrink-0 w-6 h-6 mr-3 rounded-full bg-[#E5DBFF] text-purple-600 flex items-center justify-center"
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
                <span class="text-gray-700">Book sitters on the go</span>
              </li>
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
                <span class="text-gray-700">Track bookings and payments</span>
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
                <span class="text-gray-700">Receive real‑time updates</span>
              </li>
            </ul>
            <div class="flex space-x-4">
              <a
                href="#"
                class="inline-block px-6 py-3 bg-[#F76C6C] text-white font-medium rounded-full shadow-md hover:bg-[#F55A79] transition"
                >App Store</a
              >
              <a
                href="#"
                class="inline-block px-6 py-3 bg-[#2A3786] text-white font-medium rounded-full shadow-md hover:bg-[#1E2F72] transition"
                >Google Play</a
              >
            </div>
          </div>
        </div>
      </section>

      <!-- Blog Section -->
      <section id="blog" class="py-20 bg-[#EAF4FF]">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800 mb-12">
            From the Blog
          </h2>
          <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Blog card 1 -->
            <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
              <!-- <img
                src="./ai_childcare.png"
                alt="How to Choose the Perfect Nanny"
                class="w-full h-40 object-cover object-center"
              /> -->
              <div class="p-6 text-left">
                <h3 class="text-xl font-semibold mb-2">How to Choose the Perfect Nanny</h3>
                <p class="text-sm text-gray-600 mb-4">
                  Learn what qualities to look for and questions to ask when interviewing a nanny.
                </p>
                <a href="#" class="text-pink-500 font-medium hover:underline">Read More</a>
              </div>
            </article>
            <!-- Blog card 2 -->
            <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
              <!-- <img
                src="./ai_evening.png"
                alt="Fun Indoor Activities for Rainy Days"
                class="w-full h-40 object-cover object-center"
              /> -->
              <div class="p-6 text-left">
                <h3 class="text-xl font-semibold mb-2">Fun Indoor Activities for Rainy Days</h3>
                <p class="text-sm text-gray-600 mb-4">
                  Keep your children entertained and engaged with these creative indoor games.
                </p>
                <a href="#" class="text-pink-500 font-medium hover:underline">Read More</a>
              </div>
            </article>
            <!-- Blog card 3 -->
            <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
              <!-- <img
                src="./ai_hero.png"
                alt="Why Routine Matters in Childcare"
                class="w-full h-40 object-cover object-center"
              /> -->
              <div class="p-6 text-left">
                <h3 class="text-xl font-semibold mb-2">Why Routine Matters in Childcare</h3>
                <p class="text-sm text-gray-600 mb-4">
                  Discover how consistent routines contribute to a child’s sense of security.
                </p>
                <a href="#" class="text-pink-500 font-medium hover:underline">Read More</a>
              </div>
            </article>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <!--
        A deep navy footer (#1E2F72) closer to the original colour palette replaces the
        default purple‑800.  Text colours remain light for contrast.
      -->
      <footer id="contact" class="pt-20 pb-12 bg-[#1E2F72] text-white">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col md:flex-row justify-between mb-12">
            <div class="mb-8 md:mb-0">
              <h3 class="text-2xl font-extrabold mb-3">We Provide Best Nanny Service</h3>
              <p class="text-sm text-purple-200 max-w-xs">
                Trust Happy Baby for reliable childcare when you need it most. Let us take care of
                your family so you can take care of yourself.
              </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
              <div>
                <h4 class="text-lg font-semibold mb-3">Contact</h4>
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
                    123 Main Street, Dallas, TX
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
                    (214) 555‑1234
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
                    info@happybaby.com
                  </li>
                </ul>
              </div>
              <div>
                <h4 class="text-lg font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-sm text-purple-200">
                  <li><a href="#services" class="hover:text-white">Services</a></li>
                  <li><a href="#about" class="hover:text-white">About</a></li>
                  <li><a href="#rates" class="hover:text-white">Rates</a></li>
                  <li><a href="#blog" class="hover:text-white">Blog</a></li>
                </ul>
              </div>
              <div>
                <h4 class="text-lg font-semibold mb-3">Follow Us</h4>
                <div class="flex space-x-4 text-purple-200">
                  <a href="#" class="hover:text-white">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                      class="w-5 h-5"
                    >
                      <path
                        d="M24 4.56c-.88.39-1.83.65-2.82.77a4.93 4.93 0 002.17-2.72 9.86 9.86 0 01-3.12 1.19 4.92 4.92 0 00-8.39 4.49A13.94 13.94 0 011.67 3.15a4.92 4.92 0 001.52 6.56 4.9 4.9 0 01-2.23-.62v.06a4.93 4.93 0 003.95 4.83 4.91 4.91 0 01-2.22.08 4.93 4.93 0 004.6 3.42A9.87 9.87 0 010 19.54a13.93 13.93 0 007.55 2.21c9.06 0 14.02-7.5 14.02-14.01 0-.21 0-.42-.02-.63A10 10 0 0024 4.56z"
                      />
                    </svg>
                  </a>
                  <a href="#" class="hover:text-white">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                      class="w-5 h-5"
                    >
                      <path
                        d="M12 2.04c-5.52 0-10 4.48-10 10 0 4.72 3.24 8.67 7.56 9.84.55.1.75-.24.75-.53 0-.26-.01-1.07-.01-1.93-3.07.67-3.74-1.48-3.74-1.48-.5-1.26-1.22-1.59-1.22-1.59-.99-.68.08-.67.08-.67 1.1.08 1.68 1.13 1.68 1.13.98 1.68 2.58 1.19 3.22.91.1-.71.38-1.19.69-1.46-2.45-.28-5.02-1.22-5.02-5.43 0-1.2.43-2.18 1.13-2.95-.11-.28-.49-1.41.11-2.95 0 0 .93-.3 3.05 1.13A10.47 10.47 0 0112 6.8c.94.004 1.89.13 2.78.38 2.12-1.43 3.05-1.13 3.05-1.13.6 1.54.22 2.67.11 2.95.7.77 1.13 1.75 1.13 2.95 0 4.21-2.57 5.15-5.02 5.42.39.33.74.98.74 1.97 0 1.42-.01 2.56-.01 2.91 0 .29.2.64.76.53A10.02 10.02 0 0022 12.04c0-5.52-4.48-10-10-10z"
                      />
                    </svg>
                  </a>
                  <a href="#" class="hover:text-white">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      fill="currentColor"
                      viewBox="0 0 24 24"
                      class="w-5 h-5"
                    >
                      <path
                        d="M12 2.04c-5.52 0-10 4.48-10 10 0 4.72 3.24 8.67 7.56 9.84.55.1.75-.24.75-.53 0-.26-.01-1.07-.01-1.93-3.07.67-3.74-1.48-3.74-1.48-.5-1.26-1.22-1.59-1.22-1.59-.99-.68.08-.67.08-.67 1.1.08 1.68 1.13 1.68 1.13.98 1.68 2.58 1.19 3.22.91.1-.71.38-1.19.69-1.46-2.45-.28-5.02-1.22-5.02-5.43 0-1.2.43-2.18 1.13-2.95-.11-.28-.49-1.41.11-2.95 0 0 .93-.3 3.05 1.13A10.47 10.47 0 0112 6.8c.94.004 1.89.13 2.78.38 2.12-1.43 3.05-1.13 3.05-1.13.6 1.54.22 2.67.11 2.95.7.77 1.13 1.75 1.13 2.95 0 4.21-2.57 5.15-5.02 5.42.39.33.74.98.74 1.97 0 1.42-.01 2.56-.01 2.91 0 .29.2.64.76.53A10.02 10.02 0 0022 12.04c0-5.52-4.48-10-10-10z"
                      />
                    </svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <p class="text-center text-purple-300 text-sm mt-12">
            © 2025 Happy Baby. All rights reserved.
          </p>
        </div>
      </footer>
    </div>
  </body>
</template>

