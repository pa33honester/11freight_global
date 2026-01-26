<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Trash2, Pencil, Plus } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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

interface Item {
    id: number;
    shipment?: { id: number; shipment_code: string };
    shelf?: string | null;
    photo_path?: string | null;
    photo_url?: string | null;
    intake_by?: { id: number; name: string } | null;
    intake_time: string;
    created_at: string;
}

interface PaginatedData {
    data: Item[];
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

const { items } = defineProps<{
    items: PaginatedData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Warehouse Inventory', href: '/warehouse-inventory' },
];

const confirmOpen = ref(false);
const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);

const deletingLabel = computed(() => {
    const id = deleting.value;
    if (id === null) return '-';
    const found = items.data.find((x) => x.id === id);
    return found?.shipment?.shipment_code ?? '-';
});

const deleteItem = (id: number) => {
    deleting.value = id;
    confirmOpen.value = true;
};

const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;
    await router.delete(`/warehouse-inventory/${deleting.value}`, { preserveScroll: true });
    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};
</script>

<template>
    <Head title="Warehouse Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Warehouse Inventory</h1>
                    <p class="text-muted-foreground">Manage items in the warehouse</p>
                </div>
                <Link href="/warehouse-inventory/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Inventory</CardTitle>
                    <CardDescription>Items received into warehouse</CardDescription>
                </CardHeader>
                <CardContent class="pb-8">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th class="px-4 py-3 font-medium">Shipment</th>
                                    <th class="px-4 py-3 font-medium">Shelf</th>
                                    <th class="px-4 py-3 font-medium">Photo</th>
                                    <th class="px-4 py-3 font-medium">Intake By</th>
                                    <th class="px-4 py-3 font-medium">Intake Time</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="it in items.data" :key="it.id" class="border-b border-border hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">{{ it.shipment?.shipment_code || '-' }}</td>
                                    <td class="px-4 py-3">{{ it.shelf || '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div v-if="it.photo_url">
                                            <img :src="it.photo_url" class="h-12 w-auto rounded" alt="photo" />
                                        </div>
                                        <div v-else>{{ it.photo_path ? 'View' : '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ it.intake_by?.name || '-' }}</td>
                                    <td class="px-4 py-3">{{ new Date(it.intake_time || it.created_at).toLocaleString() }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <Link :href="`/warehouse-inventory/${it.id}/edit`">
                                            <Button variant="outline" size="sm"><Pencil class="h-4 w-4" /></Button>
                                        </Link>
                                        <Button variant="destructive" size="sm" @click="deleteItem(it.id)"><Trash2 class="h-4 w-4" /></Button>
                                    </td>
                                </tr>
                                <tr v-if="items.data.length === 0" class="border-b border-border">
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No items found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="items.last_page > 1" class="flex items-center justify-between mt-6 pt-6 border-t border-border">
                        <div class="text-sm text-muted-foreground">Showing {{ items.data.length }} of {{ items.total }} items</div>
                        <div class="flex gap-2">
                            <Link v-for="link in items.links" :key="link.label" :href="link.url || '#'" :class="[
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
                        <DialogTitle>Delete item</DialogTitle>
                        <DialogDescription>Are you sure you want to remove this item from inventory? This action cannot be undone.</DialogDescription>
                    </DialogHeader>

                    <div class="text-sm">
                        <p><strong>Item:</strong> {{ deletingLabel }}</p>
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
