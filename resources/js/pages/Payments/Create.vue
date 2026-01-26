<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{ customers: Array<{ id: number; full_name: string }>; }>();

const form = useForm({ customer_id: '', reference_code: '', amount: '', status: 'PENDING' });
const submitting = ref(false);

const submit = async () => {
    if (!form.customer_id) { form.setError('customer_id', 'Please select a customer'); return; }
    submitting.value = true;
    await form.post('/payments', { preserveScroll: true });
    submitting.value = false;
};
</script>

<template>
    <Head title="Add Payment" />
    <AppLayout :breadcrumbs="[{ title: 'Payments', href: '/payments' }, { title: 'Create' }]">
        <div class="p-4">
            <Card>
                <CardHeader><CardTitle>Add Payment</CardTitle></CardHeader>
                <CardContent>
                    <div class="grid gap-4">
                        <div>
                            <Label>Customer</Label>
                            <select v-model="form.customer_id" class="w-full rounded border px-3 py-2">
                                <option value="">Select customer</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.full_name }}</option>
                            </select>
                            <p v-if="form.errors.customer_id" class="text-sm text-destructive">{{ form.errors.customer_id }}</p>
                        </div>
                        <div>
                            <Label>Reference Code</Label>
                            <Input v-model="form.reference_code" />
                        </div>
                        <div>
                            <Label>Amount</Label>
                            <Input v-model="form.amount" type="number" step="0.01" />
                        </div>
                        <div class="flex gap-2">
                            <Button :disabled="submitting" @click.prevent="submit">Save</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
