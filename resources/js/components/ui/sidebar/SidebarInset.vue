<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
      'bg-background/50 relative flex w-full flex-1 flex-col',
      'md:peer-data-[variant=inset]:m-5 md:peer-data-[variant=inset]:ml-0 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-0',
      'sticky top-0 h-[94vh] overflow-hidden', 
      props.class,
    )"
  >
    <div class="flex-1 overflow-y-auto px-4 py-6"> 
      <slot />
    </div>
  </main>
</template>

