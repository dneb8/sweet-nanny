<script setup lang="ts">
import { computed } from "vue"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { TagsInput } from "@/components/ui/tags-input"
import { useBoundField } from "@/services/bookingFormService"

const props = defineProps<{
  qualities: Record<string, string>
  degrees: Record<string, string>
  courseNames: Record<string, string>
}>()

// Campos enlazados al form global
const selectedQualities = useBoundField<string[]>("booking.qualities")
const degree = useBoundField<string>("booking.degree")
const selectedCourses = useBoundField<string[]>("booking.courses")

// Convert enums to options for TagsInput
const qualityOptions = computed(() => 
  Object.entries(props.qualities).map(([value, label]) => ({ value, label }))
)

const courseOptions = computed(() =>
  Object.entries(props.courseNames).map(([value, label]) => ({ value, label }))
)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h3 class="text-lg font-semibold mb-4">Cualidades y Formación</h3>
      <p class="text-sm text-muted-foreground mb-4">
        Selecciona las cualidades, formación académica y cursos deseados para la niñera.
      </p>
    </div>

    <!-- Qualities -->
    <!-- <div class="space-y-2">
      <Label class="text-sm font-medium">Cualidades deseadas</Label>
      <TagsInput
        v-model="selectedQualities.value.value"
        :options="qualityOptions"
        placeholder="Escribe o selecciona cualidades..."
      />
      <p v-if="selectedQualities.errorMessage" class="text-xs text-red-500">
        {{ selectedQualities.errorMessage }}
      </p>
    </div> -->

    <!-- Degree / Career -->
    <!-- <div class="space-y-2">
      <Label class="text-sm font-medium">Carrera o formación académica</Label>
      <Select v-model="degree.value.value">
        <SelectTrigger class="w-full">
          <SelectValue placeholder="Selecciona una formación" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="">Sin especificar</SelectItem>
          <SelectItem
            v-for="(label, value) in props.degrees"
            :key="String(value)"
            :value="String(value)"
          >
            {{ label }}
          </SelectItem>
        </SelectContent>
      </Select>
      <p v-if="degree.errorMessage" class="text-xs text-red-500">
        {{ degree.errorMessage }}
      </p>
    </div> -->

    <!-- Courses -->
    <!-- <div class="space-y-2">
      <Label class="text-sm font-medium">Cursos especializados</Label>
      <TagsInput
        v-model="selectedCourses.value.value"
        :options="courseOptions"
        placeholder="Escribe o selecciona cursos..."
      />
      <p v-if="selectedCourses.errorMessage" class="text-xs text-red-500">
        {{ selectedCourses.errorMessage }}
      </p>
    </div> -->
  </div>
</template>
