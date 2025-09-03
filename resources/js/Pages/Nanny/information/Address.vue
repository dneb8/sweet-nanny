<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'
import AddressForm from '@/Pages/Address/components/AddressForm.vue'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'

const props = defineProps<{ 
  nanny: Nanny,
  isOwner: boolean
}>()


// Estado para crear/editar dirección
const showModal = ref(false)
const selectedAddress = ref<any>(null)

// Estado para eliminar dirección
const showDeleteModal = ref(false)

// Abrir modal para agregar o editar dirección
const openModal = (address: any | null = null) => {
  selectedAddress.value = address
  showModal.value = true
}

// Abrir modal de eliminar
const openDelete = (address: any) => {
  selectedAddress.value = address
  showDeleteModal.value = true
}

// Función para eliminar al confirmar
const deleteAddress = () => {
  if (!selectedAddress.value) return
  router.delete(route('addresses.destroy', selectedAddress.value.id), {
    onSuccess: () => {
      selectedAddress.value = null
      showDeleteModal.value = false
    },
    onError: (errors) => {
      console.error(errors)
    },
  })
}
</script>

<template>
  <Card class="bg-blue-50 dark:bg-blue-500/5 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:map-pin" /> Dirección
        </div>

        <!-- Mostrar botón "Nuevo" SOLO si no hay dirección -->
        <Button
          v-if="!props.nanny.user.address"
          size="sm"
          variant="outline"
          @click="openModal()"
        >
          <Icon icon="lucide:plus" /> Nuevo
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent>
      <div v-if="props.nanny.user.address" class="p-2 rounded shadow-sm border flex justify-between items-center">
        <!-- Información de la dirección -->
        <div>
          <p class="font-medium">{{ props.nanny.user.address.street }}</p>
          <p class="text-sm text-muted-foreground">{{ props.nanny.user.address.neighborhood }}</p>
          <p class="text-sm text-muted-foreground">{{ props.nanny.user.address.postal_code }}</p>
        </div>

        <!-- Botones para editar/eliminar -->
        <div class="flex gap-2">
          <Button size="sm" variant="ghost" @click="openModal(props.nanny.user.address)">
            <Icon icon="lucide:edit" />
          </Button>
          <Button size="sm" variant="destructive" @click="openDelete(props.nanny.user.address)">
            <Icon icon="lucide:trash" />
          </Button>
        </div>
      </div>

      <div v-else class="flex flex-col items-center text-muted-foreground">
        <Icon icon="lucide:map" class="w-8 h-8 mb-2" />
        <span>No registrada</span>
      </div>
    </CardContent>
  </Card>

  <!-- Modales -->
  <FormModal
    v-model="showModal"
    :title="selectedAddress ? 'Editar Dirección' : 'Agregar Dirección'"
    :form-component="AddressForm"
    :form-props="{
      nanny: nanny,
      address: selectedAddress
    }"
  />

  <DeleteModal
    v-model:show="showDeleteModal"
    title="dirección"
    :message="`¿Estás seguro de eliminar la dirección ${selectedAddress?.street}?`"
    @confirm="deleteAddress"
  />
</template>
