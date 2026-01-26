<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Container {
    id: number;
    container_code: string;
    status?: string;
    departure_date?: string | null;
    arrival_date?: string | null;
    created_at: string;
}

const props = defineProps<{
    container: Container;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Containers', href: '/containers' },
    { title: 'Show', href: `/containers/${props.container.id}` },
];
</script>

<template>
    <Head :title="`Container ${props.container.container_code}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Container {{ props.container.container_code }}</CardTitle>
                    <CardDescription>Details</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div><strong>Status:</strong> {{ props.container.status || '-' }}</div>
                        <div><strong>Departure:</strong> {{ props.container.departure_date || '-' }}</div>
                        <div><strong>Arrival:</strong> {{ props.container.arrival_date || '-' }}</div>
                        <div><strong>Created:</strong> {{ new Date(props.container.created_at).toLocaleString() }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
