<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardContent, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ payments: Array<{ id: number }>; }>();

const form = useForm({ payment_id: '', supplier_name: '', proof_path: '', status: 'PENDING' });
const submitting = ref(false);

const submit = async () => {
  submitting.value = true;
  await form.post('/supplier-settlements', { preserveScroll: true });
  submitting.value = false;
};
</script>

<template>
  <Head title="Create Settlement" />
  <AppLayout :breadcrumbs="[{ title: 'Supplier Settlements', href: '/supplier-settlements' }, { title: 'Create', href: '/supplier-settlements/create' }]">
    <div class="p-4">
      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Create Settlement</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4">
            <div class="flex flex-col space-y-2">
              <Label>Payment</Label>
              <select v-model="form.payment_id" class="w-full rounded border px-3 py-2">
                <option value="">Select payment</option>
                <option v-for="p in props.payments" :key="p.id" :value="p.id">{{ p.id }}</option>
              </select>
            </div>

            <div class="flex flex-col space-y-2">
              <Label>Supplier Name</Label>
              <Input v-model="form.supplier_name" />
            </div>

            <div class="flex flex-col space-y-2">
              <Label>Proof Path</Label>
              <Input v-model="form.proof_path" />
            </div>

            <div class="flex flex-col space-y-2">
              <Label>Status</Label>
              <select v-model="form.status" class="w-full rounded border px-3 py-2">
                <option value="PENDING">PENDING</option>
                <option value="PAID">PAID</option>
              </select>
            </div>

            <div class="flex gap-6">
              <Button :disabled="submitting" @click.prevent="submit">Save</Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
