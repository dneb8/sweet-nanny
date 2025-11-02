<script setup lang="ts">
import { ref, computed, onUnmounted } from "vue"
import { useForm, router } from "@inertiajs/vue3"
import { Icon } from "@iconify/vue"

// Si tus componentes Avatar son locales, ajusta el import:
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar"

// Helper para iniciales (puedes extraerlo a un util)
function getUserInitials(u: { name?: string | null; surnames?: string | null }) {
  const n = (u?.name ?? "").trim()
  const s = (u?.surnames ?? "").trim()
  const i1 = n ? n[0] : ""
  const i2 = s ? s[0] : ""
  const res = (i1 + i2).toUpperCase()
  return res || "U"
}

interface Props {
  /** URL actual del avatar (firmada o pública) */
  avatarUrl?: string | null
  /** 'pending' | 'approved' | 'rejected' */
  avatarStatus?: string | null
  /** Nota/razón del estado */
  avatarNote?: string | null
  /** Rutas Inertia para subir y borrar */
  uploadRoute: string
  deleteRoute: string
  /** (Opcional) polling cuando status === 'pending' */
  poll?: boolean
  pollMs?: number
  /** Tamaño del avatar (px) */
  size?: number
  /** Deshabilitar controles si no hay permiso */
  readonly?: boolean
  /** Para iniciales si no hay imagen */
  firstName?: string | null
  lastName?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  avatarUrl: null,
  avatarStatus: null,
  avatarNote: null,
  poll: true,
  pollMs: 3000,
  size: 128,
  readonly: false,
  firstName: null,
  lastName: null,
})

const emit = defineEmits<{
  (e: "uploaded"): void
  (e: "deleted"): void
}>()

// Iniciales para fallback
const initials = computed(() =>
  getUserInitials({ name: props.firstName, surnames: props.lastName })
)

// Form & preview
const avatarForm = useForm<{ avatar: File | null }>({ avatar: null })
const fileInputRef = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)
const isDeleting = ref(false)
const currentAvatarUrl = computed(() => previewUrl.value || props.avatarUrl || null)

function onFileChange(e: Event) {
  const f = (e.target as HTMLInputElement).files?.[0]
  if (!f) return
  avatarForm.avatar = f
  const reader = new FileReader()
  reader.onload = ev => (previewUrl.value = ev.target?.result as string)
  reader.readAsDataURL(f)
}

function resetFile() {
  previewUrl.value = null
  avatarForm.reset("avatar")
  if (fileInputRef.value) fileInputRef.value.value = ""
}

function submit() {
  if (!avatarForm.avatar) return
  avatarForm.post(props.uploadRoute, {
    preserveScroll: true,
    onSuccess: () => {
      resetFile()
      emit("uploaded")
      startPollingIfNeeded()
    },
  })
}

function remove() {
  if (!confirm("¿Eliminar foto de perfil?")) return
  isDeleting.value = true
  router.delete(props.deleteRoute, {
    preserveScroll: true,
    onFinish: () => {
      isDeleting.value = false
      resetFile()
      emit("deleted")
    },
  })
}

// Polling (si pending)
let interval: number | null = null
function stopPolling() {
  if (interval) { clearInterval(interval); interval = null }
}
function startPollingIfNeeded() {
  stopPolling()
  if (props.poll && props.avatarStatus === "pending") {
    interval = window.setInterval(() => {
      router.reload({ only: ["avatarUrl", "avatarStatus", "avatarNote"] })
    }, props.pollMs)
  }
}
onUnmounted(stopPolling)
startPollingIfNeeded()

// Estilos calculados
const fontSize = computed(() =>
  `${Math.max(12, Math.floor((props.size ?? 128) * 0.4))}px`
)
const boxStyle = computed(() => ({
  width: `${props.size}px`,
  height: `${props.size}px`,
}))
</script>

<template>
  <div class="flex items-start gap-4">
    <!-- Avatar + overlay -->
    <div class="relative" :style="boxStyle">
      <Avatar class="overflow-hidden rounded-full border-2 border-border" :style="boxStyle">
        <!-- Foto si hay URL (o preview) -->
        <AvatarImage
          v-if="currentAvatarUrl"
          :src="currentAvatarUrl"
          alt="Avatar"
          class="w-full h-full object-cover"
        />
        <!-- Iniciales si no hay imagen -->
        <AvatarFallback
          v-else
          class="w-full h-full grid place-items-center bg-muted text-foreground/80 font-semibold select-none"
          :style="{ fontSize }"
          :aria-label="'Iniciales ' + initials"
        >
          {{ initials }}
        </AvatarFallback>
      </Avatar>

      <!-- Botón overlay: subir (dentro de la imagen) -->
      <button
        type="button"
        class="absolute bottom-1 right-1 rounded-full bg-background/80 backdrop-blur border border-border shadow px-2.5 py-1 text-xs inline-flex items-center gap-1 hover:bg-background disabled:opacity-50"
        @click="fileInputRef?.click()"
        :disabled="avatarForm.processing || isDeleting || readonly"
      >
        <Icon icon="mdi:camera" class="w-4 h-4" />
        Subir
      </button>

      <!-- Estado pending -->
      <div
        v-if="avatarUrl && avatarStatus === 'pending'"
        class="absolute -bottom-1 -left-1 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-white shadow-md"
        :title="avatarNote || 'En validación'"
      >
        <Icon icon="line-md:loading-twotone-loop" class="h-4 w-4" />
      </div>

      <!-- Overlay de loading -->
      <div
        v-if="avatarForm.processing || isDeleting"
        class="absolute inset-0 flex items-center justify-center rounded-full bg-background/50"
      >
        <div class="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
      </div>

      <!-- input file oculto -->
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg,image/png,image/jpg,image/webp"
        class="hidden"
        id="avatar-input"
        @change="onFileChange"
        :disabled="readonly"
      />
    </div>

    <!-- Controles -->
    <div class="flex flex-col gap-2">
      <!-- Acciones de preview -->
      <div v-if="previewUrl" class="flex items-center gap-2">
        <button
          type="button"
          class="inline-flex items-center rounded-md bg-primary text-primary-foreground px-3 py-1.5 text-sm hover:opacity-90 disabled:opacity-50"
          @click="submit"
          :disabled="avatarForm.processing"
        >
          Guardar
        </button>
        <button
          type="button"
          class="inline-flex items-center rounded-md px-3 py-1.5 text-sm hover:bg-accent"
          @click="resetFile"
          :disabled="avatarForm.processing"
        >
          Cancelar
        </button>
      </div>

      <!-- Tips -->
    </div>
  </div>
</template>
