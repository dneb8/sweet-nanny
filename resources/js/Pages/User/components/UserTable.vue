<script setup lang="ts">
import DataTable from "@/components/datatable/Main.vue";
import Column from "@/components/datatable/Column.vue";
import type { FetcherResponse } from "@/types/FetcherResponse";
import type { User } from "@/types/User";
import UserCard from "./UserCard.vue";

import { Avatar, AvatarFallback } from "@/components/ui/avatar";

import { getUserInitials } from "@/utils/getUserInitials";
import { UserTableService } from "@/services/userTableService";
import UserFiltros from "./UserFiltros.vue";
import { getRoleLabelByString } from "@/enums/role.enum";
import Badge from "@/components/common/Badge.vue";
// import Icon from "@/Components/common/Icon.vue"; // ajusta ruta/casing si aplica

// 👇 Nuevo import
import DeleteModal from '@/components/common/DeleteModal.vue';

defineProps<{
  resource: FetcherResponse<User>;
  roles: Array<string>;
}>();

// Servicio que expone estado + handlers
const {
  usuarioEliminar,
  modalEliminarUsuario,
  filtros,
  visibleColumns,
  verUsuarioPerfil,
  editarUsuario,
  abrirModalEliminarUsuario,
  cerrarModalEliminarUsuario,
  eliminarUsuario,
  getRoleBadgeClasses,
} = new UserTableService();
</script>

<template>
  <DataTable
    :resource="resource"
    resourcePropName="users"
    use-filters
    :canToggleColumnsVisibility="true"
    v-model:visibleColumns="visibleColumns"
    :responsiveCards="'lg'"
  >
    <!-- Filtros -->
    <template #filters>
      <UserFiltros v-model:filtros="filtros" :roles="roles" />
    </template>

    <!-- Card responsivo -->
    <template #responsive-card="{ slotProps }">
      <UserCard :user="slotProps" :permissions="permissions" />
    </template>

    <!-- Columna Perfil -->
    <Column header="Perfil" field="id">
      <template #body="slotProps">
        <div
          @click="verUsuarioPerfil(slotProps.record)"
          class="flex items-center gap-2 cursor-pointer hover:text-purple-600 dark:hover:text-purple-400"
        >
          <Avatar shape="square" size="sm" class="cursor-pointer">
            <AvatarFallback>
              {{ getUserInitials(slotProps.record) }}
            </AvatarFallback>
          </Avatar>
        </div>
      </template>
    </Column>

    <!-- Nombre -->
    <Column header="Nombre" :sortable="true">
      <template #body="slotProps">
        {{ slotProps.record?.name ?? "—" }}
      </template>
    </Column>

    <!-- Apellidos -->
    <Column header="Apellidos" :sortable="true">
      <template #body="slotProps">
        {{ slotProps.record?.surnames ?? "—" }}
      </template>
    </Column>

    <!-- Email -->
    <Column header="Correo Electrónico" :sortable="true">
      <template #body="slotProps">
        {{ slotProps.record?.email ?? "—" }}
      </template>
    </Column>

    <!-- Rol (único) -->
    <Column header="Rol">
      <template #body="slotProps">
        <Badge
          class="min-w-32"
          :label="getRoleLabelByString(slotProps.record?.roles?.[0]?.name ?? '') || 'Sin rol'"
          :customClass="getRoleBadgeClasses(slotProps.record?.roles?.[0]?.name ?? '')"
        />
      </template>
    </Column>

    <!-- Acciones -->
    <Column header="Acciones" field="id">
      <template #body="slotProps">
        <div class="grid grid-cols-3 gap-2">
          <div
            @click="editarUsuario(slotProps.record)"
            class="flex justify-center items-center w-max text-blue-600 dark:text-blue-500 hover:text-blue-600/80 dark:hover:text-blue-400 hover:cursor-pointer"
            title="Editar usuario"
          >
            <Icon icon="mdi:edit-outline" :size="20" />
          </div>

          <div
            @click="abrirModalEliminarUsuario(slotProps.record)"
            class="flex justify-center items-center w-max text-rose-600 dark:text-rose-500 hover:text-rose-600/80 dark:hover:text-rose-400 hover:cursor-pointer"
            title="Eliminar usuario"
          >
            <Icon icon="fluent:delete-12-regular" :size="20" />
          </div>
        </div>
      </template>
    </Column>
  </DataTable>

  <!-- ✅ Reemplazo del Dialog por DeleteModal -->
  <DeleteModal
    v-model:show="modalEliminarUsuario"
    :message="`¿Estás seguro de eliminar a ${usuarioEliminar?.name ?? ''} (${usuarioEliminar?.email ?? ''})?`"
    :onConfirm="eliminarUsuario"
    :onCancel="cerrarModalEliminarUsuario"
    confirmText="Sí, eliminar"
    cancelText="No, cancelar"
  />
</template>
