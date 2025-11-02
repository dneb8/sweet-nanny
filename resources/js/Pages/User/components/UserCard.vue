<script setup lang="ts">
import { Card, CardHeader, CardContent } from '@/components/ui/card'
import { Button } from "@/components/ui/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { ScrollArea, ScrollBar } from "@/components/ui/scroll-area"
import DeleteModal from '@/components/common/DeleteModal.vue'
import { MoreVertical } from 'lucide-vue-next'
import { Icon } from '@iconify/vue'

import type { User } from '@/types/User'
import { UserService } from '@/services/UserService'
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'
import { getQualityLabelByString } from '@/enums/quality.enum'
import { userPolicy } from '@/policies/userPolicy'


const props = defineProps<{
  user: User
}>()

// User Service con funciones para manejar acciones del usuario
const {
  showDeleteModal,
  showUser,
  editUser,
  deleteUser,
  confirmDeleteUser,
  getRoleBadgeClass,
} = new UserService(props.user)
</script>

<template>
  <Card class="relative overflow-hidden">
    <!-- Menú -->
    <div class="absolute top-2 right-2 z-20">
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
            <MoreVertical class="h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-44">
          <DropdownMenuGroup>
            <!-- Ver perfil -->
            <DropdownMenuItem
              v-if="props.user.roles?.[0]?.name !== RoleEnum.ADMIN"
              @click="showUser"
              class="group text-muted-foreground hover:bg-muted"
            >
              <Icon icon="mdi:account-eye-outline" class="w-4 h-4 mr-2 text-muted-foreground" />
              Ver perfil
            </DropdownMenuItem>

            <!-- Editar -->
            <DropdownMenuItem
              v-if="userPolicy.canUpdateUser(props.user)"
              @click="editUser"
              class="group text-muted-foreground hover:bg-muted"
            >
              <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2 text-sky-600" />
              Editar
            </DropdownMenuItem>
          </DropdownMenuGroup>

          <DropdownMenuSeparator v-if="userPolicy.canUpdateUser(props.user) && userPolicy.canDeleteUser(props.user)" />

          <!-- Eliminar -->
          <DropdownMenuItem
            v-if="userPolicy.canDeleteUser(props.user)"
            @click="deleteUser"
            class="group text-muted-foreground hover:bg-muted"
          >
            <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2 text-rose-600" />
            Eliminar
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
    <!-- Cabecera -->
    <CardHeader
      class="flex flex-row gap-4 items-start px-4 transition-transform duration-200 hover:scale-105 cursor-pointer"
      @click="props.user.roles?.[0]?.name === RoleEnum.ADMIN ? editUser() : showUser()"
    >
      <!-- Avatar -->
      <div class="flex-none w-20 flex flex-col items-center">
        <div
          class="w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center border overflow-hidden"
        >
          <Icon icon="mdi:image-outline" class="w-8 h-8 text-slate-400" />
        </div>
      </div>

      <!-- Info usuario -->
      <div class="flex-1 min-w-0">
        <span
          :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium',
            getRoleBadgeClass(props.user.roles?.[0]?.name)
          ]"
        >
          {{ getRoleLabelByString(props.user.roles?.[0]?.name) ?? 'Sin rol' }}
        </span>

        <div class="mt-2 flex items-center gap-2 min-w-0">
          <h3 class="text-sm font-semibold truncate">
            {{ props.user.name }} {{ props.user.surnames }}
          </h3>
          <Icon
            v-if="props.user.email_verified_at"
            icon="mdi:check-circle"
            class="w-4 h-4 text-emerald-500"
          />
        </div>

        <p class="mt-1 text-xs text-muted-foreground truncate">
          {{ props.user.email }}
        </p>
      </div>
    </CardHeader>

    <!-- Contenido: habilidades (solo si es nanny) -->
    <CardContent v-if="props.user.roles?.[0]?.name === RoleEnum.NANNY && props.user.nanny?.qualities?.length">
      <ScrollArea class="w-full whitespace-nowrap ">
        <div class="flex gap-2">
          <span
            v-for="(quality, idx) in props.user.nanny.qualities"
            :key="idx"
            class="flex-none text-xs px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800"
          >
            {{ getQualityLabelByString(quality.name) ?? '' }}
          </span>
        </div>
        <ScrollBar orientation="horizontal" class="pt-1 overflow-auto"/>
      </ScrollArea>
    </CardContent>

    <!-- Modal eliminar -->
    <DeleteModal
      v-model:show="showDeleteModal"
      :message="`¿Estás seguro de eliminar a ${props.user.name}?`"
      :onConfirm="confirmDeleteUser"
    />
  </Card>
</template>

