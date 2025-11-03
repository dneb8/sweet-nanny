<script setup lang="ts">
import type { Tutor } from '@/types/Tutor'
import { Card } from '@/components/ui/card'
import { getRoleLabelByString } from "@/enums/role.enum"
import Badge from "@/components/common/Badge.vue"
import AvatarUploader from '@/components/common/AvatarUploader.vue'
import { UserTableService } from "@/services/userTableService"

const props = defineProps<{ tutor: Tutor }>()

const { getRoleBadgeClasses } = new UserTableService()

const isVerified = !!props.tutor.user?.email_verified_at

</script>

<template>
  <Card
    class="p-6 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-r from-blue-50 dark:from-blue-700/5 to-cyan-50 dark:to-cyan-700/5 border-none shadow-md"
  >
    <!-- Avatar + Controles (reutilizable) -->
    <AvatarUploader
        :avatar-url="tutor.user?.avatar_url"
        :avatar-status="tutor.user?.avatar_status"
        :avatar-note="tutor.user?.avatar_note"
        :upload-route="route('users.avatar.update', tutor.user?.ulid)"
        :delete-route="route('users.avatar.delete', tutor.user?.ulid)"
        :size="160"
        :first-name="tutor.user?.name"
        :last-name="tutor.user?.surnames"
    />

    <!-- Info -->
    <div class="flex-1 space-y-3 text-center md:text-left">
      <!-- Header: nombre + verificado -->
      <div class="flex flex-row md:items-center gap-2 md:gap-3">
        <h1 class="text-2xl font-bold leading-tight truncate">
          {{ props.tutor.user?.name ?? 'Tutor sin nombre' }}
          <span v-if="props.tutor.user?.surnames"> {{ props.tutor.user?.surnames }}</span>
        </h1>

        <div class="flex items-center justify-center md:justify-start gap-2">
          <Badge
            v-if="isVerified"
            :size="32"
            aria-label="Verificado"
            :customClass="'inline-flex items-center bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-200 border border-emerald-700'"
            :icon="'mdi:check-decagram'"
          />
        </div>
      </div>

      <!-- Email -->
      <p class="text-sm text-muted-foreground truncate">
        {{ props.tutor.user?.email ?? 'Sin email' }}
      </p>

      <!-- Rol (debajo del nombre + correo) -->
      <div class="flex justify-center md:justify-start">
        <Badge
          class="min-w-32"
          :label="getRoleLabelByString(props.tutor.user?.roles?.[0]?.name ?? '') || 'Sin rol'"
          :customClass="getRoleBadgeClasses(props.tutor.user?.roles?.[0]?.name ?? '')"
        />
      </div>

      <!-- Metadatos -->
      <div
        v-if="props.tutor.emergency_contact || props.tutor.emergency_number"
        class="flex flex-col sm:flex-row sm:flex-wrap items-center md:items-start gap-3 pt-1"
      >
        <div v-if="props.tutor.emergency_contact" class="inline-flex items-center gap-2 text-sm">
          <Icon icon="mdi:phone-alert-outline" class="w-4 h-4 text-muted-foreground" />
          <span class="text-muted-foreground">Contacto emergencia:</span>
          <span class="font-medium">{{ props.tutor.emergency_contact }}</span>
        </div>
        <div v-if="props.tutor.emergency_number" class="inline-flex items-center gap-2 text-sm">
          <Icon icon="mdi:cellphone" class="w-4 h-4 text-muted-foreground" />
          <span class="font-medium tracking-wide">{{ props.tutor.emergency_number }}</span>
        </div>
      </div>
    </div>
  </Card>
</template>
