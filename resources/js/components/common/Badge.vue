<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  label?: string
  icon?: string
  customClass?: string
  /** Tamaño base del chip/ícono cuando es circular (px) */
  size?: number
  /** Texto accesible cuando no hay label visible */
  ariaLabel?: string
}>(), {
  size: 28,
  ariaLabel: 'icono',
})

// ¿Hay texto?
const hasLabel = computed(() => !!props.label && props.label.trim().length > 0)

// Clases dinámicas:
// - Con label: pill con padding horizontal
// - Sin label (solo ícono): círculo perfecto con ancho/alto iguales
const rootClasses = computed(() => {
  const base =
    'text-xs font-semibold cursor-default dark:border flex items-center justify-center w-fit'
  const withLabel =
    'rounded-full p-1 px-2 gap-1'
  const onlyIcon =
    'rounded-full' // padding lo controlamos con size para mantener el círculo

  return [
    base,
    hasLabel.value ? withLabel : onlyIcon,
    props.customClass,
  ]
})

// Estilo inline para el modo circular (solo icono)
const circleStyle = computed(() => {
  if (hasLabel.value) return {}
  const s = `${props.size}px`
  return {
    width: s,
    height: s,
  }
})

// Tamaño del ícono: un pelín menor que el contenedor
const iconClass = computed(() => {
  if (hasLabel.value) return 'min-w-[1.2em] min-h-[1.2em] text-base'
  // cuando es circular, ajustamos al contenedor
  return 'w-[60%] h-[60%]'
})
</script>

<template>
  <div
    :class="rootClasses"
    :style="circleStyle"
    :aria-label="!hasLabel ? ariaLabel : undefined"
    role="status"
  >
    <Icon
      v-if="icon"
      :icon="icon"
      :class="iconClass"
    />
    <span v-if="hasLabel">{{ label }}</span>
  </div>
</template>
