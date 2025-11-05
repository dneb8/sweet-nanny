<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import HeaderLanding from '@/components/landing/HeaderLanding.vue'
import HeroSection from '@/components/landing/HeroSection.vue'
import WhatOfferSection from '@/components/landing/WhatOfferSection.vue'
import HowWorksSection from "@/components/landing/HowWorksSection.vue";
import AboutSection from '@/components/landing/AboutSection.vue';
import MeetNannySection from '@/components/landing/MeetNannySection.vue';
import BabysittingPricesSection from "@/components/landing/BabysittingPricesSection.vue";
import FooterLanding from "@/components/landing/FooterLanding.vue";
import BlogSection from "@/components/landing/BlogSection.vue";
import BabyPartiSection from "@/components/landing/BabyPartiSection.vue";
import TestimonialsSection from "../components/landing/TestimonialsSection.vue";
import EveningSection from "@/components/landing/EveningSection.vue";
import RelaxSection from "@/components/landing/RelaxSection.vue";

defineOptions({
  layout: false,
});
</script>

<template>
  <Head title="Bienvenidos">
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

      <!--BABYPARTI DE LANDING-->
      <BabyPartiSection />

      <!--BABYSITTINGPRICES DE LANDING-->
      <BabysittingPricesSection />

      <!--RELAX DE LANDING-->
      <RelaxSection /> 

      <!--BLOGS DE LANDING-->
      <BlogSection />

      <!--TESTIMONIALS DE LANDING-->
      <TestimonialsSection />

      <!--EVENING DE LANDING-->
      <EveningSection />

      <!--FOOTER-->
      <FooterLanding />
    </div>
  </body>
</template>

