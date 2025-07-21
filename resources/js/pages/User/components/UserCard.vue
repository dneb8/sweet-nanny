<!-- components/UserCard.vue -->
<script setup lang="ts">
import type { User } from '@/types/User'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'

defineProps<{
  user: User
  columns: string[] // Ej. ['name', 'email', 'roles']
}>()
</script>

<template>
  <Card class="max-h-48">
    <CardHeader>
      <CardTitle>{{ user.name }} {{ user.first_surname }} {{ user.last_surname }}</CardTitle>
      <CardDescription class="hyphens-manual">{{ user.email }}</CardDescription>
    </CardHeader>
    <CardContent>
      <!-- contenido dinámico aquí -->
    </CardContent>
    <CardFooter>
      <!-- footer si es necesario -->
    </CardFooter>
  </Card>
</template>

