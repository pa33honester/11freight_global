<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ user: any, roles: any }>();
const selected = ref<string[]>(props.user.role_names ? props.user.role_names : []);
const form = useForm({ roles: selected.value });

const roleForm = useForm({ name: '' });
const creating = ref(false);

// local mutable copy of roles so we can optimistically add new roles
const rolesLocal = ref(Array.isArray(props.roles) ? props.roles.slice() : []);

function submit() {
    form.post(`/admin/staff/${props.user.id}/roles`, { onSuccess: () => { /* noop */ } });
}

function createRole() {
    if (!roleForm.name) return;
    creating.value = true;
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch('/admin/roles', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ name: roleForm.name }),
    })
        .then(async (res) => {
            if (!res.ok) throw res;
            const role = await res.json();
            // append to local roles and select it
            rolesLocal.value.push(role);
            if (!form.roles.includes(role.name)) form.roles.push(role.name);
            roleForm.name = '';
        })
        .catch(async (err) => {
            let msg = 'Failed to create role.';
            try {
                const j = await err.json();
                if (j?.message) msg = j.message;
            } catch (_) {}
            alert(msg);
        })
        .finally(() => {
            creating.value = false;
        });
}
</script>

<template>
    <Head :title="`Staff - ${props.user.name}`" />
    <AppLayout :breadcrumbs="[{ title: 'Staff & Roles', href: '/admin/staff' }, { title: props.user.name, href: `/admin/staff/${props.user.id}` }]">
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>{{ props.user.name }}</CardTitle>
                    <CardDescription>{{ props.user.email }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="mb-4">
                        <h3 class="font-medium">Roles</h3>
                        <div class="space-y-2 mt-2">
                            <div v-for="r in rolesLocal" :key="r.id" class="flex items-center gap-2">
                                <input type="checkbox" :value="r.name" v-model="form.roles" />
                                <label>{{ r.name }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="font-medium">Create Role</h4>
                        <div class="flex items-center gap-2 mt-2">
                            <Input v-model="roleForm.name" placeholder="New role name" />
                            <Button :disabled="creating" @click.prevent="createRole">Create</Button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <Button @click.prevent="submit">Save Roles</Button>
                        <Link href="/admin/staff"><Button variant="ghost">Back</Button></Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
