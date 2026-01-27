<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
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

interface Settlement {
    id: number;
    payment?: { id: number } | null;
    supplier_name?: string | null;
    proof_path?: string | null;
    status: string;
    created_at: string;
}

interface PaginatedData {
    data: Settlement[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{ settlements: PaginatedData; viewSettlement?: Settlement | null }>();

const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);
const confirmOpen = ref(false);

const deleteItem = (id: number) => { deleting.value = id; confirmOpen.value = true; };
const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;
    await router.delete(`/supplier-settlements/${deleting.value}`, { preserveScroll: true });
    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};

// Modal view state
const viewOpen = ref(false);
const selectedSettlement = ref<Settlement | null>(null);

const openView = (s: Settlement) => {
    selectedSettlement.value = s;
    viewOpen.value = true;
};

// If server provided a settlement to view, open modal on mount
if (props.viewSettlement) {
    selectedSettlement.value = props.viewSettlement as Settlement;
    viewOpen.value = true;
}
</script>

<template>
    <Head title="Supplier Settlements" />
    <AppLayout :breadcrumbs="[{ title: 'Supplier Settlements', href: '/supplier-settlements' }]">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Supplier Settlements</h1>
                    <p class="text-muted-foreground">Trade facilitation supplier settlements</p>
                </div>
                <Link href="/supplier-settlements/create">
                    <Button>Add Settlement</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Settlements</CardTitle>
                    <CardDescription>List of supplier settlements</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2">Supplier</th>
                                    <th class="px-4 py-2">Payment</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="s in settlements.data" :key="s.id" class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-2">{{ s.supplier_name || '-' }}</td>
                                    <td class="px-4 py-2">{{ s.payment?.id || '-' }}</td>
                                    <td class="px-4 py-2">{{ s.status }}</td>
                                    <td class="px-4 py-2 text-right space-x-2">
                                        <Button class="cursor-pointer" variant="outline" size="sm" @click="openView(s)">View</Button>
                                        <Link :href="`/supplier-settlements/${s.id}/edit`"><Button class="cursor-pointer" variant="secondary" size="sm">Edit</Button></Link>
                                        <Button class="cursor-pointer" variant="destructive" size="sm" @click="deleteItem(s.id)">Delete</Button>
                                    </td>
                                </tr>
                                <tr v-if="settlements.data.length === 0"><td colspan="4" class="p-6 text-center text-muted-foreground">No settlements found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Delete settlement</DialogTitle>
                        <DialogDescription>Are you sure you want to delete this settlement? This action cannot be undone.</DialogDescription>
                    </DialogHeader>
                    <div class="mt-2 text-sm"><strong>Settlement ID:</strong> {{ deleting || '-' }}</div>
                    <DialogFooter class="gap-2 mt-4">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>
                        <Button variant="destructive" :disabled="deletingProcessing" @click="confirmDelete">{{ deletingProcessing ? 'Deleting...' : 'Delete' }}</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

                <!-- View modal -->
                <Dialog :open="viewOpen" @update:open="viewOpen = $event">
                    <DialogContent class="sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Supplier settlement</DialogTitle>
                            <DialogDescription>Details for supplier settlement</DialogDescription>
                        </DialogHeader>
                        <div class="mt-2 text-sm space-y-2">
                            <div><strong>Supplier:</strong> {{ selectedSettlement?.supplier_name || '-' }}</div>
                            <div><strong>Payment ID:</strong> {{ selectedSettlement?.payment?.id || '-' }}</div>
                            <div><strong>Status:</strong> {{ selectedSettlement?.status }}</div>
                            <div><strong>Proof Path:</strong> {{ selectedSettlement?.proof_path || '-' }}</div>
                            <div><strong>Created At:</strong> {{ selectedSettlement?.created_at }}</div>
                        </div>
                        <DialogFooter class="gap-2 mt-4">
                            <DialogClose as-child>
                                <Button variant="secondary">Close</Button>
                            </DialogClose>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
        </div>
    </AppLayout>
</template>
