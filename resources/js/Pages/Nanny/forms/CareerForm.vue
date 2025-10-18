<script setup lang="ts">
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem
} from '@/components/ui/select'

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { watch } from 'vue'
import { CareerFormService } from '@/services/careerFormService'
import type { Nanny } from '@/types/Nanny'
import type { Career } from '@/types/Career'

// 1. Importar enum + función labels (con alias)
import { StatusEnum,    labels as statusLabels }    from '@/enums/careers/status.enum'
import { DegreeEnum,    labels as degreeLabels }    from '@/enums/careers/degree.enum'
import { NameCareerEnum,labels as nameCareerLabels } from '@/enums/careers/name_career.enum'

const props = defineProps<{
  career?: Career
  nanny: Nanny
}>()

const emit = defineEmits<{
  (e: 'saved'): void
}>()

const formService = new CareerFormService(props.nanny, props.career)
const { errors, loading, saved } = formService

watch(saved, value => value && emit('saved'))

const submit = () =>
  props.career?.id
    ? formService.updateCareer()
    : formService.saveCareer()
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Nombre carrera -->
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <Label>Nombre de la carrera</Label>
        <FormControl>
          <Select v-bind="componentField" :disabled="loading">
            <SelectTrigger>
              <SelectValue placeholder="Selecciona una carrera" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="(label, value) in nameCareerLabels()"
                :key="value"
                :value="value"
              >
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors.name?.[0] ?? '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Grado académico -->
    <FormField v-slot="{ componentField }" name="degree">
      <FormItem>
        <Label>Grado académico</Label>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Selecciona un grado" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="(label, value) in degreeLabels()"
                :key="value"
                :value="value"
              >
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors.degree?.[0] ?? '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Estatus -->
    <FormField v-slot="{ componentField }" name="status">
      <FormItem>
        <Label>Estatus</Label>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Selecciona un estatus" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="(label, value) in statusLabels()"
                :key="value"
                :value="value"
              >
                {{ label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage>{{ errors.status?.[0] ?? '' }}</FormMessage>
      </FormItem>
    </FormField>

    <!-- Institución -->
    <FormField v-slot="{ componentField }" name="institution">
      <FormItem>
        <Label>Institución</Label>
        <FormControl>
          <Input placeholder="Ej. UDG, UNAM, IPN" v-bind="componentField" />
        </FormControl>
        <FormMessage>{{ errors.institution?.[0] ?? '' }}</FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>{{ career ? 'Actualizar' : 'Guardar' }}</span>
    </Button>
  </div>
</template>