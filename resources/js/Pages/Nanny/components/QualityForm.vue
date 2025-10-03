<script setup lang="ts">
import { ref, computed } from "vue";
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete } from "@/components/ui/tags-input";
import { Combobox, ComboboxAnchor, ComboboxInput, ComboboxList, ComboboxEmpty, ComboboxGroup, ComboboxItem } from "@/components/ui/combobox";
import { Badge } from "@/components/ui/badge";
import { QualityEnum } from "@/enums/quality.enum";
import { QualityFormService, NannyType } from "@/services/qualityFormService";

// Props
const props = defineProps<{ nanny: NannyType }>();

// Servicio que maneja la lógica y el guardado
const service = new QualityFormService(props.nanny);

const searchTerm = ref("");
const open = ref(false);

// Mensaje de estado
const statusMessage = ref<string | null>(null);
const statusSuccess = ref<boolean | null>(null);

// Computed: filtrar cualidades disponibles del enum que no estén ya seleccionadas
const filteredQualities = computed(() => {
  const current = service.values.qualities ?? [];
  const options = Object.values(QualityEnum).filter((q) => !current.includes(q));
  return searchTerm.value
    ? options.filter((q) => q.toLowerCase().includes(searchTerm.value.toLowerCase()))
    : options;
});

// Función para agregar tag
async function selectTag(tag: string) {
  service.addQuality(tag);
  searchTerm.value = "";
  open.value = false;

  const ok = await service.guardar();
  if (ok) {
    statusMessage.value = `Cualidad "${tag}" agregada ✅`;
    statusSuccess.value = true;
  } else {
    statusMessage.value = `Error al guardar al agregar etiqueta"${tag}" ❌`;
    statusSuccess.value = false;
  }
}

// Función para eliminar tag
async function removeTag(tag: string) {
  service.removeQuality(tag);

  const ok = await service.guardar();
  if (ok) {
    statusMessage.value = `Cualidad "${tag}" eliminada ✅`;
    statusSuccess.value = true;
  } else {
    statusMessage.value = `Error al guardar al eliminar etiqueta"${tag}" ❌`;
    statusSuccess.value = false;
  }
}

// Función de prueba manual
async function save() {
  const ok = await service.guardar();
  if (ok) {
    statusMessage.value = "Cualidades guardadas con éxito ✅";
    statusSuccess.value = true;
  } else {
    statusMessage.value = "Error al guardar ❌";
    statusSuccess.value = false;
  }
}
</script>

<template>
  <div class="card p-4 relative w-full max-w-md">
    <Combobox v-model="service.values.qualities" v-model:open="open" :ignore-filter="true" strategy="fixed">
      <ComboboxAnchor as-child>
        <TagsInput v-model="service.values.qualities" class="px-2 gap-2 w-full">
          <div class="flex gap-2 flex-wrap items-center">
            <TagsInputItem v-for="item in service.values.qualities ?? []" :key="item" :value="item">
              <Badge class="bg-purple-200 text-purple-900 flex items-center gap-1 px-2 py-1 rounded">
                {{ item }}
                <TagsInputItemDelete @click="() => removeTag(item)" class="ml-1 cursor-pointer" />
              </Badge>
            </TagsInputItem>
          </div>

          <ComboboxInput v-model="searchTerm" as-child>
            <TagsInputInput
              placeholder="Selecciona una cualidad..."
              class="flex-1 min-w-[150px] w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-300 h-10"
              @keydown.enter.prevent="() => {
                const match = filteredQualities.find((q: string) => q.toLowerCase() === searchTerm.value.toLowerCase());
                if (match) selectTag(match);
              }"
            />
          </ComboboxInput>

          <ComboboxList class="w-full mt-1 border border-gray-300 rounded max-h-40 overflow-auto bg-white shadow-lg z-50">
            <ComboboxEmpty>No hay resultados</ComboboxEmpty>
            <ComboboxGroup>
              <ComboboxItem
                v-for="quality in filteredQualities"
                :key="quality"
                :value="quality"
                @select.prevent="() => selectTag(quality)"
                class="cursor-pointer hover:bg-purple-100 px-2 py-1 rounded"
              >
                {{ quality }}
              </ComboboxItem>
            </ComboboxGroup>
          </ComboboxList>
        </TagsInput>
      </ComboboxAnchor>
    </Combobox>

    <!-- Botón de prueba manual -->
    <div class="mt-4 flex justify-end gap-4">
      <button
        @click="save"
        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700"
        :disabled="service.loading"
      >
        <span v-if="service.loading">Guardando...</span>
        <span v-else>Guardar</span>
      </button>

      <!-- Mensaje de estado visible -->
      <span
        v-if="statusMessage"
        :class="statusSuccess ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
      >
        {{ statusMessage }}
      </span>
    </div>
  </div>
</template>
