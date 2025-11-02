<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card } from '@/components/ui/card'
import Badge from "@/components/common/Badge.vue"
import AvatarUploader from '@/components/common/AvatarUploader.vue'
import { getRoleLabelByString } from "@/enums/role.enum"
import { UserTableService } from "@/services/userTableService"

import { Progress } from "@/components/ui/progress"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogTrigger } from "@/components/ui/dialog"
import NannyProfileForm from '../components/NannyProfileForm.vue'
import { ref, computed } from "vue"
import { formatExperienceFrom } from "@/utils/formatExperienceForm"

const props = defineProps<{ nanny: Nanny }>()
const emit = defineEmits<{
  (e: 'avatar-updated'): void
  (e: 'avatar-deleted'): void
}>()

const { getRoleBadgeClasses } = new UserTableService()
const isVerified = !!props.nanny.user?.email_verified_at

const open = ref(false)
const sections = [
  { key: 'bio', value: props.nanny.bio },
  { key: 'address', value: props.nanny.addresses },
  { key: 'courses', value: props.nanny.courses },
  { key: 'careers', value: props.nanny.careers },
  { key: 'qualities', value: props.nanny.qualities },
]
const completedSections = sections.filter(
  s => s.value && (Array.isArray(s.value) ? s.value.length > 0 : true)
).length
const completion = ref(Math.round((completedSections / sections.length) * 100))

const experience = ref(formatExperienceFrom(props.nanny.start_date ?? ''))

const fullName = computed(() => {
  const name = props.nanny.user?.name ?? 'Nanny sin nombre'
  const surnames = props.nanny.user?.surnames ? ` ${props.nanny.user?.surnames}` : ''
  return `${name}${surnames}`
})

const handleSaved = () => {
  open.value = false
  experience.value = formatExperienceFrom(props.nanny.start_date ?? '')
}
</script>

<template>
  <Card
    class="relative p-6 border-none shadow-md bg-gradient-to-r from-pink-50 dark:from-pink-700/5 to-purple-50 dark:to-purple-700/5"
  >
    <!-- Botón flotante -->
    <Dialog v-model:open="open">
      <DialogTrigger as-child>
        <Button
          variant="outline"
          size="sm"
          class="absolute top-3 right-3 h-9 gap-2 bg-white/70 dark:bg-background/60 backdrop-blur-md hover:bg-white dark:hover:bg-background"
        >
          <Icon icon="mdi:pencil" class="text-base" />
          <span class="hidden sm:inline">Editar niñera</span>
        </Button>
      </DialogTrigger>

      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Editar perfil de niñera</DialogTitle>
          <DialogDescription>Modifica tu biografía o fecha de inicio</DialogDescription>
        </DialogHeader>
        <NannyProfileForm :nanny="props.nanny" @saved="handleSaved" />
      </DialogContent>
    </Dialog>

    <!-- Layout: avatar izquierda, info derecha -->
    <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
      <!-- Avatar -->
      <div class="shrink-0 self-center md:self-start">
        <AvatarUploader
          :avatar-url="props.nanny.user?.avatar_url"
          :avatar-status="(props.nanny.user as any)?.avatar_status"
          :avatar-note="(props.nanny.user as any)?.avatar_note"
          :upload-route="route('users.avatar.update', props.nanny.user?.ulid)"
          :delete-route="route('users.avatar.delete', props.nanny.user?.ulid)"
          :size="160"
          :first-name="props.nanny.user?.name"
          :last-name="props.nanny.user?.surnames"
          :poll="true"
          :readonly="false"
          @uploaded="emit('avatar-updated')"
          @deleted="emit('avatar-deleted')"
        />
      </div>

      <!-- Info a la derecha (centrada en móvil, start en md+) -->
      <div class="flex-1 space-y-3 text-center md:text-left">
        <!-- Nombre + verificado -->
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
          <h1 class="text-2xl font-bold leading-tight truncate">
            {{ fullName }}
          </h1>
          <Badge
            v-if="isVerified"
            :size="32"
            aria-label="Verificado"
            :customClass="'inline-flex items-center bg-emerald-100 text-emerald-700 dark:bg-emerald-600/20 dark:text-emerald-200 border border-emerald-700'"
            :icon="'mdi:check-decagram'"
          />
        </div>

        <!-- Email -->
        <p class="text-sm text-muted-foreground truncate">
          {{ props.nanny.user?.email ?? 'Sin email' }}
        </p>

        <!-- Rol -->
        <div class="flex justify-center md:justify-start">
          <Badge
            class="min-w-32"
            :label="getRoleLabelByString(props.nanny.user?.roles?.[0]?.name ?? '') || 'Sin rol'"
            :customClass="getRoleBadgeClasses(props.nanny.user?.roles?.[0]?.name ?? '')"
          />
        </div>

        <!-- Bio -->
        <p class="text-sm text-muted-foreground">
          <span v-if="props.nanny.bio">{{ props.nanny.bio }}</span>
          <span v-else class="italic">Ninguna descripción aún</span>
        </p>

        <!-- Experiencia cute -->
        <div class="flex justify-center md:justify-start">
          <span
            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium
                   bg-pink-100/70 text-pink-700 border border-pink-200
                   dark:bg-pink-500/15 dark:text-pink-200 dark:border-pink-700/40"
          >
            <Icon icon="mdi:flower-outline" class="text-base" />
            Experiencia: {{ experience }}
          </span>
        </div>

        <!-- Progreso -->
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-2">
          <Badge
            v-if="completion !== 100"
            :label="`Perfil ${completion}% completado`"
            :customClass="'px-3 py-1 text-sm bg-secondary text-secondary-foreground border border-border'"
          />
        </div>
        <Progress v-if="completion !== 100" v-model="completion" class="h-2 mt-2" />
      </div>
    </div>
  </Card>
</template>
