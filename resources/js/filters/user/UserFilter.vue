<script setup lang="ts">
import { useUserFilter } from './index';

defineProps({
  show: Boolean,
  sortables: Array as () => string[],
});

const emit = defineEmits(['update:selectedFilters', 'update:filterFn', 'closePopover']);

const {
  roleFilter,
  verifiedFilter,
  toggleRole,
  resetFilters,
  sortables: _sortables,
  getRoleLabelByString,
  RoleEnum,
  Checkbox,
  Label,
  Button,
  ToggleGroup,
  ToggleGroupItem,
} = useUserFilter(emit, sortables);
</script>

<template>
  <div v-if="show" class="p-4 space-y-3">
    <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-200">
      Filtrar por:
    </h4>

    <!-- Roles -->
    <Label>Rol</Label>
    <div v-if="_sortables?.includes('role')" class="flex gap-4 flex-col">
      <div
        v-for="roleKey in Object.values(RoleEnum)"
        :key="roleKey"
        class="flex items-center space-x-2"
      >
        <Checkbox
          :checked="roleFilter.includes(roleKey)"
          @click="toggleRole(roleKey)"
          :id="`role-${roleKey}`"
        />
        <label :for="`role-${roleKey}`">
          {{ getRoleLabelByString(roleKey) }}
        </label>
      </div>
    </div>

    <!-- Verified -->
    <div>
      <Label>Verificación de correo</Label>
      <ToggleGroup
        type="single"
        v-model="verifiedFilter"
        class="flex gap-2 mt-2"
      >
        <ToggleGroupItem value="verified" aria-label="Verificados">
          Verificados
        </ToggleGroupItem>
        <ToggleGroupItem value="unverified" aria-label="No verificados">
          No verificados
        </ToggleGroupItem>
      </ToggleGroup>
    </div>

    <!-- Botón reset -->
    <div class="pt-2 flex justify-end">
      <Button @click="resetFilters" class="">
        <Icon icon="solar:restart-circle-linear" class="size-6" />
        Limpiar filtros
      </Button>
    </div>
  </div>
</template>
