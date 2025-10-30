<script setup lang="ts">
import { watch, ref, computed } from 'vue';
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useDebounceFn } from '@vueuse/core';
import { route } from 'ziggy-js';

import { AddressFormService } from '@/services/AddressFormService';
import type { Address } from '@/types/Address';
import { TypeEnum } from '@/enums/addresses/type.enum';
import { useGooglePlaces, type AddressSuggestion } from '@/composables/useGooglePlaces';

// 🔸 Props polimórficas (REQUIRED)
const props = defineProps<{
    address?: Address;
    ownerId: number; // id del dueño (Tutor|Nanny|Booking)
    ownerType: string; // FQCN, ej: "App\\Models\\Tutor"
}>();

const emit = defineEmits<{
    (e: 'saved', payload?: any): void;
}>();

// Instancia del servicio (polimórfico)
const formService = new AddressFormService({
    address: props.address,
    ownerId: props.ownerId,
    ownerType: props.ownerType,
});

const { errors, loading, saved, values, setFieldValue } = formService;

// Two-flow toggle: 'search' or 'manual'
const activeFlow = ref<'search' | 'manual'>('search');

// Google Places autocomplete
const { loading: searchLoading, suggestions, searchPlaces, clearSuggestions } = useGooglePlaces();
const searchQuery = ref('');
const showSuggestions = ref(false);

// Manual flow state
const postalCodeData = ref<any>(null);
const loadingPostalCode = ref(false);
const coloniaOptions = ref<string[]>([]);
const postalCodeError = ref<string | null>(null);

// Debounced search for AWS
const debouncedSearch = useDebounceFn(async (query: string) => {
    if (query && query.length >= 3) {
        await searchPlaces(query);
        showSuggestions.value = true;
    } else {
        clearSuggestions();
        showSuggestions.value = false;
    }
}, 500);

// Handle AWS search input change
const onSearchInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchQuery.value = target.value;
    debouncedSearch(target.value);
};

// Select AWS suggestion
const selectSuggestion = (suggestion: AddressSuggestion) => {
    searchQuery.value = suggestion.fullAddress;

    // Auto-fill form fields
    setFieldValue('street', suggestion.street);
    setFieldValue('external_number', suggestion.external_number);
    setFieldValue('neighborhood', suggestion.neighborhood);
    setFieldValue('postal_code', suggestion.postalCode);
    setFieldValue('municipality', suggestion.municipality);
    setFieldValue('state', suggestion.state);
    setFieldValue('latitude', suggestion.latitude);
    setFieldValue('longitude', suggestion.longitude);

    // Clear suggestions
    showSuggestions.value = false;
    clearSuggestions();
};

// Handle postal code change in manual flow
const onPostalCodeChange = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const cp = target.value;

    // Reset colonia options
    coloniaOptions.value = [];
    postalCodeData.value = null;
    postalCodeError.value = null;

    // Clear municipality and state
    setFieldValue('municipality', '');
    setFieldValue('state', '');

    if (cp.length === 5 && /^\d{5}$/.test(cp)) {
        loadingPostalCode.value = true;
        try {
            const response = await fetch(route('postalcode.show', { postalCode: cp }));
            if (response.ok) {
                const data = await response.json();
                postalCodeData.value = data;
                coloniaOptions.value = data.colonias || [];

                // Auto-fill municipality and state
                setFieldValue('municipality', data.municipality || '');
                setFieldValue('state', data.state || '');
            } else {
                postalCodeError.value = 'Código postal no encontrado';
            }
        } catch (error) {
            console.error('Error fetching postal code:', error);
            postalCodeError.value = 'Error al buscar código postal';
        } finally {
            loadingPostalCode.value = false;
        }
    }
};

// Computed
const hasSuggestions = computed(() => suggestions.value.length > 0 && showSuggestions.value);
const isColoniaEnabled = computed(() => {
    const cp = values.postal_code;
    return cp && cp.length === 5 && /^\d{5}$/.test(cp);
});

// Cuando guarde/edite, notificar al padre
watch(
    () => saved.value,
    (ok) => {
        if (!ok) return;
        if (formService.savedAddress.value) {
            emit('saved', formService.savedAddress.value);
        } else {
            emit('saved', { ...values });
        }
    }
);

// Submit
const submit = async () => {
    if (props.address?.id) {
        await formService.updateAddress();
    } else {
        await formService.saveAddress();
    }
};
</script>

