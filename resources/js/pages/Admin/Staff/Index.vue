<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ users: any }>();

const page = usePage();
const pageProps = page.props.value as { filters?: { q?: string; per_page?: number } };

const q = ref((pageProps?.filters?.q as string) ?? '');
const perPage = ref((pageProps?.filters?.per_page as number) ?? props.users.per_page ?? 20);

const applyFilters = () => {
    const params: Record<string, any> = {};
    if (q.value) params.q = q.value;
    if (perPage.value) params.per_page = perPage.value;
    router.get('/admin/staff', params, { preserveState: true, replace: true });
};

const clearFilters = () => {
    q.value = '';
    perPage.value = props.users.per_page ?? 20;
    applyFilters();
};

// live search debounce
let searchTimer: number | null = null;
watch(q, () => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => applyFilters(), 400);
});

const startIndex = computed(() => {
    const meta = props.users?.meta;
    if (meta?.from) return meta.from;
    if (meta?.current_page && meta?.per_page) return (meta.current_page - 1) * meta.per_page + 1;
    return 1;
});
</script>

<template>
    <Head title="Staff & Roles" />
    <AppLayout :breadcrumbs="[{ title: 'Staff & Roles', href: '/admin/staff' }]">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Staff & Roles</h1>
                    <p class="text-sm text-muted-foreground">List of application users and their assigned role (if any).</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Staff</CardTitle>
                    <CardDescription>Users and roles</CardDescription>
                </CardHeader>
                <div class="p-4 flex gap-3 items-center">
                    <input type="search" v-model="q" @keyup.enter="applyFilters" placeholder="Search users..." class="rounded border px-3 py-2 w-64" />

                    <select v-model.number="perPage" class="rounded border px-3 py-2">
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>

                    <Button @click="applyFilters">Apply</Button>
                    <Button variant="outline" @click="clearFilters">Clear</Button>
                </div>

                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2">#</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Role</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(u, i) in users.data" :key="u.id" class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-2">{{ startIndex + i }}</td>
                                    <td class="px-4 py-2">{{ u.name }}</td>
                                    <td class="px-4 py-2">{{ u.email }}</td>
                                    <td class="px-4 py-2">{{ u.role || '-' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <Link :href="`/admin/staff/${u.id}`"><Button variant="outline" size="sm">View</Button></Link>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0"><td colspan="5" class="p-6 text-center text-muted-foreground">No users found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
