<script setup lang="ts">
import { ref, computed } from "vue"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { ScrollArea } from "@/components/ui/scroll-area"
import { Icon } from "@iconify/vue"
import axios from "axios"
import { route } from "ziggy-js"

import FormModal from "@/components/common/FormModal.vue"
import DeleteModal from "@/components/common/DeleteModal.vue"
import ChildForm from "../partials/ChildForm.vue"

import { useBoundField } from "@/services/bookingFormService"
import type { Child, ChildInput } from "@/types/Child"
import type { Tutor } from "@/types/Tutor"
import { getKinshipLabelByString } from "@/enums/kinkship.enum"

import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"

const props = defineProps<{
  tutor: Tutor | null
  kinkships: string[]
  initialChildren?: Child[] | null
}>()

// --- Campos del form padre ---
const childIdsBF = useBoundField<string[]>("booking.child_ids")
const descBF     = useBoundField<string>("booking.description")

// --- Estado local ---
const children = ref<Child[]>(props.initialChildren ?? [])
const tutorId  = computed<string>(() => String(props.tutor?.id ?? ""))

// --- Util: calcular edad ---
function ageFrom(birthdate?: string | null): string {
  if (!birthdate) return "—"
  const [y, m, d] = birthdate.split("-").map(Number)
  if (!y || !m || !d) return "—"
  const today = new Date()
  let age = today.getFullYear() - y
  const mm = today.getMonth() + 1
  const dd = today.getDate()
  if (mm < m || (mm === m && dd < d)) age--
  return `${age} años`
}

// --- Selección (máximo 4) ---
const selectedIds = computed<string[]>({
  get: () => (childIdsBF.value.value ?? []).map(String),
  set: (v) => (childIdsBF.value.value = v),
})
const reachedLimit = computed(() => selectedIds.value.length >= 4)

const isSelected = (id?: string | number | null) =>
  id != null && selectedIds.value.includes(String(id))

function toggleSelect(id?: string | number | null) {
  if (id == null) return
  const sid = String(id)
  if (isSelected(sid)) {
    selectedIds.value = selectedIds.value.filter(x => x !== sid)
  } else {
    if (reachedLimit.value) return
    selectedIds.value = [...selectedIds.value, sid]
  }
}

// --- Modal Crear/Editar ---
const showFormModal = ref(false)
const formTitle     = ref("Agregar niño")
const formChild     = ref<ChildInput | null>(null)

function asChildInput(c?: Child | null): ChildInput {
  if (!c) {
    return {
      tutor_id: tutorId.value,
      name: "",
      birthdate: "",
      kinkship: (props.kinkships[0] ?? "") as any,
    }
  }
  return {
    id: c.id ? String(c.id) : undefined,
    tutor_id: String((c as any).tutor_id ?? tutorId.value),
    name: c.name ?? "",
    birthdate: c.birthdate ?? "",
    kinkship: (c as any).kinkship ?? (props.kinkships[0] ?? ""),
  }
}

function openCreate() {
  formTitle.value = "Agregar niño"
  formChild.value = asChildInput(null)
  showFormModal.value = true
}

function openEdit(c: Child) {
  formTitle.value = "Editar niño"
  formChild.value = asChildInput(c)
  showFormModal.value = true
}

// callbacks del ChildForm
function onChildSaved(child: Child) {
  const i = children.value.findIndex(x => String(x.id) === String(child.id))
  if (i === -1) {
    children.value.unshift(child)
    if (child.id && !reachedLimit.value) {
      selectedIds.value = [...selectedIds.value, String(child.id)]
    }
  } else {
    children.value[i] = child
  }
  showFormModal.value = false
}

// --- Modal Eliminar ---
const showDeleteModal = ref(false)
const toDelete = ref<Child | null>(null)

function openDelete(c: Child) {
  toDelete.value = c
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (!toDelete.value?.id) return
  try {
    await axios.delete(route("children.destroy", String(toDelete.value.id)), {
      headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
    })
    children.value = children.value.filter(x => String(x.id) !== String(toDelete.value?.id))
    selectedIds.value = selectedIds.value.filter(x => x !== String(toDelete.value?.id))
  } catch (e) {
    console.error(e)
  } finally {
    showDeleteModal.value = false
    toDelete.value = null
  }
}
</script>

