<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { User as UserIcon, X } from 'lucide-vue-next'
import { Icon } from '@iconify/vue'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useNotify } from '@/composables/useNotify'

import DeleteUser from '@/components/DeleteUser.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import type { User } from '@/types'

interface Props {
  mustVerifyEmail: boolean
  status?: string
  avatarUrl?: string | null
  avatarStatus?: 'pending' | 'approved' | 'rejected' | string | undefined
  avatarNote?: string | null
}
const props = defineProps<Props>()

/** ---- Contexto Auth ---- */
const page = usePage()
const user = page.props.auth?.user as User

/** ---- Notificaciones ---- */
const { notifyError } = useNotify()

/** ---- IDs únicos por render para accesibilidad ---- */
const uid = ref(
  (typeof crypto !== 'undefined' && 'randomUUID' in crypto)
    ? crypto.randomUUID()
    : `uid-${Math.random().toString(36).slice(2, 8)}`
)

/** ---- Formulario de perfil ---- */
const form = useForm({
  name: user?.name ?? '',
  surnames: (user as any)?.surnames ?? '',
  email: user?.email ?? '',
})

const submit = () => {
  form.patch(route('profile.update'), { preserveScroll: true })
}

/** ---- Avatar: subir / previsualizar / borrar ---- */
const avatarForm = useForm<{ avatar: File | null }>({ avatar: null })
const previewUrl = ref<string | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)
const isDeleting = ref(false)

const currentAvatarUrl = computed(() => previewUrl.value || props.avatarUrl || null)

/** Iniciales para fallback */
const userInitials = computed(() => {
  const n = (user?.name ?? '').trim()
  const s = ((user as any)?.surnames ?? '').trim()
  const parts = `${n} ${s}`.trim().split(/\s+/).filter(Boolean)
  if (parts.length === 0) return ''
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
})
const hasInitials = computed(() => userInitials.value.length > 0)

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  avatarForm.avatar = file

  // Previsualización
  const reader = new FileReader()
  reader.onload = e => { previewUrl.value = e.target?.result as string }
  reader.readAsDataURL(file)
}

const resetFileInput = () => {
  previewUrl.value = null
  avatarForm.reset('avatar')
  if (fileInputRef.value) fileInputRef.value.value = ''
}

const submitAvatar = () => {
  if (!avatarForm.avatar) return
  avatarForm.post(route('profile.avatar.update'), {
    preserveScroll: true,
    onSuccess: () => {
      resetFileInput()
      startStatusPolling() // comenzamos a observar validación si aplica
    },
    onError: (errors) => {
      notifyError(
        'Error al subir imagen',
        (errors as any)?.avatar ?? 'Por favor, intenta nuevamente',
        'mdi:alert-circle'
      )
    }
  })
}

const cancelPreview = () => {
  resetFileInput()
}

const deleteAvatar = () => {
  if (!confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?')) return
  isDeleting.value = true
  router.delete(route('profile.avatar.delete'), {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      resetFileInput()
    }
  })
}

/** ---- Polling del estado del avatar (cuando está en pendiente) ---- */
let statusPollInterval: number | null = null
const POLL_MS = 3000

const clearStatusPolling = () => {
  if (statusPollInterval) {
    clearInterval(statusPollInterval)
    statusPollInterval = null
  }
}

const startStatusPolling = () => {
  clearStatusPolling()
  if (props.avatarStatus === 'pending') {
    statusPollInterval = window.setInterval(() => {
      router.reload({ only: ['avatarUrl', 'avatarStatus', 'avatarNote'] })
    }, POLL_MS)
  }
}

onMounted(() => {
  startStatusPolling()
})

onUnmounted(() => {
  clearStatusPolling()
})

/** ---- Utilidades de verificación de email ---- */
const isEmailVerified = computed<boolean>(() => !!(user as any)?.email_verified_at)
</script>

