<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import type { Course } from '@/types/Course'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import CourseForm from '../forms/CourseForm.vue'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import { getCourseNameLabelByString } from '@/enums/courses/course-name.enum'


const props = defineProps<{ 
  nanny: Nanny 
}>()

// Estado para crear/editar curso
const showModal = ref(false)
const selectedCourse = ref<any>(null)

// Estado para eliminar curso
const showDeleteModal = ref(false)

// Abrir modal para agregar o editar curso
const openModal = (course: Course | null = null) => {
  selectedCourse.value = course
  showModal.value = true;
}

// Abrir modal de eliminar
const openDelete = (course: any) => {
  selectedCourse.value = course
  showDeleteModal.value = true
}

// Función para eliminar al confirmar 
const deleteCourse = () => {
  if (!selectedCourse.value) return
  router.delete(route('courses.destroy', selectedCourse.value.id), {
    onSuccess: () => {
      selectedCourse.value = null
      showDeleteModal.value = false
    },
    onError: (errors) => {
      console.error(errors)
    },
  })
}
</script>

<template>
  <Card class="bg-green-50 dark:bg-green-500/10 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:graduation-cap" /> Cursos
        </div>
        <Button size="sm" variant="outline" @click="openModal()">
          <Icon icon="lucide:plus" /> Nuevo
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent>
      <!-- Lista o sin cursos -->
      <div v-if="props.nanny.courses?.length" class="flex flex-col gap-2">
        <div
          v-for="course in props.nanny.courses"
          :key="course.id"
          class="p-2 rounded shadow-sm border flex justify-between items-center"
        >
          <!-- Info -->
          <div>
            <p class="font-medium">{{ getCourseNameLabelByString(course.name) }}</p>
            <p class="text-sm text-muted-foreground">{{ course.organization }}</p>
            <p class="text-sm text-muted-foreground">{{ course.date }}</p>
          </div>

          <!-- Botones -->
          <div class="flex gap-2">
            <Button size="sm" variant="ghost" @click="openModal(course)">
              <Icon icon="lucide:edit" />
            </Button>
            <Button size="sm" variant="destructive" @click="openDelete(course)">
              <Icon icon="lucide:trash" />
            </Button>
          </div>
        </div>
      </div>

      <div v-else>
        <div class="flex flex-col items-center text-muted-foreground">
          <Icon icon="lucide:book-open" class="w-8 h-8 mb-2" />
          <span>Sin cursos</span>
        </div>
      </div>
    </CardContent>
  </Card>

  <!-- Modales -->
  <FormModal
    v-model="showModal"
    :title="selectedCourse ? 'Editar Curso' : 'Agregar Curso'"
    :form-component="CourseForm"
    :form-props="{
      nanny: nanny,
      course: selectedCourse
    }"
  />

  <DeleteModal
    v-model:show="showDeleteModal"
    title="curso"
    :message="`¿Estás seguro de eliminar el curso ${selectedCourse?.name}?`"
    @confirm="deleteCourse"
  />
</template>
