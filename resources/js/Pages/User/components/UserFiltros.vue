<script setup lang="ts">
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getRoleLabelByString } from '@/enums/role.enum';

export interface FiltrosUser {
    role: string | null;
}

defineProps<{
    roles: Array<string>;
}>();

const filtros = defineModel<FiltrosUser>('filtros', { default: {
    role: null,
}});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 w-full">
        <div class="w-full">
            <div class="flex flex-col">
                <label for="filtro-role" class="mb-1 ml-1">
                    Rol
                </label>

                <Select v-model="filtros.role">
                    <SelectTrigger>
                        <SelectValue placeholder="Selecciona un rol" />
                    </SelectTrigger>

                    <SelectContent>
                        <SelectGroup>
                            <SelectItem
                                v-for="(role, index) in roles"
                                :key="index"
                                :value="role"
                            >
                                {{ getRoleLabelByString(role) }}
                            </SelectItem>
                        </SelectGroup>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>