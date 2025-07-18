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
    <Card>
        <CardHeader>
            <CardTitle>{{ user.name }} {{ user.first_surname}} {{user.last_surname }}</CardTitle>
            <CardDescription>{{user.email}}</CardDescription>
            </CardHeader>
        <CardContent>
        </CardContent>
        <CardFooter>
        </CardFooter>
    </Card>
</template>
