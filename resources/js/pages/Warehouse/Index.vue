<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Trash2, Pencil, Plus } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

const props = defineProps<{
    items: PaginatedData;
    customers: Array<{ id: number; name: string }>;
    filters?: { q?: string; shipment_id?: string; per_page?: number };
}>();

const page = usePage();
const pageProps = page.props.value as any;

const items = props.items;
const customers = props.customers;

const q = ref((pageProps?.filters?.q as string) ?? (props.filters?.q ?? ''));
const perPage = ref((pageProps?.filters?.per_page as number) ?? (props.filters?.per_page ?? props.items.per_page ?? 10));

const intakeOpen = ref(false);

const intakeForm = useForm<{
    customer_id: string;
    supplier_name: string;
    weight: string;
    photo: File | null;
}>({
    customer_id: '',
    supplier_name: '',
    weight: '',
    photo: null,
});

const intakeSubmitting = ref(false);

const intakeSubmit = async () => {
    if (!intakeForm.customer_id) {
        intakeForm.setError('customer_id', 'Please select a customer');
        return;
    }
    if (!intakeForm.supplier_name) {
        intakeForm.setError('supplier_name', 'Supplier name is required');
        return;
    }
    if (!intakeForm.weight) {
        intakeForm.setError('weight', 'Weight is required');
        return;
    }

    intakeSubmitting.value = true;
    try {
        intakeForm.post('/warehouse/intake', { preserveScroll: true, forceFormData: true });
        intakeForm.reset();
    } finally {
        intakeSubmitting.value = false;
        intakeOpen.value = false;
    }
};

const intakeHandleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    intakeForm.photo = target.files && target.files.length ? target.files[0] : null;
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Warehouse', href: '/warehouse' },
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
    await router.delete(`/warehouse/${deleting.value}`, { preserveScroll: true });
    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};

const applyFilters = () => {
    const params: Record<string, any> = {};
    if (q.value) params.q = q.value;
    if (perPage.value) params.per_page = perPage.value;

    router.get('/warehouse', params, { preserveState: true, replace: true });
};

const clearFilters = () => {
    q.value = '';
    perPage.value = props.items.per_page ?? 10;
    applyFilters();
};

// Live search debounce
let searchTimer: number | null = null;
watch(q, () => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => applyFilters(), 500);
});
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
                <Button @click="intakeOpen = true">
                    <Plus class="mr-2 h-4 w-4" />
                    Add Intake
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Inventory</CardTitle>
                    <CardDescription>Items received into warehouse</CardDescription>
                </CardHeader>
                <div class="p-4 flex gap-3 items-center">
                    <input type="search" v-model="q" @keyup.enter="applyFilters" placeholder="Search warehouse..." class="rounded border px-3 py-2 w-64" />

                    <select v-model.number="perPage" class="rounded border px-3 py-2">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>

                    <Button @click="applyFilters">Apply</Button>
                    <Button variant="outline" @click="clearFilters">Clear</Button>
                </div>

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
                                        <Link :href="`/warehouse/${it.id}/edit`">
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

            <Dialog :open="intakeOpen" @update:open="intakeOpen = $event">
                <DialogContent class="sm:max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>Warehouse Intake</DialogTitle>
                    </DialogHeader>

                    <div class="pt-2">
                        <div class="p-2 max-w-xl">
                            <div class="space-y-4">
                                <div class="flex flex-col space-y-2">
                                    <Label>Customer</Label>
                                    <select v-model="intakeForm.customer_id" class="w-full rounded border px-3 py-2">
                                        <option value="">Select customer</option>
                                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                    <p v-if="intakeForm.errors.customer_id" class="text-sm text-destructive mt-1">{{ intakeForm.errors.customer_id }}</p>
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <Label>Supplier Name</Label>
                                    <Input v-model="intakeForm.supplier_name" />
                                    <p v-if="intakeForm.errors.supplier_name" class="text-sm text-destructive mt-1">{{ intakeForm.errors.supplier_name }}</p>
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <Label>Weight</Label>
                                    <Input v-model="intakeForm.weight" type="number" />
                                    <p v-if="intakeForm.errors.weight" class="text-sm text-destructive mt-1">{{ intakeForm.errors.weight }}</p>
                                </div>

                                <div class="flex flex-col space-y-2">
                                    <Label>Photo</Label>
                                    <input type="file" accept="image/*" @change="intakeHandleFileChange" class="w-full rounded border px-3 py-2" />
                                    <p v-if="intakeForm.errors.photo" class="text-sm text-destructive mt-1">{{ intakeForm.errors.photo }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <DialogFooter>
                        <DialogClose as-child>
                            <Button :disabled="intakeSubmitting" @click.prevent="intakeSubmit">{{ intakeSubmitting ? 'Submitting...' : 'Receive Parcel' }}</Button>
                            <Button variant="secondary">Close</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

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