<template>
  <Card class="border-none bg-transparent shadow-none">
    <CardHeader>
      <CardTitle class="flex items-center justify-between">
        <div class="flex flex-col gap-1">
          <div class="flex items-center gap-2">
            <Icon icon="lucide:users" /> Niños
          </div>
          <p class="text-xs text-muted-foreground max-w-md font-light italic">
            Selecciona entre 1 y 4 niños para la reserva. Si no aparece en la lista, agrégalo.
          </p>
        </div>

        <Button size="sm" variant="outline" @click="openCreate">
          <Icon icon="lucide:plus" /> Nuevo
        </Button>
      </CardTitle>
    </CardHeader>

    <CardContent class="space-y-4">
      <!-- Lista de niños como GRID -->
      <ScrollArea>
        <div class="max-h-64 rounded border p-6">
          <div v-if="!children.length" class="text-xs text-muted-foreground">
            No hay niños registrados.
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div
              v-for="c in children"
              :key="c.id"
              class="relative rounded-md border p-3 transition select-none"
              :class="[
                isSelected(c.id)
                  ? 'ring-2 ring-primary border-primary bg-primary/5'
                  : 'hover:border-primary/50',
                (!isSelected(c.id) && reachedLimit)
                  ? 'opacity-60 cursor-not-allowed'
                  : 'cursor-pointer'
              ]"
              role="button"
              tabindex="0"
              @click="toggleSelect(c.id)"
              @keydown.enter.prevent="toggleSelect(c.id)"
              @keydown.space.prevent="toggleSelect(c.id)"
              :aria-pressed="isSelected(c.id)"
              :title="(!isSelected(c.id) && reachedLimit) ? 'Límite de 4 alcanzado' : ''"
            >
              <span
                v-if="isSelected(c.id)"
                class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
              >
                <Icon icon="lucide:check-circle" class="h-3.5 w-3.5" />
                Seleccionado
              </span>

              <div class="pr-16">
                <div class="flex items-center gap-2">
                  <span class="font-medium truncate">{{ c.name }}</span>
                </div>
                <div class="mt-1 text-xs text-muted-foreground">
                  {{ getKinshipLabelByString((c as any).kinkship) }}
                  <span class="text-xs text-muted-foreground">• {{ ageFrom(c.birthdate) }}</span>
                </div>
              </div>

              <div class="absolute right-2 bottom-2 flex items-center gap-2">
                <Button size="icon" variant="ghost" class="h-8 w-8" @click.stop="openEdit(c)" :aria-label="`Editar a ${c.name}`">
                  <Icon icon="lucide:edit" class="h-4 w-4" />
                </Button>
                <Button size="icon" variant="destructive" class="h-8 w-8" @click.stop="openDelete(c)" :aria-label="`Eliminar a ${c.name}`">
                  <Icon icon="lucide:trash" class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <div class="mt-2 flex items-center justify-between">
            <p class="text-[11px] text-muted-foreground">
              Seleccionados: <span class="font-medium">{{ selectedIds.length }}</span> / 4
            </p>
            <p v-if="childIdsBF.errorMessage" class="text-[11px] text-rose-600">
              {{ childIdsBF.errorMessage }}
            </p>
          </div>
        </div>
      </ScrollArea>
            <!-- Descripción (requerida por el schema del paso 1) -->
      <div class="space-y-2">
        <Label for="booking-description" class="text-sm font-medium">Descripción</Label>
        <Textarea
          id="booking-description"
          v-model="descBF.value.value"
          class="resize-none"
          placeholder="Escribe una breve descripción del servicio…"
          rows="3"
        />
        <p v-if="descBF.errorMessage" class="text-[11px] text-rose-600">
          {{ descBF.errorMessage }}
        </p>
      </div>
    </CardContent>
  </Card>

  <!-- Modal Form (crear / editar) -->
  <FormModal
    v-model="showFormModal"
    :title="formTitle"
    :form-component="ChildForm"
    :form-props="{ kinkships, child: formChild }"
    @saved="onChildSaved"
  />

  <!-- Modal Delete -->
  <DeleteModal
    v-model:show="showDeleteModal"
    title="niño"
    :message="`¿Seguro que deseas eliminar a ${toDelete?.name}?`"
    @confirm="confirmDelete"
  />
</template>
