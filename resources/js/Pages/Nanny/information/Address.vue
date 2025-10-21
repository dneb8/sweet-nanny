<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import type { Address } from '@/types/Address'
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import { router } from '@inertiajs/vue3'

// Formulario polimórfico
import AddressForm from '@/Pages/Address/components/AddressForm.vue'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'

const props = defineProps<{ 
  nanny: Nanny,
  isOwner: boolean
}>()

// Nanny::with('addresses')->findOrFail(...)
const addresses = computed<Address[]>(() => props.nanny.addresses ?? [])

// Dueño polimórfico para el AddressForm
const ownerId = computed<number>(() => Number(props.nanny.id))
const ownerType = "App\\Models\\Nanny"

// Estado modal crear/editar
const showModal = ref(false)
const selectedAddress = ref<Address | null>(null)

// Estado modal eliminar
const showDeleteModal = ref(false)

// Abrir modal crear/editar
const openModal = (address: Address | null = null) => {
  selectedAddress.value = address
  showModal.value = true
}

// Abrir modal eliminar
const openDelete = (address: Address) => {
  selectedAddress.value = address
  showDeleteModal.value = true
}

// Tras guardar/editar
const onSaved = () => {
  showModal.value = false
  selectedAddress.value = null
  router.reload({ only: ['nanny'] })
}

// Eliminar
const deleteAddress = () => {
  if (!selectedAddress.value) return
  router.delete(route('addresses.destroy', selectedAddress.value.id), {
    onSuccess: () => {
      selectedAddress.value = null
      showDeleteModal.value = false
      router.reload({ only: ['nanny'] })
    },
  })
}
</script>

<template>
  <Card class="bg-blue-50 dark:bg-blue-500/5 border-none shadow-sm">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:map-pin" />
          <span>Direcciones</span>
        </div>

        <Button
          size="sm"
          variant="outline"
          @click="openModal()"
        >
          <Icon icon="lucide:plus" class="mr-1" />
          Nueva
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent>
      <div v-if="addresses.length" class="grid gap-3">
        <div
          v-for="addr in addresses"
          :key="addr.id"
          class="p-3 rounded border flex justify-between items-start"
        >
          <div class="space-y-1">
            <p class="font-medium">{{ addr.street }}</p>
            <p class="text-sm text-muted-foreground">{{ addr.neighborhood }}</p>
            <p class="text-sm text-muted-foreground">CP {{ addr.postal_code }}</p>
          </div>

          <div class="flex gap-2">
            <Button size="sm" variant="ghost" @click="openModal(addr)">
              <Icon icon="lucide:edit" />
            </Button>
            <Button size="sm" variant="destructive" @click="openDelete(addr)">
              <Icon icon="lucide:trash" />
            </Button>
          </div>
        </div>
      </div>

      <div v-else class="flex flex-col items-center text-muted-foreground py-6">
        <Icon icon="lucide:map" class="w-8 h-8 mb-2" />
        <span>Sin direcciones registradas</span>
      </div>
    </CardContent>
  </Card>

  <!-- Modal polimórfico con formComponent -->
  <FormModal
    v-model="showModal"
    :title="selectedAddress ? 'Editar dirección' : 'Agregar dirección'"
    :form-component="AddressForm"
    :form-props="{
      address: selectedAddress ?? undefined,
      ownerId: ownerId,
      ownerType: ownerType
    }"
    @saved="onSaved"
  />

  <!-- Modal eliminar -->
  <DeleteModal
    v-model:show="showDeleteModal"
    title="dirección"
    :message="`¿Estás seguro de eliminar la dirección ${selectedAddress?.street}?`"
    @confirm="deleteAddress"
  />
</template>
