<script setup lang="ts">
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { useBoundField } from "@/services/bookingFormService"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Icon } from "@iconify/vue"
import { ref } from "vue"

const props = defineProps<{
  qualities: Record<string, string>
  degrees: Record<string, string>
  courseNames: Record<string, string>
}>()

// Campos enlazados al form global
const selectedQualities = useBoundField<string[]>("booking.qualities")
const degree = useBoundField<string>("booking.degree")
const selectedCourses = useBoundField<string[]>("booking.courses")

// Temporary selection states for adding items
const qualityToAdd = ref<string>("")
const courseToAdd = ref<string>("")

function addQuality() {
  if (!qualityToAdd.value) return
  const current = selectedQualities.value.value || []
  if (!current.includes(qualityToAdd.value)) {
    selectedQualities.value.value = [...current, qualityToAdd.value]
  }
  qualityToAdd.value = ""
}

function removeQuality(quality: string) {
  const current = selectedQualities.value.value || []
  selectedQualities.value.value = current.filter(q => q !== quality)
}

function addCourse() {
  if (!courseToAdd.value) return
  const current = selectedCourses.value.value || []
  if (!current.includes(courseToAdd.value)) {
    selectedCourses.value.value = [...current, courseToAdd.value]
  }
  courseToAdd.value = ""
}

function removeCourse(course: string) {
  const current = selectedCourses.value.value || []
  selectedCourses.value.value = current.filter(c => c !== course)
}
</script>

<template>
  <div class="space-y-6">
    <div>
      <h3 class="text-lg font-semibold mb-4">Cualidades y Formación</h3>
      <p class="text-sm text-muted-foreground mb-4">
        Selecciona las cualidades, grado académico y cursos deseados para la niñera.
      </p>
    </div>

    <!-- Qualities -->
    <div class="space-y-3">
      <Label class="text-sm font-medium">Cualidades deseadas</Label>
      <div class="flex gap-2">
        <Select v-model="qualityToAdd">
          <SelectTrigger class="flex-1">
            <SelectValue placeholder="Selecciona una cualidad" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="(label, value) in props.qualities"
              :key="String(value)"
              :value="String(value)"
            >
              {{ label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Button type="button" @click="addQuality" size="sm" variant="outline">
          <Icon icon="solar:add-circle-linear" class="w-4 h-4" />
        </Button>
      </div>
      
      <div v-if="selectedQualities.value.value && selectedQualities.value.value.length > 0" class="flex flex-wrap gap-2 mt-2">
        <Badge
          v-for="quality in selectedQualities.value.value"
          :key="quality"
          variant="secondary"
          class="flex items-center gap-1 px-3 py-1"
        >
          {{ props.qualities[quality] || quality }}
          <button
            type="button"
            @click="removeQuality(quality)"
            class="ml-1 hover:text-destructive"
          >
            <Icon icon="solar:close-circle-linear" class="w-3 h-3" />
          </button>
        </Badge>
      </div>
      <p v-if="selectedQualities.errorMessage" class="text-xs text-red-500 mt-1">
        {{ selectedQualities.errorMessage }}
      </p>
    </div>

    <!-- Degree -->
    <div class="space-y-3">
      <Label class="text-sm font-medium">Grado académico</Label>
      <Select v-model="degree.value.value">
        <SelectTrigger class="w-full">
          <SelectValue placeholder="Selecciona un grado" />
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
      <p v-if="degree.errorMessage" class="text-xs text-red-500 mt-1">
        {{ degree.errorMessage }}
      </p>
    </div>

    <!-- Courses -->
    <div class="space-y-3">
      <Label class="text-sm font-medium">Cursos especializados</Label>
      <div class="flex gap-2">
        <Select v-model="courseToAdd">
          <SelectTrigger class="flex-1">
            <SelectValue placeholder="Selecciona un curso" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem
              v-for="(label, value) in props.courseNames"
              :key="String(value)"
              :value="String(value)"
            >
              {{ label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Button type="button" @click="addCourse" size="sm" variant="outline">
          <Icon icon="solar:add-circle-linear" class="w-4 h-4" />
        </Button>
      </div>
      
      <div v-if="selectedCourses.value.value && selectedCourses.value.value.length > 0" class="flex flex-wrap gap-2 mt-2">
        <Badge
          v-for="course in selectedCourses.value.value"
          :key="course"
          variant="secondary"
          class="flex items-center gap-1 px-3 py-1"
        >
          {{ props.courseNames[course] || course }}
          <button
            type="button"
            @click="removeCourse(course)"
            class="ml-1 hover:text-destructive"
          >
            <Icon icon="solar:close-circle-linear" class="w-3 h-3" />
          </button>
        </Badge>
      </div>
      <p v-if="selectedCourses.errorMessage" class="text-xs text-red-500 mt-1">
        {{ selectedCourses.errorMessage }}
      </p>
    </div>
  </div>
</template>
