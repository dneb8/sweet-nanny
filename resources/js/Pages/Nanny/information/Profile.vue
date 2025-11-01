<script setup lang="ts">
import type { Nanny } from '@/types/Nanny'
import { Card } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogTrigger } from "@/components/ui/dialog"
import NannyProfileForm from '../components/NannyProfileForm.vue'
import { ref } from "vue"
import { formatExperienceFrom } from "@/utils/formatExperienceForm"

const props = defineProps<{ nanny: Nanny }>()

// Modal
const open = ref(false)

// Calcular % de completitud
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

// Experiencia calculada
const experience = ref(formatExperienceFrom(props.nanny.start_date ?? ''))

// Cuando se guarde el perfil
const handleSaved = () => {
  open.value = false
  // Refrescar datos si cambió start_date o bio
  experience.value = formatExperienceFrom(props.nanny.start_date ?? '')
}
</script>

<template>
  <Card class="p-6 flex flex-col md:flex-row items-center gap-6 bg-gradient-to-r from-pink-50 dark:from-pink-700/5 to-purple-50 dark:to-purple-700/5 border-none shadow-md">
    <img 
      src="https://randomuser.me/api/portraits/women/68.jpg" 
      alt="Foto de perfil" 
      class="w-28 h-28 rounded-sm border-4 border-white shadow-md object-cover"
    />

    <div class="flex-1 space-y-2 text-center md:text-left">
      <h1 class="text-2xl font-bold">
        {{ props.nanny.user?.name ?? 'Nanny sin nombre' }}
        {{ props.nanny.user?.surnames ?? '' }}
      </h1>

      <p class="text-muted-foreground">
        <span v-if="props.nanny.bio">{{ props.nanny.bio }}</span>
        <span v-else class="italic">Ninguna descripción aún</span>
      </p>

      <p class="text-sm text-gray-600">
        Experiencia: {{ experience }}
      </p>

      <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-2">
        <Badge v-if="completion !== 100" variant="secondary" class="px-3 py-1 text-sm">
          Perfil {{ completion }}% completado
        </Badge>

        <!-- ✨ Botón para abrir el modal -->
        <Dialog v-model:open="open">
          <DialogTrigger as-child>
            <Button variant="outline" size="sm">
              Editar perfil
            </Button>
          </DialogTrigger>

          <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
              <DialogTitle>Editar perfil de niñera</DialogTitle>
              <DialogDescription>
                Modifica tu biografía o fecha de inicio
              </DialogDescription>
            </DialogHeader>

            <!-- 🧁 Formulario -->
            <NannyProfileForm :nanny="props.nanny" @saved="handleSaved" />
          </DialogContent>
        </Dialog>
      </div>

      <Progress v-if="completion !== 100" v-model="completion" class="h-2 mt-4" />
    </div>
  </Card>
</template>
