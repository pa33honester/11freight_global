<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

const props = defineProps<{
    customer: Customer;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: '/customers',
    },
    {
        title: 'Edit',
        href: `/customers/${props.customer.id}/edit`,
    },
];

const form = useForm({
    full_name: props.customer.full_name,
    phone: props.customer.phone || '',
    whatsapp_number: props.customer.whatsapp_number || '',
    customer_code: props.customer.customer_code || '',
});

const submit = () => {
    form.put(`/customers/${props.customer.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div>
                <h1 class="text-3xl font-bold">Edit Customer</h1>
                <p class="text-muted-foreground">
                    Update customer information
                </p>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Customer Information</CardTitle>
                    <CardDescription>
                        Update the customer details below
                    </CardDescription>
                </CardHeader>
                <form @submit.prevent="submit">
                    <CardContent class="space-y-4 pb-8">
                        <div class="space-y-2">
                            <Label for="full_name"
                                >Full Name
                                <span class="text-destructive">*</span></Label
                            >
                            <Input
                                id="full_name"
                                v-model="form.full_name"
                                type="text"
                                placeholder="Enter full name"
                                required
                                :class="{
                                    'border-destructive': form.errors.full_name,
                                }"
                            />
                            <p
                                v-if="form.errors.full_name"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.full_name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="customer_code">Customer Code</Label>
                            <Input
                                id="customer_code"
                                v-model="form.customer_code"
                                type="text"
                                placeholder="Enter customer code"
                                :class="{
                                    'border-destructive':
                                        form.errors.customer_code,
                                }"
                            />
                            <p
                                v-if="form.errors.customer_code"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.customer_code }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                placeholder="Enter phone number"
                                :class="{
                                    'border-destructive': form.errors.phone,
                                }"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="whatsapp_number">WhatsApp Number</Label>
                            <Input
                                id="whatsapp_number"
                                v-model="form.whatsapp_number"
                                type="text"
                                placeholder="Enter WhatsApp number"
                                :class="{
                                    'border-destructive':
                                        form.errors.whatsapp_number,
                                }"
                            />
                            <p
                                v-if="form.errors.whatsapp_number"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.whatsapp_number }}
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit('/customers')"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Updating...' : 'Update Customer' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
