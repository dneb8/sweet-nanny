<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button'
import { getRoleLabelByString } from "@/enums/role.enum";
import { User } from '@/types/User';
import { router, useForm } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { FormItem } from '@/components/ui/form';
// import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps<{
    user?: User;
    roles: Array<string>;
}>();


// Inicializar valores del formulario
const form = useForm({
    name: props.user?.name || '',
    surnames: props.user?.surnames || '',
    email: props.user?.email,
    number: props.user?.number || '',
    roles: props.user?.roles?.[0]?.name || '',
});

const submit = () => {

    if (props.user) {
        form.patch(route('users.update', props.user.ulid));

        return;
    }

    form.post(route('users.store'));
};

const cancelar = () => {
    router.get(route('users.index'));
}; 

</script>

<template>
    <form @submit.prevent="submit">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <FormItem>
                    <Label for="name">Nombre</Label>
                    <div class="flex flex-col w-full">
                        <Input id="name" v-model:model-value="form.name" type="text" placeholder="Ingresa el nombre" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                </FormItem>

                <FormItem>
                    <Label for="surnames">Apellidos</Label>
                    <div class="flex flex-col w-full">
                        <Input id="surnames" v-model:model-value="form.surnames" type="text" placeholder="Ingresa los apellidos" />
                        <InputError :message="form.errors.surnames" class="mt-2" />
                    </div>
                </FormItem>

                <FormItem class="sm:col-span-2 md:col-span-1">
                    <Label for="email">Correo Electrónico</Label>
                    <div class="flex flex-col w-full">
                        <Input id="email" v-model:model-value="form.email" type="text" placeholder="Ingresa el número" />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                </FormItem>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">

            <FormItem>
                <Label for="email">
                    Teléfono
                </Label>

                <div class="grid grid-cols-1 w-full">
                    <Input id="number" v-model:model-value="form.number"  type="tel" name="number" pattern="\d{10}" maxlength="10" required placeholder="Ingresa el número telefónico" />

                    <InputError :message="form.errors.number" class="mt-2" />
                </div>
            </FormItem>
            
            <FormItem label="Rol">
                <Label for="role">Rol</Label>
                <Select v-model="form.roles" id="role">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Selecciona un rol" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="role in roles" :key="role" :value="role">
                            {{ getRoleLabelByString(role) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.roles" class="mt-2" />
            </FormItem>
        </div>

        <div class="flex gap-4 mt-14 w-full sm:justify-end">
        <Button
            @click="submit"
            type="button"
            class="grow sm:grow-0"
        >
            {{ user ? 'Guardar' : 'Crear Usuario' }}
        </Button>

        <Button
            @click="cancelar"
            type="button"
            variant="outline"
            class="grow sm:grow-0"
        >
            Cancelar
        </Button>
        </div>
        
    </form>
</template>