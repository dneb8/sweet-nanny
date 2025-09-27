<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>

     <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <!-- <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                            <!- <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" /> --
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link> -->
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-center text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>

  <!-- Layout principal: pantalla completa -->
  <div class="flex flex-col min-h-screen w-full bg-background">
    
    <!-- HEADER: siempre 100% ancho -->
    <header class="w-full bg-[#f4c0b9] shadow-md h-16 flex items-center px-6">
      <h1 class="text-lg font-semibold text-white">{{ title }}</h1>
    </header>

    <!-- CONTENIDO -->
    <main class="flex-1 flex flex-col md:flex-row w-full">
      <!-- SLOT: aquí entra login o register -->
      <section class="flex-1 flex items-center justify-center p-8 md:p-12 lg:p-16">
        <slot />
      </section>
    </main>

 

  </div>
</template>
