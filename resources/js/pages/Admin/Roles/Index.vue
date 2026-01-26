<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
// eslint-disable-next-line @typescript-eslint/no-unused-vars
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ roles: any }>();
const form = useForm({ name: '' });

function submit() {
    form.post('/admin/roles');
}

function destroy(id: number) {
    if (!confirm('Delete role?')) return;
    fetch(`/admin/roles/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } }).then(() => location.reload());
}
</script>

<template>
    <Head title="Roles" />
    <AppLayout :breadcrumbs="[{ title: 'Roles', href: '/admin/roles' }]">
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Roles</CardTitle>
                    <CardDescription>Manage roles</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4 flex gap-2">
                        <Input v-model="form.name" placeholder="Role name" />
                        <Button @click.prevent="submit">Create</Button>
                    </div>

                    <ul>
                        <li v-for="r in props.roles" :key="r.id" class="flex items-center justify-between py-2">
                            <div>{{ r.name }}</div>
                            <div><button class="btn btn-sm" @click.prevent="destroy(r.id)">Delete</button></div>
                        </li>
                        <li v-if="props.roles.length === 0" class="text-muted-foreground">No roles yet.</li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
