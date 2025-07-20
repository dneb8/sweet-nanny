<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { ScrollArea} from '@/components/ui/scroll-area'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <main
    data-slot="sidebar-inset"
    :class="cn(
      'bg-background/70 relative flex w-full flex-1 flex-col',
      'md:peer-data-[variant=inset]:my-auto md:peer-data-[variant=inset]:mr-5 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-sm md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-0',
      'md:h-[95vh] overflow-hidden', 
      props.class,
    )"
  >
<ScrollArea class="rounded-md overflow-y-auto">
  <slot />
</ScrollArea>
  </main>
</template>

