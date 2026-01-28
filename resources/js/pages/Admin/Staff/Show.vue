<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ user: any, roles: any }>();
// single selected role — default to first existing role if present
const selectedSingle = ref<string>(props.user.role_names && props.user.role_names.length ? props.user.role_names[0] : '');
const form = useForm({ roles: selectedSingle.value ? [selectedSingle.value] : [] });

const roleForm = useForm({ name: '' });
const creating = ref(false);

const successMsg = ref('');
const errorMsgs = ref<string[]>([]);
let successTimer: number | null = null;
let errorTimer: number | null = null;

function showSuccess(msg: string) {
    successMsg.value = msg;
    if (successTimer) window.clearTimeout(successTimer);
    successTimer = window.setTimeout(() => { successMsg.value = ''; successTimer = null; }, 4000);
}

function showErrors(arr: string[]) {
    errorMsgs.value = arr;
    if (errorTimer) window.clearTimeout(errorTimer);
    errorTimer = window.setTimeout(() => { errorMsgs.value = []; errorTimer = null; }, 6000);
}

// local mutable copy of roles so we can optimistically add new roles
const rolesLocal = ref(Array.isArray(props.roles) ? props.roles.slice() : []);

function submit() {
    successMsg.value = '';
    errorMsgs.value = [];
    form.post(`/admin/staff/${props.user.id}/roles`, {
        onSuccess: () => {
            showSuccess('Roles updated.');
            errorMsgs.value = [];
        },
        onError: (errs) => {
            // errs is an object of field -> messages
            try {
                const arr: string[] = [];
                Object.values(errs).forEach((v: any) => {
                    if (Array.isArray(v)) arr.push(...v);
                    else if (typeof v === 'string') arr.push(v);
                });
                showErrors(arr.length ? arr : ['Failed to update roles.']);
            } catch (e: any) {
                console.log(e);
                showErrors(['Failed to update roles.']);
            }
        }
    });
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
                    // append to local roles and select it (single role)
                    rolesLocal.value.push(role);
                    form.roles = [role.name];
                    selectedSingle.value = role.name;
                    roleForm.name = '';
        })
        .catch(async (err) => {
            let msg = 'Failed to create role.';
            try {
                const j = await err.json();
                if (j?.message) msg = j.message;
            } catch (e) {
                console.log(e);
            }
            alert(msg);
        })
        .finally(() => {
            creating.value = false;
        });
}

function setRole(name: string) {
    selectedSingle.value = name;
    form.roles = [name];
}
</script>

<template>
    <Head :title="`Staff - ${props.user.name}`" />
    <AppLayout :breadcrumbs="[{ title: 'Staff & Roles', href: '/admin/staff' }, { title: props.user.name, href: `/admin/staff/${props.user.id}` }]">
        <!-- Toast container (top-right) -->
        <div class="fixed top-6 right-6 z-50 flex flex-col items-end space-y-3">
            <transition name="fade">
                <Alert v-if="successMsg" class="w-96 bg-gradient-to-r from-emerald-500 to-emerald-400 text-white shadow-lg ring-1 ring-emerald-300">
                    <AlertTitle class="text-white">Success</AlertTitle>
                    <AlertDescription class="text-white">{{ successMsg }}</AlertDescription>
                </Alert>
            </transition>

            <transition name="fade">
                <Alert v-if="errorMsgs.length" variant="destructive" class="w-96 bg-gradient-to-r from-rose-600 to-rose-500 text-white shadow-lg ring-1 ring-rose-400">
                    <AlertTitle class="text-white">Failed</AlertTitle>
                    <AlertDescription class="text-white">
                        <ul class="list-inside list-disc text-sm">
                            <li v-for="(e, i) in errorMsgs" :key="i">{{ e }}</li>
                        </ul>
                    </AlertDescription>
                </Alert>
            </transition>
        </div>

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
                                <input
                                    type="radio"
                                    :id="`role-${r.id}`"
                                    :value="r.name"
                                    v-model="selectedSingle"
                                    @change="() => setRole(r.name)"
                                    class="form-radio"
                                />
                                <label :for="`role-${r.id}`">{{ r.name }}</label>
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
                        <Button :disabled="form.processing" @click.prevent="submit">Save Roles</Button>
                        <Link href="/admin/staff"><Button variant="ghost">Back</Button></Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Enhanced fade/slide toast animation */
.fade-enter-active {
    transition: transform 360ms cubic-bezier(0.2, 0.8, 0.2, 1), opacity 300ms ease;
}
.fade-leave-active {
    transition: transform 260ms cubic-bezier(0.4, 0.0, 0.2, 1), opacity 220ms ease;
}
.fade-enter-from {
    opacity: 0;
    transform: translateY(-12px) scale(0.985);
}
.fade-enter-to {
    opacity: 1;
    transform: translateY(0) scale(1);
}
.fade-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}
.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.99);
}
</style>
