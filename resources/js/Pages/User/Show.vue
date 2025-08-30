<script lang="ts" setup>
import type { User } from '@/types/User'
import Heading from '@/components/Heading.vue'
import { Button } from "@/components/ui/button"
import { Icon } from '@iconify/vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import { useUserService } from '@/services/UserService'

// Props
const props = defineProps<{
  user: User
  columns: string[]
}>()

// usamos nuestro "servicio"
const {
  showDeleteModal,
  showUser,
  editUser,
  deleteUser,
  confirmDeleteUser,
  getRoleBadgeClass,
} = useUserService(props.user)
</script>

<template>
  <div class="space-y-6">
    <!-- Heading -->
    <Heading icon="proicons:person" :title="props.user.name + ' ' + props.user.surnames" />

    <!-- Info básica -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <p class="font-medium">{{ props.user.name }} {{ props.user.surnames }}</p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Email</p>
        <p class="font-medium flex items-center gap-2">
          {{ props.user.email }}
          <Icon
            v-if="props.user.email_verified_at"
            icon="mdi:check-circle"
            class="w-4 h-4 text-emerald-500"
          />
        </p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Número</p>
        <p class="font-medium">{{ props.user.number }}</p>
      </div>

      <div>
        <p class="text-sm text-muted-foreground">Rol</p>
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium',
            getRoleBadgeClass(props.user.roles?.[0]?.name)
          ]"
        >
          {{ props.user.roles?.[0]?.name ?? 'Sin rol' }}
        </span>
      </div>
    </div>

    <!-- Acciones -->
    <div class="flex flex-wrap gap-3">
      <Button @click="editUser" variant="secondary">
        <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2" />
        Editar
      </Button>

      <Button @click="deleteUser" variant="destructive">
        <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2" />
        Eliminar
      </Button>

      <!-- Mostrar botón especial si es nanny o tutor -->
      <Button v-if="props.user.nanny" @click="showUser" variant="outline">
        <Icon icon="mdi:account-child-outline" class="w-4 h-4 mr-2" />
        Ver perfil de Niñera
      </Button>

      <Button v-if="props.user.tutor" @click="showUser" variant="outline">
        <Icon icon="mdi:school-outline" class="w-4 h-4 mr-2" />
        Ver perfil de Tutor
      </Button>
    </div>

    <!-- Delete Modal -->
    <DeleteModal
      v-model:show="showDeleteModal"
      :message="`¿Estás seguro de eliminar a ${props.user.name}?`"
      :onConfirm="confirmDeleteUser"
    />
  </div>
</template>
