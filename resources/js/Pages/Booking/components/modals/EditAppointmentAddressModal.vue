<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm as useInertiaForm } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'
import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Icon } from '@iconify/vue'
import { Badge } from '@/components/ui/badge'
import FormModal from '@/components/common/FormModal.vue'
import DeleteModal from '@/components/common/DeleteModal.vue'
import AddressForm from '@/Pages/Address/components/AddressForm.vue'
import { TypeEnum } from '@/enums/addresses/type.enum'
import type { Address } from '@/types/Address'
import type { BookingAppointment } from '@/types/BookingAppointment'
import type { Booking } from '@/types/Booking'

const props = defineProps<{
    appointment: BookingAppointment
    booking: Booking
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'saved'): void
}>()

// Get addresses from tutor (passed from controller)
const addresses = ref<Address[]>((props.booking.tutor as any)?.addresses ?? [])
const selectedAddressId = ref<number | null>(props.appointment.addresses?.[0]?.id ?? null)
const tutorIdNum = computed<number>(() => Number(props.booking.tutor?.id ?? 0))
const tutorType = 'App\\Models\\Tutor'

const isSelected = (id?: string | number | null) => id != null && Number(selectedAddressId.value) === Number(id)

// Form modal for creating/editing addresses
const showFormModal = ref(false)
const formTitle = ref('Agregar dirección')
const formAddress = ref<Address | null>(null)

function openCreate() {
    formTitle.value = 'Agregar dirección'
    formAddress.value = null
    showFormModal.value = true
}

function openEdit(a: Address) {
    formTitle.value = 'Editar dirección'
    formAddress.value = a
    showFormModal.value = true
}

function onAddressSaved(address: Address) {
    const i = addresses.value.findIndex((x) => String(x.id) === String(address.id))
    if (i === -1) {
        addresses.value.unshift(address)
    } else {
        addresses.value[i] = address
    }
    selectedAddressId.value = Number(address.id ?? 0)
    showFormModal.value = false
}

// Delete modal
const showDeleteModal = ref(false)
const toDelete = ref<Address | null>(null)

function openDelete(a: Address) {
    toDelete.value = a
    showDeleteModal.value = true
}

async function confirmDelete() {
    if (!toDelete.value?.id) return
    try {
        await axios.delete(route('addresses.destroy', { address: String(toDelete.value.id) }), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        addresses.value = addresses.value.filter((x) => String(x.id) !== String(toDelete.value?.id))
        if (Number(selectedAddressId.value) === Number(toDelete.value?.id)) selectedAddressId.value = null
    } finally {
        showDeleteModal.value = false
        toDelete.value = null
    }
}

// Submit form
const form = useInertiaForm({
    address_id: selectedAddressId.value,
})

function submit() {
    if (!selectedAddressId.value) {
        return
    }
    console.log('DEBUG EditAppointmentAddressModal - appointment:', props.appointment, 'id:', props.appointment?.id)
    form.address_id = selectedAddressId.value
    form.patch(route('bookings.appointments.update-address', { booking: props.booking.id, appointment: props.appointment.value.id }), {
        onSuccess: () => {
            // Backend redirects to bookings.show with openAppointmentId
            // Close modal and let Inertia follow the redirect to reload data
            emit('close')
        },
    })
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-muted-foreground">Selecciona una dirección del tutor o crea una nueva.</p>
            <Button size="sm" variant="outline" type="button" @click="openCreate">
                <Icon icon="lucide:plus" class="mr-1" /> Nueva
            </Button>
        </div>

        <ScrollArea>
            <div class="max-h-64 rounded border p-4">
                <div v-if="!addresses.length" class="text-sm text-muted-foreground text-center py-4">
                    No hay direcciones registradas.
                </div>

                <div v-else class="grid grid-cols-1 gap-3">
                    <div
                        v-for="a in addresses"
                        :key="a.id"
                        class="relative rounded-md border p-3 transition select-none bg-background/50"
                        :class="[
                            isSelected(a.id) ? 'ring-2 ring-primary border-primary bg-primary/5' : 'hover:border-primary/50',
                            'cursor-pointer',
                        ]"
                        role="button"
                        tabindex="0"
                        @click="selectedAddressId = Number(a.id)"
                        @keydown.enter.prevent="selectedAddressId = Number(a.id)"
                        @keydown.space.prevent="selectedAddressId = Number(a.id)"
                        :aria-pressed="isSelected(a.id)"
                    >
                        <span
                            v-if="isSelected(a.id)"
                            class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-medium text-primary"
                        >
                            <Icon icon="lucide:check-circle" class="h-3.5 w-3.5" /> Seleccionada
                        </span>

                        <div class="pr-16">
                            <div class="flex items-center gap-2">
                                <span class="font-medium truncate">{{ a.street }}</span>
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">{{ a.neighborhood }} • CP {{ a.postal_code }}</div>
                            <div class="mt-2">
                                <Badge variant="secondary" class="text-[11px]">{{ TypeEnum.labels()[a.type] || a.type }}</Badge>
                            </div>
                        </div>

                        <div class="absolute right-2 bottom-2 flex items-center gap-2">
                            <Button
                                size="icon"
                                variant="ghost"
                                class="h-8 w-8"
                                type="button"
                                @click.stop="openEdit(a)"
                                :aria-label="`Editar ${a.street}`"
                            >
                                <Icon icon="lucide:edit" class="h-4 w-4" />
                            </Button>
                            <Button
                                size="icon"
                                variant="destructive"
                                class="h-8 w-8"
                                type="button"
                                @click.stop="openDelete(a)"
                                :aria-label="`Eliminar ${a.street}`"
                            >
                                <Icon icon="lucide:trash" class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </ScrollArea>

        <p v-if="!selectedAddressId" class="text-xs text-rose-600">Selecciona una dirección.</p>
        <p v-if="form.errors.address_id" class="text-xs text-rose-600">{{ form.errors.address_id }}</p>

        <div class="flex justify-end gap-2 pt-4">
            <Button variant="outline" @click="emit('close')" :disabled="form.processing">Cancelar</Button>
            <Button @click="submit" :disabled="!selectedAddressId || form.processing">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>

        <!-- Nested modals -->
        <FormModal
            v-model="showFormModal"
            :title="formTitle"
            :form-component="AddressForm"
            :form-props="{
                address: formAddress,
                ownerId: tutorIdNum,
                ownerType: tutorType,
            }"
            @saved="onAddressSaved"
        />

        <DeleteModal
            v-model:show="showDeleteModal"
            title="dirección"
            :message="`¿Seguro que deseas eliminar la dirección ${toDelete?.street}?`"
            @confirm="confirmDelete"
        />
    </div>
</template>
