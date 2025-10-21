<script setup lang="ts">
import type { DateValue } from "@internationalized/date"
import type { CalendarRootEmits, CalendarRootProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { getLocalTimeZone, today } from "@internationalized/date"
import { CalendarRoot, useDateFormatter, useForwardPropsEmits } from "reka-ui"
import { createDecade, createYear, toDate } from "reka-ui/date"
import { computed, ref, watch } from "vue"
import { cn } from "@/lib/utils"
import {
  CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody,
  CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading,
} from "@/components/ui/calendar"
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select"

const props = withDefaults(
  defineProps<CalendarRootProps & { class?: HTMLAttributes["class"] }>(),
  {
    modelValue: undefined,
    placeholder() {
      return today(getLocalTimeZone())
    },
    weekdayFormat: "short",
  }
)
const emits = defineEmits<CalendarRootEmits>()

// Reenviamos todo excepto class/placeholder (que manejamos localmente)
const delegatedProps = computed(() => {
  const { class: _c, placeholder: _p, ...delegated } = props
  return delegated
})
const forwarded = useForwardPropsEmits(delegatedProps, emits)

// ⚠️ FIX: placeholder interno independiente del modelValue
const placeholder = ref<DateValue>(
  (props.modelValue as DateValue | undefined)
  ?? props.placeholder
  ?? today(getLocalTimeZone())
)

// Si el modelValue cambia desde fuera, movemos el placeholder al mismo mes/año
watch(
  () => props.modelValue,
  (nv) => {
    if (!nv) return
    // solo sincronizamos mes/año para no romper selección
    placeholder.value = placeholder.value.set({ year: nv.year, month: nv.month })
  },
  { immediate: true }
)

const formatter = useDateFormatter("es")
</script>

<template>
  <CalendarRoot
    v-slot="{ date, grid, weekDays }"
    :model-value="props.modelValue"
    @update:model-value="(v) => emits('update:modelValue', v)"
    v-model:placeholder="placeholder"
    v-bind="forwarded"
    :class="cn('rounded-md border p-3', props.class)"
  >
    <CalendarHeader>
      <CalendarHeading class="flex w-full items-center justify-between gap-2">
        <!-- Mes -->
        <Select
          :default-value="placeholder.month.toString()"
          @update:model-value="(v) => {
            if (!v) return
            const m = Number(v)
            if (m !== placeholder.month) placeholder = placeholder.set({ month: m })
          }"
        >
          <SelectTrigger aria-label="Selecciona mes" class="w-[60%]">
            <SelectValue placeholder="Mes" />
          </SelectTrigger>
          <SelectContent class="max-h-[200px]">
            <SelectItem
              v-for="month in createYear({ dateObj: date })"
              :key="month.toString()"
              :value="month.month.toString()"
            >
              {{ formatter.custom(toDate(month), { month: 'long' }) }}
            </SelectItem>
          </SelectContent>
        </Select>

        <!-- Año -->
        <Select
          :default-value="placeholder.year.toString()"
          @update:model-value="(v) => {
            if (!v) return
            const y = Number(v)
            if (y !== placeholder.year) placeholder = placeholder.set({ year: y })
          }"
        >
          <SelectTrigger aria-label="Selecciona año" class="w-[40%]">
            <SelectValue placeholder="Año" />
          </SelectTrigger>
          <SelectContent class="max-h-[200px]">
            <SelectItem
              v-for="y in createDecade({ dateObj: date, startIndex: -10, endIndex: 10 })"
              :key="y.toString()"
              :value="y.year.toString()"
            >
              {{ y.year }}
            </SelectItem>
          </SelectContent>
        </Select>
      </CalendarHeading>
    </CalendarHeader>

    <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:gap-x-4 sm:gap-y-0">
      <CalendarGrid v-for="month in grid" :key="month.value.toString()">
        <CalendarGridHead>
          <CalendarGridRow>
            <CalendarHeadCell v-for="day in weekDays" :key="day">
              {{ day }}
            </CalendarHeadCell>
          </CalendarGridRow>
        </CalendarGridHead>
        <CalendarGridBody class="grid">
          <CalendarGridRow
            v-for="(weekDates, idx) in month.rows"
            :key="`week-${idx}`"
            class="mt-2 w-full"
          >
            <CalendarCell v-for="d in weekDates" :key="d.toString()" :date="d">
              <CalendarCellTrigger :day="d" :month="month.value" />
            </CalendarCell>
          </CalendarGridRow>
        </CalendarGridBody>
      </CalendarGrid>
    </div>
  </CalendarRoot>
</template>
