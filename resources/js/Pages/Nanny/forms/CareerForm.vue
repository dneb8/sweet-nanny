<script setup lang="ts">
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { watch } from 'vue'
import { CareerFormService } from "@/services/careerFormService"; 
import { Nanny } from "@/types/Nanny"
import { Career } from "@/types/Career"

const props = defineProps<{
  career?: Career;
  nanny: Nanny;
}>()

const emit = defineEmits(["saved"])

const formService = new CareerFormService(
  props.nanny, props.career
);

// Valores reactivos desde el servicio
const { errors, loading, saved } = formService;

// Emitir saved a formservice
watch(() => saved.value, (value) => {
  if (value) {
   
    emit("saved"); 
  }
});

// Función de submit
const submit = async () => {


  if (props.career?.id) {
   
    await formService.updateCareer();
  } else {
   
    await formService.saveCareer();
  }
};
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Nombre -->
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <Label>Nombre de la carrera</Label>
        <FormControl>
          <Input placeholder="Ej. Ingeniería en Informática" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['name'] ? errors['name'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Grado académico -->
    <FormField v-slot="{ componentField }" name="degree">
      <FormItem>
        <Label>Grado académico</Label>
        <FormControl>
          <Input placeholder="Ej. Licenciatura, Técnico" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['degree'] ? errors['degree'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Estatus -->
    <FormField v-slot="{ componentField }" name="status">
      <FormItem>
        <Label>Estatus</Label>
        <FormControl>
          <Input placeholder="Ej. En curso, Finalizado" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['status'] ? errors['status'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Institución -->
    <FormField v-slot="{ componentField }" name="institution">
      <FormItem>
        <Label>Institución</Label>
        <FormControl>
          <Input placeholder="Ej. UDG, UNAM, IPN" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors['institution'] ? errors['institution'][0] : '' }}</FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>{{ props.career ? "Actualizar" : "Guardar" }}</span>
    </Button>
  </div>
</template>
