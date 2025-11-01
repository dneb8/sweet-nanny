<script setup lang="ts">
import { FormControl, FormField, FormItem, FormMessage } from "@/components/ui/form"
import { Label } from "@/components/ui/label"
import { Input } from "@/components/ui/input"
import { Textarea } from "@/components/ui/textarea"
import { Button } from "@/components/ui/button"
import { watch } from "vue"
import { NannyProfileFormService } from "@/services/nannyProfileFormService"
import { Nanny } from "@/types/Nanny"

// Props
const props = defineProps<{ nanny: Nanny }>()
const emit = defineEmits(["saved"])

// Instanciamos el servicio
const formService = new NannyProfileFormService(props.nanny)

// Extraemos los valores reactivos del servicio
const { values, errors, loading, saved } = formService

// Watch para emitir evento al guardar
watch(() => saved.value, (value) => {
  if (value) emit("saved")
})

// Submit para actualizar
const submit = async () => {
  await formService.updateProfile()
}
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-8 mt-6">
    <!-- Biografía -->
    <FormField v-slot="{ componentField }" name="bio">
      <FormItem class="col-span-2">
        <Label>Biografía</Label>
        <FormControl>
          <Textarea
            placeholder="Escribe una breve biografía sobre ti y tu experiencia..."
            rows="4"
            v-bind="componentField"
            v-model="values.bio"
          />
        </FormControl>
        <FormMessage>
          {{ errors.bio ? errors.bio[0] : "" }}
        </FormMessage>
      </FormItem>
    </FormField>

    <!-- Fecha de inicio -->
    <FormField v-slot="{ componentField }" name="start_date">
      <FormItem>
        <Label>Fecha de inicio</Label>
        <FormControl>
          <Input
            type="date"
            v-bind="componentField"
            v-model="values.start_date"
          />
        </FormControl>
        <FormMessage>
          {{ errors.start_date ? errors.start_date[0] : "" }}
        </FormMessage>
      </FormItem>
    </FormField>
  </div>

  <!-- Botones -->
  <div class="mt-6 flex justify-end gap-2">
    <Button @click="submit" class="w-auto" :disabled="loading">
      <span v-if="loading">Guardando...</span>
      <span v-else>Actualizar</span>
    </Button>
  </div>
</template>