<template>
    <div>
        <!-- Tabs for two flows -->
        <div class="mb-6 border-b border-border">
            <div class="flex gap-4">
                <button
                    @click="activeFlow = 'search'"
                    :class="[
                        'pb-2 px-1 font-medium text-sm transition-colors',
                        activeFlow === 'search'
                            ? 'border-b-2 border-primary text-foreground'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    🔍 Buscar dirección
                </button>
                <button
                    @click="activeFlow = 'manual'"
                    :class="[
                        'pb-2 px-1 font-medium text-sm transition-colors',
                        activeFlow === 'manual'
                            ? 'border-b-2 border-primary text-foreground'
                            : 'text-muted-foreground hover:text-foreground',
                    ]"
                >
                    ✍️ Llenar manualmente
                </button>
            </div>
        </div>

        <!-- Google Places Search Flow -->
        <div v-if="activeFlow === 'search'" class="mb-6">
            <div class="relative">
                <Label>Buscar dirección (Google Maps)</Label>
                <Input :model-value="searchQuery" @input="onSearchInput" placeholder="Ej. Av. Vallarta 1234, Guadalajara" class="mt-1" />

                <!-- Loading indicator -->
                <div v-if="searchLoading" class="absolute right-3 top-10 text-muted-foreground">
                    <span class="text-xs">Buscando...</span>
                </div>

                <!-- Suggestions dropdown -->
                <div v-if="hasSuggestions" class="absolute z-50 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md">
                    <div class="max-h-60 overflow-y-auto p-1">
                        <button
                            v-for="(suggestion, index) in suggestions"
                            :key="index"
                            @click="selectSuggestion(suggestion)"
                            class="w-full cursor-pointer rounded-sm px-3 py-2 text-left text-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                        >
                            <div class="font-medium">{{ suggestion.label }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ suggestion.neighborhood }}, {{ suggestion.municipality }}, {{ suggestion.state }}
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            <p class="mt-2 text-xs text-muted-foreground">Escribe al menos 3 caracteres para buscar direcciones con Google Maps</p>
        </div>

        <!-- Manual Flow -->
        <div v-if="activeFlow === 'manual'" class="space-y-6">
            <div class="rounded-lg border border-border bg-muted/50 p-4">
                <p class="text-sm text-muted-foreground">
                    Complete los campos en orden. El código postal debe tener 5 dígitos para habilitar la selección de colonia.
                </p>
            </div>

            <!-- Postal Code (first, required) -->
            <FormField v-slot="{ componentField }" name="postal_code">
                <FormItem>
                    <Label>Código Postal *</Label>
                    <FormControl>
                        <Input
                            placeholder="Ej. 44100"
                            maxlength="5"
                            v-bind="componentField"
                            @input="onPostalCodeChange"
                            :disabled="loadingPostalCode"
                        />
                    </FormControl>
                    <FormMessage v-if="errors['postal_code']?.[0]">{{ errors['postal_code'][0] }}</FormMessage>
                    <p v-else-if="postalCodeError" class="text-sm text-destructive">{{ postalCodeError }}</p>
                    <p v-else-if="loadingPostalCode" class="text-sm text-muted-foreground">Buscando código postal...</p>
                    <p v-else class="text-sm text-muted-foreground">Ingrese el código postal de 5 dígitos</p>
                </FormItem>
            </FormField>

            <!-- Colonia (enabled only after valid CP) -->
            <FormField v-slot="{ componentField }" name="neighborhood">
                <FormItem>
                    <Label>Colonia *</Label>
                    <FormControl>
                        <Select v-if="coloniaOptions.length > 0" :model-value="componentField.value" @update:model-value="componentField.onChange">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecciona una colonia" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="colonia in coloniaOptions" :key="colonia" :value="colonia">
                                    {{ colonia }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-else placeholder="Colonia" v-bind="componentField" :disabled="!isColoniaEnabled" />
                    </FormControl>
                    <FormMessage v-if="errors['neighborhood']?.[0]">{{ errors['neighborhood'][0] }}</FormMessage>
                    <p v-else-if="!isColoniaEnabled" class="text-sm text-muted-foreground">Ingrese un código postal válido primero</p>
                </FormItem>
            </FormField>
        </div>

        <!-- Common fields (always visible) -->
        <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
            <!-- Calle -->
            <FormField v-slot="{ componentField }" name="street">
                <FormItem>
                    <Label>Calle *</Label>
                    <FormControl>
                        <Input placeholder="Ej. Av. Vallarta" v-bind="componentField" />
                    </FormControl>
                    <FormMessage>{{ errors['street']?.[0] }}</FormMessage>
                </FormItem>
            </FormField>

            <!-- Número Exterior -->
            <FormField v-slot="{ componentField }" name="external_number">
                <FormItem>
                    <Label>Número Exterior *</Label>
                    <FormControl>
                        <Input placeholder="Ej. 1234" v-bind="componentField" />
                    </FormControl>
                    <FormMessage>{{ errors['external_number']?.[0] }}</FormMessage>
                </FormItem>
            </FormField>

            <!-- Número Interno (optional) -->
            <FormField v-slot="{ componentField }" name="internal_number">
                <FormItem>
                    <Label>Número Interno</Label>
                    <FormControl>
                        <Input placeholder="Ej. 2A (opcional)" v-bind="componentField" />
                    </FormControl>
                    <FormMessage>{{ errors['internal_number']?.[0] }}</FormMessage>
                </FormItem>
            </FormField>

            <!-- Tipo de Dirección -->
            <FormField v-slot="{ componentField }" name="type">
                <FormItem>
                    <Label>Tipo de Dirección *</Label>
                    <FormControl>
                        <Select :model-value="componentField.value" @update:model-value="componentField.onChange">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecciona un tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(label, value) in TypeEnum.labels()" :key="String(value)" :value="String(value)">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </FormControl>
                    <FormMessage>{{ errors['type']?.[0] }}</FormMessage>
                </FormItem>
            </FormField>

            <!-- Municipality (read-only, auto-filled) -->
            <FormField v-slot="{ componentField }" name="municipality">
                <FormItem>
                    <Label>Municipio</Label>
                    <FormControl>
                        <Input placeholder="Auto-completado" v-bind="componentField" readonly class="bg-muted" />
                    </FormControl>
                </FormItem>
            </FormField>

            <!-- State (read-only, auto-filled) -->
            <FormField v-slot="{ componentField }" name="state">
                <FormItem>
                    <Label>Estado</Label>
                    <FormControl>
                        <Input placeholder="Auto-completado" v-bind="componentField" readonly class="bg-muted" />
                    </FormControl>
                </FormItem>
            </FormField>
        </div>

        <!-- Botones -->
        <div class="mt-6 flex justify-end gap-2">
            <Button @click="submit" :disabled="loading">
                <span v-if="loading">Guardando...</span>
                <span v-else>{{ props.address ? 'Actualizar' : 'Guardar' }}</span>
            </Button>
        </div>
    </div>
</template>
