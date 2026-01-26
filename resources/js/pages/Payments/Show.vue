<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{ payment: { id: number; customer?: { id:number; full_name:string }; reference_code: string; amount: number; status: string; approved_by?: { id:number; name:string } | null; created_at:string } }>();
</script>

<template>
    <Head :title="`Payment #${payment.id}`" />
    <AppLayout :breadcrumbs="[{ title: 'Payments', href: '/payments' }, { title: 'Payment #' + payment.id }]">
        <div class="p-4">
            <div class="grid gap-4">
                <div><strong>Customer:</strong> {{ payment.customer?.full_name || '-' }}</div>
                <div><strong>Reference:</strong> {{ payment.reference_code }}</div>
                <div><strong>Amount:</strong> {{ (Number.isFinite(Number(payment.amount)) ? Number(payment.amount).toFixed(2) : payment.amount) }}</div>
                <div><strong>Status:</strong> {{ payment.status }}</div>
                <div><strong>Approved By:</strong> {{ payment.approved_by?.name || '-' }}</div>
                <div><strong>Created:</strong> {{ payment.created_at }}</div>
            </div>
        </div>
    </AppLayout>
</template>
