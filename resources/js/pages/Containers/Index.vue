<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2, Pencil, Plus } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Container {
    id: number;
    container_code: string;
    status: string;
    departure_date: string | null;
    arrival_date: string | null;
    created_at: string;
}

interface PaginatedData {
    data: Container[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{
    containers: PaginatedData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Containers', href: '/containers' },
];

const confirmOpen = ref(false);
const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);

const deleteContainer = (id: number) => {
    deleting.value = id;
    confirmOpen.value = true;
};

const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;
    await router.delete(`/containers/${deleting.value}`, { preserveScroll: true });
    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};
</script>

<template>
    <Head title="Containers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Containers</h1>
                    <p class="text-muted-foreground">Manage containers</p>
                </div>
                <Link href="/containers/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Container
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Container List</CardTitle>
                    <CardDescription>View and manage containers</CardDescription>
                </CardHeader>
                <CardContent class="pb-8">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th class="px-4 py-3 font-medium">Code</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Departure</th>
                                    <th class="px-4 py-3 font-medium">Arrival</th>
                                    <th class="px-4 py-3 font-medium">Created</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in containers.data" :key="c.id" class="border-b border-border hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">{{ c.container_code }}</td>
                                    <td class="px-4 py-3">{{ c.status }}</td>
                                    <td class="px-4 py-3">{{ c.departure_date || '-' }}</td>
                                    <td class="px-4 py-3">{{ c.arrival_date || '-' }}</td>
                                    <td class="px-4 py-3">{{ new Date(c.created_at).toLocaleDateString() }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <Link :href="`/containers/${c.id}/edit`">
                                            <Button variant="outline" size="sm"><Pencil class="h-4 w-4" /></Button>
                                        </Link>
                                        <Button variant="destructive" size="sm" @click="deleteContainer(c.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="containers.data.length === 0" class="border-b border-border">
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No containers found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="containers.last_page > 1" class="flex items-center justify-between mt-6 pt-6 border-t border-border">
                        <div class="text-sm text-muted-foreground">Showing {{ containers.data.length }} of {{ containers.total }} containers</div>
                        <div class="flex gap-2">
                            <Link v-for="link in containers.links" :key="link.label" :href="link.url || '#'" :class="[
                                'px-3 py-1 rounded border',
                                link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background border-border hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
                            ]" :disabled="!link.url">{{ link.label.replace(/&laquo;|&raquo;/g, '') }}</Link>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>Delete container</DialogTitle>
                        <DialogDescription>Are you sure you want to delete this container? This action cannot be undone.</DialogDescription>
                    </DialogHeader>

                    <div class="text-sm">
                        <p><strong>Container:</strong> {{ (containers.data.find((x) => x.id === deleting) || { container_code: '-' }).container_code }}</p>
                    </div>

                    <DialogFooter class="gap-2 mt-4">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>

                        <Button variant="destructive" :disabled="deletingProcessing" @click="confirmDelete">{{ deletingProcessing ? 'Deleting...' : 'Delete' }}</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
