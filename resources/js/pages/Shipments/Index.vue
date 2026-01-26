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

interface Shipment {
    id: number;
    shipment_code: string;
    customer_id: number;
    supplier_name: string | null;
    weight: number | null;
    shelf_code: string | null;
    status: string;
    created_at: string;
    customer?: { id: number; full_name: string };
}

interface PaginatedData {
    data: Shipment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

defineProps<{
    shipments: PaginatedData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shipments',
        href: '/shipments',
    },
];

const confirmOpen = ref(false);
const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);

const deleteShipment = (id: number) => {
    deleting.value = id;
    confirmOpen.value = true;
};

const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;

    await router.delete(`/shipments/${deleting.value}`, {
        preserveScroll: true,
    });

    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};
</script>

<template>
    <Head title="Shipments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Shipments</h1>
                    <p class="text-muted-foreground">Manage shipments</p>
                </div>
                <Link href="/shipments/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Shipment
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Shipment List</CardTitle>
                    <CardDescription>View and manage shipments</CardDescription>
                </CardHeader>
                <CardContent class="pb-8">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th class="px-4 py-3 font-medium">Code</th>
                                    <th class="px-4 py-3 font-medium">Customer</th>
                                    <th class="px-4 py-3 font-medium">Supplier</th>
                                    <th class="px-4 py-3 font-medium">Weight</th>
                                    <th class="px-4 py-3 font-medium">Shelf</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Created</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in shipments.data" :key="s.id" class="border-b border-border hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">{{ s.shipment_code }}</td>
                                    <td class="px-4 py-3">{{ s.customer?.full_name || '-' }}</td>
                                    <td class="px-4 py-3">{{ s.supplier_name || '-' }}</td>
                                    <td class="px-4 py-3">{{ s.weight ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ s.shelf_code || '-' }}</td>
                                    <td class="px-4 py-3">{{ s.status }}</td>
                                    <td class="px-4 py-3">{{ new Date(s.created_at).toLocaleDateString() }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <Link :href="`/shipments/${s.id}/edit`">
                                            <Button variant="outline" size="sm"><Pencil class="h-4 w-4" /></Button>
                                        </Link>
                                        <Button variant="destructive" size="sm" @click="deleteShipment(s.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="shipments.data.length === 0" class="border-b border-border">
                                    <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No shipments found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="shipments.last_page > 1" class="flex items-center justify-between mt-6 pt-6 border-t border-border">
                        <div class="text-sm text-muted-foreground">Showing {{ shipments.data.length }} of {{ shipments.total }} shipments</div>
                        <div class="flex gap-2">
                            <Link v-for="link in shipments.links" :key="link.label" :href="link.url || '#'" :class="[
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
                        <DialogTitle>Delete shipment</DialogTitle>
                        <DialogDescription>Are you sure you want to delete this shipment? This action cannot be undone.</DialogDescription>
                    </DialogHeader>

                    <div class="text-sm">
                        <p><strong>Shipment:</strong> {{ (shipments.data.find((c) => c.id === deleting) || { shipment_code: '-' }).shipment_code }}</p>
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
