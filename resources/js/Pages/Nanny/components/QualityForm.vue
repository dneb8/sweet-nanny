<script setup lang="ts">
import { ref, computed } from "vue";
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from "@/components/ui/tags-input";
import { Combobox, ComboboxAnchor, ComboboxInput, ComboboxList, ComboboxEmpty, ComboboxGroup, ComboboxItem } from "@/components/ui/combobox";
import { Icon } from "@iconify/vue";
import { QualityEnum } from "@/enums/quality.enum";
import { QualityFormService } from '@/services/qualityFormService';

// Props
const props = defineProps<{ nanny: any }>();

// Servicio que maneja la lógica y el auto-guardado
const service = new QualityFormService(props.nanny);

const searchTerm = ref("");
const open = ref(false);

// Computed: filtrar cualidades disponibles del enum que no estén ya seleccionadas
const filteredQualities = computed(() => {
  const current = service.values.qualities ?? [];
  const options = Object.values(QualityEnum).filter((q) => !current.includes(q));
  return searchTerm.value
    ? options.filter((q) => q.toLowerCase().includes(searchTerm.value.toLowerCase()))
    : options;
});

// Agregar tag (solo modifica el array, el watch del service auto-guarda)
function selectTag(tag: string) {
  service.values.qualities ??= [];
  if (!service.values.qualities.includes(tag)) {
    service.values.qualities.push(tag);
  }
  searchTerm.value = "";
  open.value = false;
}

// Eliminar tag (solo modifica el array, el watch del service auto-guarda)
function removeTag(tag: string) {
  if (service.values.qualities) {
    service.values.qualities = service.values.qualities.filter((t) => t !== tag);
  }
}
</script>

<template>
  <div class="card p-4 relative w-full max-w-md">
    <!-- Estado guardado -->
    <div class="absolute top-2 right-2 flex items-center gap-1 text-sm text-gray-600">
      <template v-if="service.loading">
        <Icon icon="codex:loader" class="animate-spin w-5 h-5" />
        <span>Guardando...</span>
      </template>
      <template v-else-if="service.saved">
        <Icon icon="line-md:confirm-circle" class="w-5 h-5 text-green-500" />
        <span>Guardado</span>
      </template>
    </div>

    <Combobox v-model="service.values.qualities" v-model:open="open" :ignore-filter="true" strategy="fixed">
      <ComboboxAnchor as-child>
        <TagsInput v-model="service.values.qualities" class="px-2 gap-2 w-full">
          <!-- Tags actuales -->
          <div class="flex gap-2 flex-wrap items-center">
            <TagsInputItem v-for="item in service.values.qualities ?? []" :key="item" :value="item">
              <TagsInputItemText />
              <TagsInputItemDelete @click="() => removeTag(item)" />
            </TagsInputItem>
          </div>

          <!-- Input para nuevas cualidades -->
          <ComboboxInput v-model="searchTerm" as-child>
            <TagsInputInput
              placeholder="Selecciona una cualidad..."
              class="flex-1 min-w-[150px] w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-purple-300 h-10"
              @keydown.enter.prevent="() => {
                const match = filteredQualities.find((q: string) => q.toLowerCase() === searchTerm.value.toLowerCase())
                if (match) selectTag(match)
              }"
            />
          </ComboboxInput>

          <!-- Lista de opciones filtradas -->
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
  </div>
</template>
