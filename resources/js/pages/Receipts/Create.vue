<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

const form = useForm({
    receipt_number: '',
    type: 'INVOICE',
    linked_id: '',
});

const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    await form.post('/receipts', { preserveScroll: true });
    submitting.value = false;
};
</script>

<template>
    <Head title="Create Receipt" />
    <AppLayout :breadcrumbs="[{ title: 'Receipts', href: '/receipts' }, { title: 'Create', href: '/receipts/create' }]">
        <div class="p-4">
            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Create Receipt</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4">
                        <div>
                            <Label>Receipt Number (optional)</Label>
                            <Input v-model="form.receipt_number" />
                        </div>

                        <div>
                            <Label>Type</Label>
                            <select v-model="form.type" class="w-full rounded border px-3 py-2">
                                <option value="PR">PR</option>
                                <option value="WR">WR</option>
                                <option value="SR">SR</option>
                                <option value="AR">AR</option>
                                <option value="DR">DR</option>
                                <option value="SS">SS</option>
                            </select>
                        </div>

                        <div>
                            <Label>Linked ID (optional)</Label>
                            <Input v-model="form.linked_id" />
                        </div>

                        <div class="flex gap-2">
                            <Button :disabled="submitting" @click.prevent="submit">Create</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
