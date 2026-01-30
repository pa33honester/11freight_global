<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import {
	Card,
	CardHeader,
	CardTitle,
	CardDescription,
	CardContent,
	CardFooter,
} from '@/components/ui/card';
// native select used for accessibility and simplicity
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Customer {
	id: number;
	full_name: string;
}

interface Shipment {
	id: number;
	shipment_code: string;
	customer_id: number | null;
	supplier_name?: string;
	weight?: number | null;
	shelf_code?: string | null;
	status?: string;
}

const props = defineProps<{
	shipment: Shipment;
	customers: Customer[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
	{ title: 'Shipments', href: '/shipments' },
	{ title: 'Edit', href: `/shipments/${props.shipment.id}/edit` },
];

const form = useForm({
	shipment_code: props.shipment.shipment_code ?? '',
	customer_id: props.shipment.customer_id ?? (props.customers?.[0]?.id ?? ''),
	supplier_name: props.shipment.supplier_name ?? '',
	weight: props.shipment.weight ?? '',
	shelf_code: props.shipment.shelf_code ?? '',
	status: props.shipment.status ?? 'RECEIVED',
});

const clientErrors = reactive<Record<string, string>>({});

const validate = (): boolean => {
	Object.keys(clientErrors).forEach((k) => delete clientErrors[k]);

	if (!form.shipment_code || !String(form.shipment_code).trim()) {
		clientErrors.shipment_code = 'Shipment code is required.';
	} else if (String(form.shipment_code).length > 50) {
		clientErrors.shipment_code = 'Shipment code must be at most 50 characters.';
	}

	if (!form.customer_id) {
		clientErrors.customer_id = 'Please select a customer.';
	}

	if (form.weight !== '' && form.weight !== null) {
		const num = Number(form.weight);
		if (Number.isNaN(num)) {
			clientErrors.weight = 'Weight must be a number.';
		} else if (num < 0) {
			clientErrors.weight = 'Weight cannot be negative.';
		}
	}

	if (form.shelf_code && String(form.shelf_code).length > 50) {
		clientErrors.shelf_code = 'Shelf code must be at most 50 characters.';
	}

	return Object.keys(clientErrors).length === 0;
};

const submit = () => {
	if (!validate()) return;
	(form as unknown as any).clearErrors?.();
	form.put(`/shipments/${props.shipment.id}`, { preserveScroll: true });
};
</script>

<template>
	<Head :title="`Edit Shipment ${props.shipment.shipment_code}`" />

	<AppLayout :breadcrumbs="breadcrumbs">
		<div class="flex h-full flex-1 flex-col gap-4 p-4">
			<div>
				<h1 class="text-3xl font-bold">Edit Shipment</h1>
				<p class="text-muted-foreground">Modify shipment details</p>
			</div>

			<Card class="max-w-2xl">
				<CardHeader>
					<CardTitle>Shipment Details</CardTitle>
					<CardDescription>Update details below</CardDescription>
				</CardHeader>
				<form @submit.prevent="submit">
					<CardContent class="space-y-4 pb-8">
						<div class="space-y-2">
							<Label for="shipment_code">Shipment Code <span class="text-destructive">*</span></Label>
							<Input id="shipment_code" :disabled="true" v-model="form.shipment_code" required :class="{ 'border-destructive': clientErrors.shipment_code || form.errors.shipment_code }" />
							<p v-if="clientErrors.shipment_code || form.errors.shipment_code" class="text-sm text-destructive">{{ clientErrors.shipment_code || form.errors.shipment_code }}</p>
						</div>

						<div class="space-y-2">
							<Label for="customer_id">Customer</Label>
								<select id="customer_id" v-model.number="form.customer_id" class="w-full rounded border px-3 py-2">
									<option value="">Select customer</option>
									<option v-for="c in customers" :key="c.id" :value="c.id">{{ c.full_name }}</option>
								</select>
							<p v-if="clientErrors.customer_id || form.errors.customer_id" class="text-sm text-destructive">{{ clientErrors.customer_id || form.errors.customer_id }}</p>
						</div>

						<div class="space-y-2">
							<Label for="supplier_name">Supplier</Label>
							<Input id="supplier_name" v-model="form.supplier_name" />
						</div>

						<div class="grid grid-cols-2 gap-4">
							<div class="space-y-2">
								<Label for="weight">Weight</Label>
								<Input id="weight" v-model="form.weight" type="number" step="0.01" :class="{ 'border-destructive': clientErrors.weight || form.errors.weight }" />
								<p v-if="clientErrors.weight || form.errors.weight" class="text-sm text-destructive">{{ clientErrors.weight || form.errors.weight }}</p>
							</div>
							<div class="space-y-2">
								<Label for="shelf_code">Shelf Code</Label>
								<Input id="shelf_code" v-model="form.shelf_code" :class="{ 'border-destructive': clientErrors.shelf_code || form.errors.shelf_code }" />
								<p v-if="clientErrors.shelf_code || form.errors.shelf_code" class="text-sm text-destructive">{{ clientErrors.shelf_code || form.errors.shelf_code }}</p>
							</div>
						</div>
					</CardContent>

					<CardFooter class="flex justify-between">
						<Button type="button" variant="outline" @click="$inertia.visit('/shipments')">Cancel</Button>
						<Button type="submit" :disabled="form.processing">{{ form.processing ? 'Saving...' : 'Save Changes' }}</Button>
					</CardFooter>
				</form>
			</Card>
		</div>
	</AppLayout>
</template>

