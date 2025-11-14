<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import DeleteModal from '@/components/common/DeleteModal.vue';
import { MoreVertical } from 'lucide-vue-next';
import { Icon } from '@iconify/vue';
import Badge from '@/components/common/Badge.vue';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { getUserInitials } from '@/utils/getUserInitials';

import type { User } from '@/types/User';
import { UserService } from '@/services/UserService';
import { getRoleLabelByString, RoleEnum } from '@/enums/role.enum';
import { getQualityLabelByString } from '@/enums/quality.enum';
import { userPolicy } from '@/policies/userPolicy';

const props = defineProps<{
    user: User;
}>();

// User Service con funciones para manejar acciones del usuario
const { showDeleteModal, showUser, editUser, deleteUser, confirmDeleteUser, getRoleBadgeClass } = new UserService(props.user);
</script>

<template>
    <!-- Card with styling matching desktop table rows -->
    <div class="bg-white/50 dark:bg-background/50 border border-foreground/20 rounded-lg p-3 space-y-3 relative">
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
                        <DropdownMenuItem v-if="userPolicy.canUpdateUser(props.user)" @click="editUser" class="group text-muted-foreground hover:bg-muted">
                            <Icon icon="mdi:pencil-outline" class="w-4 h-4 mr-2 text-sky-600" />
                            Editar
                        </DropdownMenuItem>
                    </DropdownMenuGroup>

                    <DropdownMenuSeparator v-if="userPolicy.canUpdateUser(props.user) && userPolicy.canDeleteUser(props.user)" />

                    <!-- Eliminar -->
                    <DropdownMenuItem v-if="userPolicy.canDeleteUser(props.user)" @click="deleteUser" class="group text-muted-foreground hover:bg-muted">
                        <Icon icon="mdi:trash-can-outline" class="w-4 h-4 mr-2 text-rose-600" />
                        Eliminar
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <!-- Avatar y Perfil -->
        <div class="flex items-center gap-3 pr-8">
            <Avatar shape="square" size="sm" class="overflow-hidden">
                <AvatarImage v-if="props.user?.avatar_url" :src="props.user.avatar_url" :alt="props.user?.name ?? 'avatar'" class="h-8 w-8 object-cover" />
                <AvatarFallback v-else>
                    {{ getUserInitials(props.user) }}
                </AvatarFallback>
            </Avatar>

            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold truncate text-foreground/80">
                    {{ props.user.name }} {{ props.user.surnames }}
                </div>
                <div class="text-xs text-muted-foreground truncate">{{ props.user.email }}</div>
            </div>

            <Icon v-if="props.user.email_verified_at" icon="mdi:check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0" />
        </div>

        <!-- Rol -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground font-medium">Rol:</span>
            <Badge :label="getRoleLabelByString(props.user.roles?.[0]?.name) ?? 'Sin rol'" :customClass="getRoleBadgeClass(props.user.roles?.[0]?.name)" />
        </div>

        <!-- Habilidades (solo si es nanny) -->
        <div v-if="props.user.roles?.[0]?.name === RoleEnum.NANNY && props.user.nanny?.qualities?.length" class="pt-2 border-t border-foreground/20">
            <div class="text-xs text-muted-foreground font-medium mb-2">Habilidades</div>
            <ScrollArea class="w-full whitespace-nowrap">
                <div class="flex gap-2">
                    <span
                        v-for="(quality, idx) in props.user.nanny.qualities"
                        :key="idx"
                        class="flex-none text-xs px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-foreground/80"
                    >
                        {{ getQualityLabelByString(quality.name) ?? '' }}
                    </span>
                </div>
                <ScrollBar orientation="horizontal" class="pt-1 overflow-auto" />
            </ScrollArea>
        </div>

        <!-- Modal eliminar -->
        <DeleteModal v-model:show="showDeleteModal" :message="`¿Estás seguro de eliminar a ${props.user.name}?`" :onConfirm="confirmDeleteUser" />
    </div>
</template>

