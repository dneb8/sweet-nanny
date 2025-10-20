<script setup lang="ts">
import MultiEnumPicker from '@/components/MultiEnumPicker.vue'
import { useBoundField } from '@/services/bookingFormService'

const props = defineProps<{
  qualities: Record<string,string>
  careers:   Record<string,string>
  courseNames: Record<string,string>
  loading?: boolean
}>()

// useBoundField -> { value, errorMessage }
const fQualities = useBoundField<string[]>('booking.qualities')
const fCareers   = useBoundField<string[]>('booking.careers')   // careers es array
const fCourses   = useBoundField<string[]>('booking.courses')
</script>

<template>
  <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
    <div>
      <MultiEnumPicker
        label="Cualidades deseadas"
        placeholder="Agregar cualidad…"
        :options="props.qualities"
        :model-value="fQualities.value.value ?? []"
        :max="5"
        :loading="props.loading"
        badge-class="bg-purple-200 text-purple-900 dark:text-purple-200 dark:bg-purple-900/60 dark:border-purple-200"
        @update:modelValue="(v: string[]) => (fQualities.value.value = v)"
      />
      <p v-if="fQualities.errorMessage" class="mt-2 text-xs text-rose-600">{{ fQualities.errorMessage }}</p>
    </div>

    <div>
      <MultiEnumPicker
        label="Carreras / formación"
        placeholder="Agregar carrera…"
        :options="props.careers"
        :model-value="fCareers.value.value ?? []"
        :max="5"
        :loading="props.loading"
        badge-class="bg-indigo-200 text-indigo-900 dark:text-indigo-100 dark:bg-indigo-500/40 dark:border-indigo-200"
        @update:modelValue="(v: string[]) => (fCareers.value.value = v)"
      />
      <p v-if="fCareers.errorMessage" class="mt-2 text-xs text-rose-600">{{ fCareers.errorMessage }}</p>
    </div>

    <div>
      <MultiEnumPicker
        label="Cursos especializados"
        placeholder="Agregar curso…"
        :options="props.courseNames"
        :model-value="fCourses.value.value ?? []"
        :max="5"
        :loading="props.loading"
        badge-class="bg-emerald-200 text-emerald-900 dark:text-emerald-100 dark:bg-emerald-900/60 dark:border-emerald-200"
        @update:modelValue="(v: string[]) => (fCourses.value.value = v)"
      />
      <p v-if="fCourses.errorMessage" class="mt-2 text-xs text-rose-600">{{ fCourses.errorMessage }}</p>
    </div>
  </div>
</template>
