import { ref, watch, nextTick } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum';
import { Label } from '@/components/ui/label';
import Button from '@/components/ui/button/Button.vue';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

export function useUserFilter(emit: any, sortables: string[]) {
  // Filtros
  const roleFilter = ref<string[]>([]);
  const verifiedFilter = ref<string | null>(null);

  // Toggle roles (permite múltiple selección)
  function toggleRole(role: string) {
    if (roleFilter.value.includes(role)) {
      roleFilter.value = roleFilter.value.filter(r => r !== role);
    } else {
      roleFilter.value.push(role);
    }
  }

  // Reset filters: reinicia refs y fuerza actualización del DOM
  function resetFilters() {
    roleFilter.value = [];
    verifiedFilter.value = null;
    nextTick(() => {});
    emit('closePopover');
  }

  // Emitir filtros y función de filtrado
  watch([roleFilter, verifiedFilter], () => {
    const filters = {
      role: [...roleFilter.value],
      email_verified_at: verifiedFilter.value || '',
    };

    emit('update:selectedFilters', filters);

    emit('update:filterFn', (item: any) => {
      // Filtrar roles
      if (roleFilter.value.length > 0) {
        const itemRoles = (item.roles || []).map((r: any) => r.name?.toLowerCase());
        const matchesRole = roleFilter.value.some(r => itemRoles.includes(r));
        if (!matchesRole) return false;
      }

      // Filtrar verified
      if (verifiedFilter.value) {
        const isVerified = item.email_verified_at !== null;
        if (verifiedFilter.value === 'verified' && !isVerified) return false;
        if (verifiedFilter.value === 'unverified' && isVerified) return false;
      }

      return true;
    });
  }, { immediate: true });

  return {
    roleFilter,
    verifiedFilter,
    toggleRole,
    resetFilters,
    sortables,
    getRoleLabelByString,
    RoleEnum,
    Checkbox,
    Label,
    Button,
    ToggleGroup,
    ToggleGroupItem,
  };
}
