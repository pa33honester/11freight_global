<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card';
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

const props = defineProps<{ payment: { id: number; customer_id: number; reference_code: string; amount: number; status: string }; customers: Array<{ id: number; full_name: string }>; }>();
const payment = props.payment;
const customers = props.customers;

const form = useForm({ customer_id: String(payment.customer_id), reference_code: payment.reference_code, amount: String(payment.amount), status: payment.status });
const submitting = ref(false);
const confirmOpen = ref(false);

const submit = async () => {
    // if status changed to APPROVED or REJECTED, ask for confirmation
    if (form.status && form.status !== payment.status && ['APPROVED','REJECTED'].includes(String(form.status))) {
        confirmOpen.value = true;
        return;
    }
    submitting.value = true;
    await form.put(`/payments/${payment.id}`, { preserveScroll: true });
    submitting.value = false;
};

const confirmSubmit = async () => {
    submitting.value = true;
    await form.put(`/payments/${payment.id}`, { preserveScroll: true });
    submitting.value = false;
    confirmOpen.value = false;
};
</script>

<template>
    <Head title="Edit Payment" />
    <AppLayout :breadcrumbs="[{ title: 'Payments', href: '/payments' }, { title: 'Edit' }]">
        <div class="p-4">
            <Card>
                <CardHeader><CardTitle>Edit Payment</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid gap-4">
                        <div>
                            <Label>Customer</Label>
                            <select v-model="form.customer_id" class="w-full rounded border px-3 py-2">
                                <option value="">Select customer</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.full_name }}</option>
                            </select>
                        </div>
                        <div>
                            <Label>Reference</Label>
                            <Input v-model="form.reference_code" />
                        </div>
                        <div>
                            <Label>Amount</Label>
                            <Input v-model="form.amount" type="number" step="0.01" />
                        </div>
                        <div>
                            <Label>Status</Label>
                            <select v-model="form.status" class="w-full rounded border px-3 py-2">
                                <option value="PENDING">PENDING</option>
                                <option value="APPROVED">APPROVED</option>
                                <option value="REJECTED">REJECTED</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <Button :disabled="submitting" @click.prevent="submit">Save</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Confirm status change</DialogTitle>
                    <DialogDescription>Changing the payment status to <strong>{{ form.status }}</strong> is irreversible. Are you sure?</DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 mt-4">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button variant="destructive" :disabled="submitting" @click="confirmSubmit">{{ submitting ? 'Saving...' : 'Confirm' }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
