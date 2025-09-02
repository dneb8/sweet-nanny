<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { ScrollAreaScrollbar, type ScrollAreaScrollbarProps, ScrollAreaThumb } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = withDefaults(defineProps<ScrollAreaScrollbarProps & { class?: HTMLAttributes['class'] }>(), {
  orientation: 'vertical',
})

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
  <ScrollAreaScrollbar
    data-slot="scroll-area-scrollbar"
    v-bind="delegatedProps"
    :class="
      cn(
        'flex touch-none p-px transition-colors select-none',
        orientation === 'vertical' && 'h-full w-2.5 border-l border-l-transparent',
        orientation === 'horizontal' && 'h-2.5 flex-col border-t border-t-transparent',
        props.class
      )"
  >
    <ScrollAreaThumb
      data-slot="scroll-area-thumb"
      class="
        relative flex-1 rounded-full 
        bg-slate-400/40 dark:bg-slate-500/70 
        shadow-md shadow-slate-500/30
        hover:bg-slate-400/70 dark:hover:bg-slate-400 
        transition-colors
      "
    />
  </ScrollAreaScrollbar>
</template>
