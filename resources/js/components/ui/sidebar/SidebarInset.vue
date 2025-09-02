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
      'bg-background/60 relative flex w-full flex-1 flex-col',
      'md:peer-data-[variant=inset]:my-auto md:peer-data-[variant=inset]:mr-5 md:peer-data-[variant=inset]:rounded-xl md:peer-data-[variant=inset]:shadow-[4px_0_6px_-1px_rgba(0,0,0,0.1),_2px_0_4px_-1px_rgba(0,0,0,0.06)] md:peer-data-[variant=inset]:peer-data-[state=collapsed]:ml-0',
      'md:h-[95vh] h-full', 
      props.class,
    )"
  >
     <!-- Móvil: sin scroll -->
    <div class="block md:hidden">
      <slot />
    </div>

    <!-- md o mayor: ScrollArea -->
    <ScrollArea class="hidden md:block md:overflow-y-auto rounded-md">
      <slot />
    </ScrollArea>

  </main>
</template>

