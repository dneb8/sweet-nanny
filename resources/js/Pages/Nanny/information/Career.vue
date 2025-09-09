<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import type { Career } from '@/types/Career'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import CareerForm from '../forms/CareerForm.vue'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'

const props = defineProps<{ 
  nanny: Nanny 
}>()

// Estado para crear/editar carrera
const showModal = ref(false)
const selectedCareer = ref<Career | null>(null)

// Estado para eliminar carrera
const showDeleteModal = ref(false)

// Abrir modal para agregar o editar
const openModal = (career: Career | null = null) => {
  selectedCareer.value = career
  showModal.value = true
}

// Abrir modal de eliminar
const openDelete = (career: Career) => {
  selectedCareer.value = career
  showDeleteModal.value = true
}

// Función para eliminar al confirmar 
const deleteCareer = () => {
  if (!selectedCareer.value) return
  router.delete(route('careers.destroy', selectedCareer.value.id), {
    onSuccess: () => {
      selectedCareer.value = null
      showDeleteModal.value = false
    },
    onError: (errors) => {
      console.error(errors)
    },
  })
}
</script>

<template>
  <Card class="bg-blue-50 dark:bg-blue-500/10 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:briefcase" /> Carreras
        </div>
        <Button size="sm" variant="outline" @click="openModal()">
          <Icon icon="lucide:plus" /> Nueva
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent>
      <!-- Lista de carreras -->
      <div v-if="props.nanny.careers?.length" class="flex flex-col gap-2">
        <div
          v-for="career in props.nanny.careers"
          :key="career.id"
          class="p-2 rounded shadow-sm border flex justify-between items-center"
        >
          <!-- Info -->
          <div>
            <p class="font-medium">{{ career.name }}</p>
            <p class="text-sm text-muted-foreground">{{ career.degree }} - {{ career.area }}</p>
            <p class="text-sm text-muted-foreground">{{ career.institution }}</p>
            <p class="text-sm text-muted-foreground">Estatus: {{ career.status }}</p>
          </div>

          <!-- Botones -->
          <div class="flex gap-2">
            <Button size="sm" variant="ghost" @click="openModal(career)">
              <Icon icon="lucide:edit" />
            </Button>
            <Button size="sm" variant="destructive" @click="openDelete(career)">
              <Icon icon="lucide:trash" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Sin carreras -->
      <div v-else>
        <div class="flex flex-col items-center text-muted-foreground">
          <Icon icon="lucide:briefcase" class="w-8 h-8 mb-2" />
          <span>Sin carreras</span>
        </div>
      </div>
    </CardContent>
  </Card>

  <!-- Modal de formulario -->
  <FormModal
    v-model="showModal"
    :title="selectedCareer ? 'Editar Carrera' : 'Agregar Carrera'"
    :form-component="CareerForm"
    :form-props="{
      nanny: nanny,
      career: selectedCareer
    }"
  />

  <!-- Modal de eliminación -->
  <DeleteModal
    v-model:show="showDeleteModal"
    title="carrera"
    :message="`¿Estás seguro de eliminar la carrera ${selectedCareer?.name}?`"
    @confirm="deleteCareer"
  />
</template>
