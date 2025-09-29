<script setup lang="ts">
import { ref, computed } from "vue";
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete } from "@/components/ui/tags-input";
import { Combobox, ComboboxAnchor, ComboboxInput, ComboboxList, ComboboxEmpty, ComboboxGroup, ComboboxItem } from "@/components/ui/combobox";
import { QualityEnum } from "@/enums/quality.enum";
import { QualityFormService } from '@/services/qualityFormService';
import { Badge } from "@/components/ui/badge";

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

// Agregar tag
function selectTag(tag: string) {
  service.values.qualities ??= [];
  if (!service.values.qualities.includes(tag)) {
    service.values.qualities.push(tag);
  }
  searchTerm.value = "";
  open.value = false;
}

// Eliminar tag
function removeTag(tag: string) {
  if (service.values.qualities) {
    service.values.qualities = service.values.qualities.filter((t) => t !== tag);
  }
}
</script>

<template>
  <div class="card p-4 relative w-full max-w-md">
    <Combobox v-model="service.values.qualities" v-model:open="open" :ignore-filter="true" strategy="fixed">
      <ComboboxAnchor as-child>
        <TagsInput v-model="service.values.qualities" class="px-2 gap-2 w-full">
          <!-- Tags actuales con estilo Badge morado -->
          <div class="flex gap-2 flex-wrap items-center">
            <TagsInputItem v-for="item in service.values.qualities ?? []" :key="item" :value="item">
              <Badge class="bg-purple-200 text-purple-900 flex items-center gap-1 px-2 py-1 rounded">
                {{ item }}
                <TagsInputItemDelete @click="() => removeTag(item)" class="ml-1 cursor-pointer" />
              </Badge>
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
