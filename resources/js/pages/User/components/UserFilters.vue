<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'
import { Checkbox } from "@/components/ui/checkbox"
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum'
import { Label } from '@/components/ui/label';

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

// Toggle verified (mutuamente exclusivo)
function toggleVerified(status: string) {
  verifiedFilter.value = verifiedFilter.value === status ? null : status
}

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
     <Label>Verificación de correo</Label>
    <div v-if="sortables?.includes('email_verified_at')" class="flex gap-4 flex-wrap mt-2">
      <div class="flex items-center space-x-2">
        <Checkbox :checked="verifiedFilter === 'verified'" @click="toggleVerified('verified')" id="verified-yes" />
        <label for="verified-yes">Verificados</label>
      </div>
      <div class="flex items-center space-x-2">
        <Checkbox :checked="verifiedFilter === 'unverified'" @click="toggleVerified('unverified')" id="verified-no" />
        <label for="verified-no">No verificados</label>
      </div>
    </div>

    <!-- Botón reset -->
    <div class="pt-2">
      <button
        @click="resetFilters"
        class="px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 text-sm"
      >
        Limpiar filtros
      </button>
    </div>
  </div>
</template>
