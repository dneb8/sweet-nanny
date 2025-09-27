<script setup lang="ts">
import { ref, computed, watch } from "vue"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Button } from "@/components/ui/button"
import { Slider } from "@/components/ui/slider"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Clock } from "lucide-vue-next"

const props = withDefaults(defineProps<{
  modelValue?: string | null,   // "HH:MM" (24h)
  placeholder?: string
}>(), {
  modelValue: null,
  placeholder: "Selecciona hora",
})

const emit = defineEmits<{ "update:modelValue":[val:string] }>()

// Estado interno (siempre en 12h para UI)
const hour12   = ref(12)                 // 1..12
const minute   = ref(0)                  // 0..59
const meridiem = ref<"AM"|"PM">("AM")

// Sincroniza desde modelValue (24h -> 12h)
function syncFromModel(v: string | null | undefined) {
  if (!v) { hour12.value = 12; minute.value = 0; meridiem.value = "AM"; return }
  const [HH, MM] = v.split(":").map(Number)
  minute.value = isNaN(MM) ? 0 : MM
  if (isNaN(HH)) { hour12.value = 12; meridiem.value = "AM"; return }
  if (HH === 0)        { hour12.value = 12; meridiem.value = "AM" }
  else if (HH < 12)    { hour12.value = HH; meridiem.value = "AM" }
  else if (HH === 12)  { hour12.value = 12; meridiem.value = "PM" }
  else                 { hour12.value = HH - 12; meridiem.value = "PM" }
}
watch(() => props.modelValue, syncFromModel, { immediate: true })

// 12h -> 24h (para emitir)
const hh24 = computed(() => {
  if (meridiem.value === "AM") return hour12.value === 12 ? 0 : hour12.value
  return hour12.value === 12 ? 12 : hour12.value + 12
})

// Texto del trigger
const display = computed(() => {
  const hh = String(hour12.value).padStart(2, "0")
  const mm = String(minute.value).padStart(2, "0")
  return `${hh}:${mm} ${meridiem.value}`
})

// Emitir cada cambio interno
watch([hh24, minute, meridiem], () => {
  const out = `${String(hh24.value).padStart(2,"0")}:${String(minute.value).padStart(2,"0")}`
  emit("update:modelValue", out)
})

// Proxys para sliders / tabs
const hourSlider = computed<number[]>({
  get: () => [hour12.value],
  set: a => { hour12.value = Math.min(12, Math.max(1, a[0] ?? 12)) }
})
const minuteSlider = computed<number[]>({
  get: () => [minute.value],
  set: a => { minute.value = Math.min(59, Math.max(0, a[0] ?? 0)) }
})
const meridiemTab = computed<"AM"|"PM">({
  get: () => meridiem.value,
  set: v => { meridiem.value = v }
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="h-9 w-full justify-between bg-white text-foreground border-input hover:bg-white">
        <span class="truncate">
          <span v-if="modelValue" class=" font-medium">{{ display }}</span>
          <span v-else class="text-muted-foreground">{{ placeholder }}</span>
        </span>
        <Clock class="ml-2 h-4 w-4 opacity-60" />
      </Button>
    </PopoverTrigger>

    <PopoverContent align="start" class="w-[260px] p-3 rounded-xl">
      <!-- AM / PM -->
      <div class="mb-3">
        <Tabs v-model="meridiemTab" class="w-full">
          <TabsList class="grid grid-cols-2">
            <TabsTrigger value="AM">AM</TabsTrigger>
            <TabsTrigger value="PM">PM</TabsTrigger>
          </TabsList>
        </Tabs>
      </div>

      <!-- Sliders -->
      <div class="space-y-3">
        <div>
          <div class="flex items-center justify-between text-xs mb-1">
            <span class="text-muted-foreground">Horas</span>
            <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">
              {{ String(hour12).padStart(2,"0") }}
            </span>
          </div>
          <Slider v-model="hourSlider" :min="1" :max="12" :step="1"
                  class="[&>[role=slider]]:bg-rose-600 [&>div]:bg-rose-200" />
        </div>

        <div>
          <div class="flex items-center justify-between text-xs mb-1">
            <span class="text-muted-foreground">Minutos</span>
            <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-700 font-medium">
              {{ String(minute).padStart(2,"0") }}
            </span>
          </div>
          <Slider v-model="minuteSlider" :min="0" :max="59" :step="10"
                  class="[&>[role=slider]]:bg-rose-600 [&>div]:bg-rose-200" />
        </div>
      </div>
    </PopoverContent>
  </Popover>
</template>
