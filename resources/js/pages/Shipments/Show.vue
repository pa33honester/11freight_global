<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';

interface Shipment {
    id: number;
    shipment_code: string;
    customer_id: number;
    supplier_name?: string;
    weight?: number;
    shelf_code?: string;
    status?: string;
    created_at: string;
    customer?: { id: number; full_name: string };
}

const props = defineProps<{
    shipment: Shipment;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shipments', href: '/shipments' },
    { title: 'Show', href: `/shipments/${props.shipment.id}` },
];
</script>

<template>
    <Head :title="`Shipment ${props.shipment.shipment_code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Shipment {{ props.shipment.shipment_code }}</CardTitle>
                    <CardDescription>Details</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div><strong>Customer:</strong> {{ props.shipment.customer?.full_name || '-' }}</div>
                        <div><strong>Supplier:</strong> {{ props.shipment.supplier_name || '-' }}</div>
                        <div><strong>Weight:</strong> {{ props.shipment.weight ?? '-' }}</div>
                        <div><strong>Shelf:</strong> {{ props.shipment.shelf_code || '-' }}</div>
                        <div><strong>Status:</strong> {{ props.shipment.status || '-' }}</div>
                        <div><strong>Created:</strong> {{ new Date(props.shipment.created_at).toLocaleString() }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
