<script setup lang="ts">
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { watch } from 'vue'
import { CourseFormService } from "@/services/courseFormService";
import { Nanny } from "@/types/Nanny"
import { Course } from "@/types/Course"

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
);

// Valores reactivos desde el servicio
const { errors, loading, saveCourse, updateCourse, saved } = formService;

// Emitir saved a formservice
watch(() => saved.value, (value) => {
  if (value) {
    emit("saved"); 
  }
});

// Función de submit para editar o guardar
const submit = async () => {
  if (props.course?.id) {
    await formService.updateCourse();
  } else {
    await formService.saveCourse();
  }
};
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Nombre -->
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <Label>Nombre del curso</Label>
        <FormControl>
          <Input placeholder="Ej. Primeros auxilios" v-bind="componentField" />
        </FormControl>
        <FormMessage>
          {{ errors['name'] ? errors['name'][0] : '' }}
        </FormMessage>
      </FormItem>
    </FormField>


    <!-- Organización -->
    <FormField v-slot="{ componentField }" name="organization">
      <FormItem>
        <Label>Organización</Label>
        <FormControl>
          <Input placeholder="Ej. Cruz Roja" v-bind="componentField" />
        </FormControl>
        <FormMessage />
        <span v-if="errors['organization']" class="text-sm font-medium">
           {{ errors['organization'][0] }}
        </span>
      </FormItem>
    </FormField>

    <!-- Fecha -->
    <FormField v-slot="{ componentField }" name="date">
      <FormItem>
        <Label>Fecha</Label>
        <FormControl>
          <Input type="date" v-bind="componentField" />
        </FormControl>
        <FormMessage />
        <span v-if="errors['date']" class="text-sm font-medium">
           {{ errors['date'][0] }}
        </span>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading"> Guardando...</span>
      <span v-else>{{ props.course ? "Actualizar" : "Guardar" }}</span>
    </Button>
  </div>
</template>
