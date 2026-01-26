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

interface Customer {
    id: number;
    full_name: string;
    phone: string;
    whatsapp_number: string;
    customer_code: string;
    created_at: string;
}

interface PaginatedData {
    data: Customer[];
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
    customers: PaginatedData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: '/customers',
    },
];

const confirmOpen = ref(false);
const deleting = ref<number | null>(null);
const deletingProcessing = ref(false);

const deleteCustomer = (id: number) => {
    deleting.value = id;
    confirmOpen.value = true;
};

const confirmDelete = async () => {
    if (!deleting.value) return;
    deletingProcessing.value = true;

    await router.delete(`/customers/${deleting.value}`, {
        preserveScroll: true,
    });

    deletingProcessing.value = false;
    confirmOpen.value = false;
    deleting.value = null;
};
</script>

<template>
    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">Customers</h1>
                    <p class="text-muted-foreground">
                        Manage your customer database
                    </p>
                </div>
                <Link href="/customers/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add Customer
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Customer List</CardTitle>
                    <CardDescription>
                        View and manage all customers
                    </CardDescription>
                </CardHeader>
                <CardContent class="pb-8">
                    <div class="overflow-x-auto">
                        <table
                            class="w-full border-collapse text-left text-sm"
                        >
                            <thead>
                                <tr
                                    class="border-b border-border bg-muted/50"
                                >
                                    <th class="px-4 py-3 font-medium">
                                        Customer Code
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Full Name
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Phone
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        WhatsApp
                                    </th>
                                    <th class="px-4 py-3 font-medium">
                                        Created At
                                    </th>
                                    <th
                                        class="px-4 py-3 font-medium text-right"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="customer in customers.data"
                                    :key="customer.id"
                                    class="border-b border-border hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        {{ customer.customer_code || '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ customer.full_name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ customer.phone || '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ customer.whatsapp_number || '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{
                                            new Date(
                                                customer.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right space-x-2"
                                    >
                                        <Link
                                            :href="`/customers/${customer.id}/edit`"
                                        >
                                            <Button
                                                variant="outline"
                                                size="sm"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="deleteCustomer(customer.id)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr
                                    v-if="customers.data.length === 0"
                                    class="border-b border-border"
                                >
                                    <td
                                        colspan="6"
                                        class="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No customers found. Create your first
                                        customer to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="customers.last_page > 1"
                        class="flex items-center justify-between mt-6 pt-6 border-t border-border"
                    >
                        <div class="text-sm text-muted-foreground">
                            Showing {{ customers.data.length }} of
                            {{ customers.total }} customers
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in customers.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-1 rounded border',
                                    link.active
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'bg-background border-border hover:bg-muted',
                                    !link.url
                                        ? 'opacity-50 cursor-not-allowed'
                                        : '',
                                ]"
                                :disabled="!link.url"
                            >
                                {{ link.label.replace(/&laquo;|&raquo;/g, '') }}
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
            
            <!-- Delete confirmation dialog -->
            <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
                <DialogContent class="sm:max-w-md">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>Delete customer</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete this customer? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="text-sm">
                        <p>
                            <strong>Customer:</strong>
                            {{
                                (customers.data.find((c) => c.id === deleting) || { full_name: '-' }).full_name
                            }}
                        </p>
                    </div>

                    <DialogFooter class="gap-2 mt-4">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>

                        <Button
                            variant="destructive"
                            :disabled="deletingProcessing"
                            @click="confirmDelete"
                        >
                            {{ deletingProcessing ? 'Deleting...' : 'Delete' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
