<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AuthLayout from "@/layouts/AuthLayout.vue";
import HeaderLanding from '@/components/landing/HeaderLanding.vue'
import HeroSection from '@/components/landing/HeroSection.vue'
import WhatOfferSection from '@/components/landing/WhatOfferSection.vue'
import HowWorksSection from "@/components/landing/HowWorksSection.vue";
import AboutSection from '@/components/landing/AboutSection.vue';
import MeetNannySection from '@/components/landing/MeetNannySection.vue';
import BabysittingPricesSection from "@/components/landing/BabysittingPricesSection.vue";
import FooterLanding from "@/components/landing/FooterLanding.vue";
import BlogSection from "@/components/landing/BlogSection.vue";

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

      <!--BLOGS DE LANDING-->
      <BlogSection />

      <!--BABYSITTINGPRICES DE LANDING-->
      <BabysittingPricesSection />

      <!--FOOTER-->
      <FooterLanding />
      



     
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

    </div>
  </body>
</template>

