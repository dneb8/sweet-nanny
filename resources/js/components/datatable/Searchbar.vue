<script setup lang="ts">
import { ref, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import { getQueryStringParam } from './utils.js'
import { Input } from '@/components/ui/input'
import Icon from '@/components/common/Icon.vue'

const props = defineProps<{ searchTermName?: string }>()
const emit = defineEmits<{ (e: 'onSearch', value: string): void }>()

const initialSearch = getQueryStringParam(props.searchTermName)
const searchValue = ref<string | null | undefined>(initialSearch)

const inputRef = ref<any>(null)
const wrapperRef = ref<HTMLElement | null>(null)

function getNativeInput(): HTMLInputElement | null {
  if (inputRef.value instanceof HTMLInputElement) return inputRef.value
  if (inputRef.value?.$el instanceof HTMLInputElement) return inputRef.value.$el as HTMLInputElement
  const fromWrapper = wrapperRef.value?.querySelector('input')
  if (fromWrapper instanceof HTMLInputElement) return fromWrapper
  const fallback = document.getElementById('buscar')
  return fallback instanceof HTMLInputElement ? fallback : null
}

async function focusAndPlaceCursor() {
  await nextTick()
  const el = getNativeInput()
  if (!el) return
  el.focus()
  const len = (searchValue.value?.toString().length ?? 0)
  try { el.setSelectionRange(len, len) } catch {}
}

function handleUpdatedSearch(val: string) {
  searchValue.value = val
  emit('onSearch', val)
}

const offFinish = router.on('finish', () => {
  focusAndPlaceCursor()
})

onMounted(() => {
  focusAndPlaceCursor()
})

onBeforeUnmount(() => {
  offFinish()
})
</script>

<template>
  <div class="relative w-full items-center" ref="wrapperRef">
    <Input
      ref="inputRef"
      id="buscar"
      type="text"
      placeholder="Buscar..."
      class="pl-9"
      :modelValue="searchValue ?? ''"
      v-debounce:700ms="handleUpdatedSearch"
    />
    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-2">
      <Icon name="weui:search-outlined" :size="20" class="text-foreground/30" />
    </span>
  </div>
</template>