<template>
  <Head title="Configuración de perfil" />

  <SettingsLayout>
    <div class="flex flex-col space-y-6">
      <!-- AVATAR -->
      <div>
        <HeadingSmall
          title="Foto de perfil"
          description="Actualiza tu foto de perfil (máximo 4 MB)"
        />

        <div class="mt-4 flex items-start gap-6">
          <!-- Avatar Display -->
          <div class="relative">
            <div
              class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-border bg-muted"
              :class="{ 'opacity-50': avatarForm.processing || isDeleting }"
            >
              <img
                v-if="currentAvatarUrl"
                :src="currentAvatarUrl"
                alt="Avatar"
                class="h-full w-full object-cover"
              />

              <!-- Fallback: iniciales o ícono -->
              <template v-else>
                <span
                  v-if="hasInitials"
                  class="select-none text-xl font-semibold text-muted-foreground"
                  aria-hidden="true"
                >
                  {{ userInitials }}
                </span>
                <UserIcon v-else class="h-12 w-12 text-muted-foreground" />
              </template>
            </div>

            <!-- Validation Status Badge -->
            <div
              v-if="props.avatarUrl && props.avatarStatus === 'pending'"
              class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-white shadow-md"
              :title="props.avatarNote || 'En validación'"
            >
              <Icon icon="line-md:loading-twotone-loop" class="h-4 w-4" />
            </div>

            <!-- Loading overlay -->
            <div
              v-if="avatarForm.processing || isDeleting"
              class="absolute inset-0 flex items-center justify-center rounded-full bg-background/50"
            >
              <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
            </div>
          </div>

          <!-- Avatar Controls -->
          <div class="flex flex-1 flex-col gap-4">
            <!-- Validation Status Message -->
            <div
              v-if="props.avatarUrl && props.avatarStatus === 'pending'"
              class="flex items-center gap-2 rounded-md bg-amber-50 dark:bg-amber-950 px-3 py-2 text-sm text-amber-800 dark:text-amber-200"
            >
              <Icon icon="line-md:loading-twotone-loop" class="h-4 w-4" />
              <span>{{ props.avatarNote || 'Tu imagen está siendo validada' }}</span>
            </div>

            <!-- File Input -->
            <div>
              <input
                ref="fileInputRef"
                type="file"
                accept="image/jpeg,image/png,image/jpg,image/webp"
                @change="handleFileChange"
                class="hidden"
                :id="`${uid}-avatar-upload`"
              />
              <Label :for="`${uid}-avatar-upload`" class="cursor-pointer">
                <Button
                  type="button"
                  variant="outline"
                  @click="fileInputRef?.click()"
                  :disabled="avatarForm.processing || isDeleting"
                >
                  Seleccionar imagen
                </Button>
              </Label>
              <p class="mt-2 text-sm text-muted-foreground">JPG, PNG o WEBP. Máximo 4 MB.</p>
              <InputError class="mt-2" :message="(avatarForm.errors as any)?.avatar" />
            </div>

            <!-- Preview Actions -->
            <div v-if="previewUrl" class="flex items-center gap-2">
              <Button type="button" @click="submitAvatar" :disabled="avatarForm.processing" size="sm">
                Guardar nueva foto
              </Button>
              <Button type="button" variant="ghost" @click="cancelPreview" :disabled="avatarForm.processing" size="sm">
                <X class="mr-2 h-4 w-4" />
                Cancelar
              </Button>
            </div>

            <!-- Delete Button -->
            <div v-else-if="props.avatarUrl">
              <Button type="button" variant="destructive" @click="deleteAvatar" :disabled="isDeleting" size="sm">
                Eliminar foto
              </Button>
            </div>

            <!-- Success Message -->
            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p v-show="avatarForm.recentlySuccessful" class="text-sm text-green-600">
                Foto actualizada correctamente.
              </p>
            </Transition>
          </div>
        </div>
      </div>

      <!-- PROFILE INFO -->
      <div>
        <HeadingSmall
          title="Información del perfil"
          description="Actualiza tu nombre y dirección de correo electrónico"
        />

        <form @submit.prevent="submit" class="mt-4 space-y-6">
          <!-- Nombre -->
          <div class="grid gap-2">
            <Label :for="`${uid}-name`">Nombre</Label>
            <Input
              :id="`${uid}-name`"
              class="mt-1 block w-full"
              v-model="form.name"
              required
              autocomplete="name"
              placeholder="Nombre completo"
            />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <!-- Apellidos -->
          <div class="grid gap-2">
            <Label :for="`${uid}-surnames`">Apellidos</Label>
            <Input
              :id="`${uid}-surnames`"
              class="mt-1 block w-full"
              v-model="form.surnames"
              autocomplete="family-name"
              placeholder="Apellido"
            />
            <InputError class="mt-2" :message="(form.errors as any).surname" />
          </div>

          <!-- Email -->
          <div class="grid gap-2">
            <Label :for="`${uid}-email`">Correo electrónico</Label>
            <Input
              :id="`${uid}-email`"
              type="email"
              class="mt-1 block w-full"
              v-model="form.email"
              required
              autocomplete="email"
              placeholder="Correo electrónico"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <!-- Verificación de email -->
          <div v-if="props.mustVerifyEmail && !isEmailVerified">
            <p class="-mt-4 text-sm text-muted-foreground">
              Tu dirección de correo electrónico no está verificada.
              <Link
                :href="route('verification.send')"
                method="post"
                as="button"
                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
              >
                Haz clic aquí para reenviar el correo de verificación.
              </Link>
            </p>

            <div v-if="props.status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
              Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
            </div>
          </div>

          <!-- Acciones -->
          <div class="flex items-center gap-4">
            <Button :disabled="form.processing">Guardar</Button>

            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Guardado.</p>
            </Transition>
          </div>
        </form>
      </div>

      <DeleteUser />
    </div>
  </SettingsLayout>
</template>
