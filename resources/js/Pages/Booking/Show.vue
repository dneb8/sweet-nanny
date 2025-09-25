<script lang="ts" setup>
import type { Booking } from '@/types/Booking'
import Heading from '@/components/Heading.vue'
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import { useBookingService } from '@/services/BookingService'

// Props
const props = defineProps<{
  booking: Booking
  columns: string[]
}>()

// Usamos nuestro servicio
const {
  showDeleteModal,
  showBooking,
  editBooking,
  deleteBooking,
  confirmDeleteBooking,
  getRoleBadgeClass,
} = useBookingService(props.booking)
</script>

<template>
  <div class="space-y-6">
    <!-- Heading -->
    <Heading icon="proicons:person" :title="props.booking.name + ' ' + props.booking.surnames" />

    <!-- Info básica -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <p class="font-medium">{{ props.booking.name }} {{ props.booking.surnames }}</p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Email</p>
        <p class="font-medium flex items-center gap-2">
          {{ props.booking.email }}
          <Icon
            v-if="props.booking.email_verified_at"
            icon="mdi:check-circle"
            class="w-4 h-4 text-emerald-500"
          />
        </p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Número</p>
        <p class="font-medium">{{ props.booking.number }}</p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Rol</p>
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium',
            getRoleBadgeClass(props.booking.roles?.[0]?.name)
          ]"
        >
          {{ props.booking.roles?.[0]?.name ?? 'Sin rol' }}
        </span>
      </div>
    </div>

    <!-- Acciones -->
    <div class="flex flex-wrap gap-3">
      <Button @click="editBooking" variant="secondary">
        <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2" />
        Editar
      </Button>

      <Button @click="deleteBooking" variant="destructive">
        <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2" />
        Eliminar
      </Button>

      <!-- Mostrar botón especial si es nanny o tutor -->
      <Button v-if="props.booking.nanny" @click="showBooking" variant="outline">
        <Icon icon="mdi:account-child-outline" class="w-4 h-4 mr-2" />
        Ver perfil de Niñera
      </Button>

      <Button v-if="props.booking.tutor" @click="showBooking" variant="outline">
        <Icon icon="mdi:school-outline" class="w-4 h-4 mr-2" />
        Ver perfil de Tutor
      </Button>
    </div>

    <!-- Delete Modal -->
    <DeleteModal
      v-model:show="showDeleteModal"
      :message="`¿Estás seguro de eliminar a ${props.booking.name}?`"
      :onConfirm="confirmDeleteBooking"
    />
  </div>
</template>
