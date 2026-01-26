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

interface Payment {
    id: number;
    customer?: { id: number; full_name: string };
    reference_code: string;
    amount: number;
    status: string;
    approved_by?: { id: number; name: string } | null;
    created_at: string;
}

interface PaginatedData {
    data: Payment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

defineProps<{ payments: PaginatedData }>();

const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);
const confirmOpen = ref(false);

const formatAmount = (amt: number | string | null | undefined) => {
    if (amt === null || amt === undefined || amt === '') return '-';
    const n = typeof amt === 'number' ? amt : parseFloat(String(amt));
    if (!isFinite(n)) return '-';
    return n.toFixed(2);
};

const deleteItem = (id: number) => { deleting.value = id; confirmOpen.value = true; };
const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;
    await router.delete(`/payments/${deleting.value}`, { preserveScroll: true });
    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};
</script>

<template>
    <Head title="Payments" />
    <AppLayout :breadcrumbs="[{ title: 'Payments', href: '/payments' }]">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Payments</h1>
                    <p class="text-muted-foreground">Customer payments</p>
                </div>
                <Link href="/payments/create">
                    <Button>Add Payment</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Payments</CardTitle>
                    <CardDescription>List of customer payments</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2">Customer</th>
                                    <th class="px-4 py-2">Reference</th>
                                    <th class="px-4 py-2">Amount</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in payments.data" :key="p.id" class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-2">{{ p.customer?.full_name || '-' }}</td>
                                    <td class="px-4 py-2">{{ p.reference_code }}</td>
                                    <td class="px-4 py-2">{{ formatAmount(p.amount) }}</td>
                                    <td class="px-4 py-2">{{ p.status }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <Link :href="`/payments/${p.id}`"><Button variant="outline" size="sm">View</Button></Link>
                                        <Link :href="`/payments/${p.id}/edit`"><Button variant="ghost" size="sm">Edit</Button></Link>
                                        <Button variant="destructive" size="sm" @click="deleteItem(p.id)">Delete</Button>
                                    </td>
                                </tr>
                                <tr v-if="payments.data.length === 0"><td colspan="5" class="p-6 text-center text-muted-foreground">No payments found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
            
            <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Delete payment</DialogTitle>
                        <DialogDescription>Are you sure you want to delete this payment? This action cannot be undone.</DialogDescription>
                    </DialogHeader>
                    <div class="mt-2 text-sm"><strong>Payment ID:</strong> {{ deleting || '-' }}</div>
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
