<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { Checkbox } from "@/components/ui/checkbox"
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'
import { Label } from '@/components/ui/label';
import Button from '@/components/ui/button/Button.vue';
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group"

defineProps({
  show: Boolean,
  sortables: Array as () => string[],
})

const emit = defineEmits(['update:selectedFilters', 'update:filterFn', 'closePopover'])

// Filtros
const roleFilter = ref<string[]>([])
const verifiedFilter = ref<string | null>(null)

// Toggle roles (permite múltiple selección)
function toggleRole(role: string) {
  if (roleFilter.value.includes(role)) {
    roleFilter.value = roleFilter.value.filter(r => r !== role)
  } else {
    roleFilter.value.push(role)
  }
}

// Toggle verified 
// Emitir cuando cambie
watch(verifiedFilter, (val) => {
  console.log("Nuevo valor:", val) 
})

// Reset filters: reinicia refs y fuerza actualización del DOM
function resetFilters() {
  roleFilter.value = []
  verifiedFilter.value = null
  nextTick(() => {
    
  })
  emit('closePopover')
}

// Emitir filtros y función de filtrado
watch(
  [roleFilter, verifiedFilter],
  () => {
    const filters = {
      role: [...roleFilter.value], // Array de roles
      email_verified_at: verifiedFilter.value || '',
    }

    emit('update:selectedFilters', filters)

    emit('update:filterFn', (item: any) => {
      // Filtrar roles: item.roles debe incluir al menos un rol seleccionado
      if (roleFilter.value.length > 0) {
        const itemRoles = (item.roles || []).map((r: any) => r.name?.toLowerCase())
        const matchesRole = roleFilter.value.some(r => itemRoles.includes(r))
        if (!matchesRole) return false
      }

      // Filtrar verified
      if (verifiedFilter.value) {
        const isVerified = item.email_verified_at !== null
        if (verifiedFilter.value === 'verified' && !isVerified) return false
        if (verifiedFilter.value === 'unverified' && isVerified) return false
      }

      return true
    })
  },
  { immediate: true }
)
</script>

<template>
  <div v-if="show" class="p-4 space-y-3">
    <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-200">
      Filtrar por:
    </h4>

    <!-- Roles -->
     <Label>Rol</Label>
    <div v-if="sortables?.includes('role')" class="flex gap-4 flex-col">
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
      <Button
        @click="resetFilters"
        class=""
      >
      <Icon icon="solar:restart-circle-linear" class="size-6"/>
        Limpiar filtros
      </Button>
    </div>
  </div>
</template>
