<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'
import { Icon } from '@iconify/vue'
import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import ChildForm from '@/Pages/Booking/partials/ChildForm.vue'
import type { Child, ChildInput } from '@/types/Child'
import type { BookingAppointment } from '@/types/BookingAppointment'
import type { Booking } from '@/types/Booking'
import { getKinshipLabelByString } from '@/enums/kinkship.enum'

const props = defineProps<{
    appointment: BookingAppointment
    booking: Booking
    kinkships: string[]
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'saved'): void
}>()

// Get children from tutor (passed from controller)
const children = ref<Child[]>((props.booking.tutor as any)?.children ?? [])
const selectedIds = ref<number[]>(props.appointment.children?.map((c) => Number(c.id)) ?? [])
const tutorId = computed<number | null>(() => Number(props.booking.tutor?.id ?? 0))

// Age calculation
function ageFrom(birthdate?: string | null): string {
    if (!birthdate) return '—'
    const [y, m, d] = birthdate.split('-').map(Number)
    if (!y || !m || !d) return '—'
    const today = new Date()
    let age = today.getFullYear() - y
    const mm = today.getMonth() + 1
    const dd = today.getDate()
    if (mm < m || (mm === m && dd < d)) age--
    return `${age} años`
}

const reachedLimit = computed(() => selectedIds.value.length >= 4)
const isSelected = (id?: string | number | null) => id != null && selectedIds.value.includes(Number(id))

function toggleSelect(id?: string | number | null) {
    if (id == null) return
    const nid = Number(id)
    if (isSelected(nid)) {
        selectedIds.value = selectedIds.value.filter((x) => x !== nid)
    } else {
        if (reachedLimit.value) return
        selectedIds.value = [...selectedIds.value, nid]
    }
}

// Form modal for creating/editing children
const showFormModal = ref(false)
const formTitle = ref('Agregar niño')
const formChild = ref<ChildInput | null>(null)

function asChildInput(c?: Child | null): ChildInput {
    if (!c) {
        return {
            tutor_id: String(tutorId.value ?? ''),
            name: '',
            birthdate: '',
            kinkship: (props.kinkships[0] ?? '') as any,
        }
    }
    return {
        id: c.id ? String(c.id) : undefined,
        ulid: c.ulid ? String(c.ulid) : undefined,
        tutor_id: String((c as any).tutor_id ?? (tutorId.value ?? '')),
        name: c.name ?? '',
        birthdate: c.birthdate ?? '',
        kinkship: (c as any).kinkship ?? (props.kinkships[0] ?? ''),
    }
}

function openCreate() {
    formTitle.value = 'Agregar niño'
    formChild.value = asChildInput(null)
    showFormModal.value = true
}

function openEdit(c: Child) {
    formTitle.value = 'Editar niño'
    formChild.value = asChildInput(c)
    showFormModal.value = true
}

function onChildSaved(child: Child) {
    const i = children.value.findIndex((x) => Number(x.id) === Number(child.id))
    if (i === -1) {
        children.value.unshift(child)
        if (child.id && !reachedLimit.value) selectedIds.value = [...selectedIds.value, Number(child.id)]
    } else {
        children.value[i] = child
    }
    showFormModal.value = false
}

// Delete modal
const showDeleteModal = ref(false)
const toDelete = ref<Child | null>(null)

function openDelete(c: Child) {
    toDelete.value = c
    showDeleteModal.value = true
}

async function confirmDelete() {
    const key = toDelete.value?.ulid ?? toDelete.value?.id
    if (!key) return
    try {
        await axios.delete(route('children.destroy', { child: String(key) }), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        children.value = children.value.filter((x) => Number(x.id) !== Number(toDelete.value?.id))
        selectedIds.value = selectedIds.value.filter((x) => x !== Number(toDelete.value?.id))
    } finally {
        showDeleteModal.value = false
        toDelete.value = null
    }
}

// Submit form
const form = useInertiaForm({
    child_ids: selectedIds.value,
})

function submit() {
    if (selectedIds.value.length === 0) {
        return
    }
    form.child_ids = selectedIds.value
    form.patch(route('bookings.appointments.update-children', { booking: props.booking.id, appointment: props.appointment.id }), {
        onSuccess: () => {
            // Backend now handles redirect with openAppointmentId
            // Just close the modal, don't call emit('saved') to avoid double navigation
            emit('close')
        },
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-muted-foreground">Selecciona entre 1 y 4 niños. Si no aparece, agrégalo.</p>
            <Button size="sm" variant="outline" type="button" @click="openCreate">
                <Icon icon="lucide:plus" class="mr-1" /> Agregar niño
            </Button>
        </div>

        <ScrollArea>
            <div class="max-h-64 rounded border p-4">
                <div v-if="!children.length" class="text-sm text-muted-foreground text-center py-4">
                    No hay niños registrados.
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        v-for="c in children"
                        :key="c.ulid ?? c.id"
                        class="relative rounded-md border p-3 transition select-none bg-background/50"
                        :class="[
                            isSelected(c.id) ? 'ring-2 ring-primary border-primary bg-primary/5' : 'hover:border-primary/50',
                            !isSelected(c.id) && reachedLimit ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer',
                        ]"
                        role="button"
                        tabindex="0"
                        @click="toggleSelect(c.id)"
                        @keydown.enter.prevent="toggleSelect(c.id)"
                        @keydown.space.prevent="toggleSelect(c.id)"
                        :aria-pressed="isSelected(c.id)"
                        :title="!isSelected(c.id) && reachedLimit ? 'Límite de 4 alcanzado' : ''"
                    >
                        <span
                            v-if="isSelected(c.id)"
                            class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
                        >
                            <Icon icon="lucide:check-circle" class="h-3.5 w-3.5" /> Seleccionado
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
                            <Button
                                size="icon"
                                variant="ghost"
                                class="h-8 w-8"
                                @click.stop="openEdit(c)"
                                type="button"
                                :aria-label="`Editar a ${c.name}`"
                            >
                                <Icon icon="lucide:edit" class="h-4 w-4" />
                            </Button>
                            <Button
                                size="icon"
                                variant="destructive"
                                class="h-8 w-8"
                                @click.stop="openDelete(c)"
                                type="button"
                                :aria-label="`Eliminar a ${c.name}`"
                            >
                                <Icon icon="lucide:trash" class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <p class="text-[11px] text-muted-foreground">
                        Seleccionados: <span class="font-medium">{{ selectedIds.length }}</span> / 4
                    </p>
                </div>
            </div>
        </ScrollArea>

        <p v-if="selectedIds.length === 0" class="text-xs text-rose-600">Selecciona al menos un niño.</p>
        <p v-if="form.errors.child_ids" class="text-xs text-rose-600">{{ form.errors.child_ids }}</p>

        <div class="flex justify-end gap-2 pt-4">
            <Button variant="outline" @click="emit('close')" :disabled="form.processing">Cancelar</Button>
            <Button @click="submit" :disabled="selectedIds.length === 0 || form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>

        <!-- Nested modals -->
        <FormModal
            v-model="showFormModal"
            :title="formTitle"
            :form-component="ChildForm"
            :form-props="{ kinkships, child: formChild }"
            @saved="onChildSaved"
        />

        <DeleteModal
            v-model:show="showDeleteModal"
            title="niño"
            :message="`¿Seguro que deseas eliminar a ${toDelete?.name}?`"
            @confirm="confirmDelete"
        />
    </div>
</template>
