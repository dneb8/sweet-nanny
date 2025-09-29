<script setup lang="ts">
import type { Nanny } from '@/types/Nanny';
import { reactive, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Icon } from '@iconify/vue';
import QualityForm from '@/Pages/Nanny/components/QualityForm.vue';

const props = defineProps<{ nanny: Nanny }>();

const nanny = reactive(props.nanny);

// Referencia al form para acceder a sus métodos si hace falta
const formRef = ref<InstanceType<typeof QualityForm>>();
</script>

<template>
  <Card class="bg-purple-50 dark:bg-purple-500/7 border-none shadow-sm relative">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" /> Cualidades
      </CardTitle>
    </CardHeader>

    <CardContent>
      <!-- Mostramos un título o mensaje si no hay cualidades -->
      <div v-if="!nanny.qualities || nanny.qualities.length === 0" class="flex flex-col items-center text-muted-foreground mb-4">
        <Icon icon="lucide:square" class="w-8 h-8 mb-2" />
        <span>No definidas</span>
      </div>

      <!-- Formulario de cualidades -->
      <QualityForm ref="formRef" :nanny="nanny" />
    </CardContent>
  </Card>
</template>
