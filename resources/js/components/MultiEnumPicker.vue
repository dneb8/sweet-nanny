<script setup lang="ts">
import { ref, computed } from 'vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Icon } from '@iconify/vue'

const props = withDefaults(defineProps<{
  label?: string
  placeholder?: string
  options: Record<string, string>   // { value: label }
  modelValue: string[]              // array seleccionado
  max?: number
  loading?: boolean
  badgeClass?: string               // clases tailwind para colorear los chips
}>(), {
  placeholder: 'Agregar…',
  max: 5,
  loading: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void
}>()

const open  = ref(false)
const query = ref('')

// memo de opciones
const entries = computed<[string,string][]>(() => Object.entries(props.options ?? {}))
// selección actual (plana)
const selected = computed(() => props.modelValue ?? [])
const selectedSet = computed(() => new Set(selected.value))

const atMax = computed(() => selected.value.length >= (props.max ?? 5))
const triggerText = computed(() => atMax.value ? `Máximo ${props.max} alcanzado` : (props.placeholder ?? 'Agregar…'))

const filtered = computed(() => {
  const q = query.value.toLowerCase().trim()
  const base = q ? entries.value.filter(([,label]) => (label ?? '').toLowerCase().includes(q)) : entries.value
  return base.filter(([val]) => !selectedSet.value.has(val))
})

function add(val: string) {
  if (!val || atMax.value || selectedSet.value.has(val)) return
  emit('update:modelValue', [...selected.value, val])
  // cerrar popover para evitar flicker/loops de Presence/ScrollArea
  open.value = false
  // limpiar búsqueda para siguiente apertura
  query.value = ''
}

function remove(val: string) {
  emit('update:modelValue', selected.value.filter(v => v !== val))
}
</script>

<template>
  <div class="space-y-2">
    <div v-if="label" class="text-sm font-medium">{{ label }}</div>

    <!-- Tags seleccionadas -->
    <div class="flex flex-wrap gap-2">
      <Badge v-for="val in selected" :key="val" variant="secondary" :class="['gap-1', props.badgeClass]">
        {{ options[val] ?? val }}
        <button
          type="button"
          class="ml-1 inline-flex items-center justify-center rounded hover:text-foreground/80"
          title="Quitar"
          aria-label="Quitar"
          :disabled="loading"
          @click="remove(val)"
        >
          <Icon icon="lucide:x" class="h-3.5 w-3.5" />
        </button>
      </Badge>
      <span v-if="!selected.length" class="text-xs text-muted-foreground">Sin elementos</span>
    </div>

    <!-- Combobox -->
    <Popover v-model:open="open">
      <PopoverTrigger as-child>
        <Button variant="outline" size="sm" class="h-8" :disabled="atMax || loading">
          <Icon icon="lucide:plus" class="mr-1 h-3.5 w-3.5" />
          {{ triggerText }}
        </Button>
      </PopoverTrigger>

      <PopoverContent class="w-64 p-0">
        <Command>
          <CommandInput v-model="query" placeholder="Buscar…" />
          <CommandEmpty>Sin resultados</CommandEmpty>
          <CommandList>
            <CommandGroup>
              <CommandItem
                v-for="[val, label] in filtered"
                :key="val"
                :value="val"
                @select="(e: CustomEvent<{ value: string }>) => add(String(e.detail?.value || val))"
              >
                <Icon :icon="selectedSet.has(val) ? 'lucide:check' : 'lucide:plus'" class="mr-2 h-4 w-4" />
                <span class="truncate">{{ label }}</span>
              </CommandItem>
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>

    <p v-if="atMax" class="text-xs text-muted-foreground">Has alcanzado el máximo de {{ max }} elementos.</p>
  </div>
</template>
