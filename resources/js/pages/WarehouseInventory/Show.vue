<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
    item: {
        id: number;
        shipment?: { id: number; shipment_code: string } | null;
        shelf?: string | null;
        photo_path?: string | null;
        intake_by?: { id: number; name: string } | null;
        intake_time?: string | null;
        created_at?: string | null;
    };
}>();
</script>

<template>
    <Head :title="`Item #${item.id}`" />

    <AppLayout :breadcrumbs="[{ title: 'Warehouse Inventory', href: '/warehouse-inventory' }, { title: `Item #${item.id}` }]">
        <div class="p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Inventory Item</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4">
                        <div><strong>Shipment:</strong> {{ item.shipment?.shipment_code || '-' }}</div>
                        <div><strong>Shelf:</strong> {{ item.shelf || '-' }}</div>
                        <div>
                            <strong>Photo:</strong>
                            <div v-if="item.photo_url">
                                <img :src="item.photo_url" class="h-48 w-auto rounded" alt="item photo" />
                            </div>
                            <div v-else><span v-if="item.photo_path">{{ item.photo_path }}</span><span v-else>-</span></div>
                        </div>
                        <div><strong>Intake By:</strong> {{ item.intake_by?.name || '-' }}</div>
                        <div><strong>Intake Time:</strong> {{ item.intake_time || item.created_at || '-' }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
