<script setup lang="ts">
import type { Nanny } from '@/types/Nanny';
import { reactive } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Icon } from '@iconify/vue';
import QualityForm from '@/Pages/Nanny/components/QualityForm.vue';

const props = defineProps<{ nanny: Nanny }>();
const nanny = reactive(props.nanny); // badges se actualizan automáticamente
</script>

<template>
  <Card class="bg-purple-50 dark:bg-purple-500/7 border-none shadow-sm relative">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <Icon icon="lucide:sparkles" /> Cualidades
      </CardTitle>
    </CardHeader>

    <CardContent>
      <div v-if="nanny.qualities?.length" class="flex flex-wrap gap-2 mb-4">
        <Badge v-for="quality in nanny.qualities" :key="quality.id" class="bg-purple-200 text-purple-900">
          {{ quality.name }}
        </Badge>
      </div>

      <div v-else class="flex flex-col items-center text-muted-foreground mb-4">
        <Icon icon="lucide:square" class="w-8 h-8 mb-2" />
        <span>No definidas</span>
      </div>

      <QualityForm :nanny="nanny" />
    </CardContent>
  </Card>
</template>
