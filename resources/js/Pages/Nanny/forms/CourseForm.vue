<script setup lang="ts">
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from "@/components/ui/select"
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { watch } from "vue"
import { CourseFormService } from "@/services/courseFormService"
import type { Nanny } from "@/types/Nanny"
import type { Course } from "@/types/Course"
import { COURSE_NAME_OPTIONS } from "@/enums/courses/course-name.enum"

const props = defineProps<{
  course?: Course;
  nanny?: Nanny;
}>()

const emit = defineEmits(["saved"])

const formService = new CourseFormService(
  props.course || {
    nanny_id: props.nanny?.id,
    name: "",
    organization: "",
    date: "",
  }
)

const { errors, loading, saved } = formService

watch(() => saved.value, (value) => {
  if (value) emit("saved")
})

const submit = async () => {
  if (props.course?.id) {
    await formService.updateCourse()
  } else {
    await formService.saveCourse()
  }
}
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Nombre del curso -->
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <Label>Nombre del curso</Label>
        <FormControl>
          <Select v-bind="componentField" :disabled="loading">
            <SelectTrigger>
              <SelectValue placeholder="Selecciona un curso" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="option in COURSE_NAME_OPTIONS"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors['name'] ? errors['name'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Organización -->
    <FormField v-slot="{ componentField }" name="organization">
      <FormItem>
        <Label>Organización</Label>
        <FormControl>
          <Input placeholder="Ej. Cruz Roja" v-bind="componentField" :disabled="loading" />
        </FormControl>
        <FormMessage>{{ errors['organization'] ? errors['organization'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Fecha -->
    <FormField v-slot="{ componentField }" name="date">
      <FormItem>
        <Label>Fecha</Label>
        <FormControl>
          <Input type="date" v-bind="componentField" :disabled="loading" />
        </FormControl>
        <FormMessage>{{ errors['date'] ? errors['date'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>{{ props.course ? "Actualizar" : "Guardar" }}</span>
    </Button>
  </div>
</template>
