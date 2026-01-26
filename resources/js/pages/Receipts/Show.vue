<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{ receipt: any }>();
</script>

<template>
    <Head :title="`Receipt #${receipt.id}`" />
    <AppLayout :breadcrumbs="[{ title: 'Receipts', href: '/receipts' }, { title: `Receipt #${receipt.id}` }]">
        <div class="p-4">
            <div class="grid gap-4">
                <div><strong>Number:</strong> {{ receipt.receipt_number }}</div>
                <div><strong>Type:</strong> {{ receipt.type }}</div>
                <div><strong>Linked ID:</strong> {{ receipt.linked_id || '-' }}</div>
                <div><strong>Created:</strong> {{ receipt.created_at }}</div>
                <div><strong>QR:</strong>
                    <div v-if="receipt.qr_code">
                        <div v-html="receipt.qr_code"></div>
                    </div>
                    <div v-else>-</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
