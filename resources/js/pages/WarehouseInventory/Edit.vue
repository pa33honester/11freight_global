<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
  item: {
    id: number;
    shipment_id: number;
    shelf?: string | null;
    photo_path?: string | null;
    photo_url?: string | null;
  };
  shipments: Array<{ id: number; tracking_number: string }>;
}>();

const item = props.item;
const shipments = props.shipments;

const form = useForm({
  shipment_id: String(item.shipment_id),
  shelf: item.shelf || '',
  photo: null as File | null,
});

const submitting = ref(false);

const submit = async () => {
  if (!form.shipment_id) {
    form.setError('shipment_id', 'Please select a shipment');
    return;
  }
  submitting.value = true;
  await form.put(`/warehouse-inventory/${item.id}`, { preserveScroll: true, forceFormData: true });
  submitting.value = false;
};

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  form.photo = target.files && target.files.length ? target.files[0] : null;
};

const goBack = () => window.history.back();
</script>

<template>
  <Head title="Edit Warehouse Inventory" />
  <AppLayout :breadcrumbs="[{ title: 'Warehouse Inventory', href: '/warehouse-inventory' }, { title: 'Edit', href: `/warehouse-inventory/${item.id}/edit` }]">
    <div class="p-4">
      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Edit Inventory Item</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="flex flex-col space-y-6">
            <Label>Shipment</Label>
            <select v-model="form.shipment_id" class="w-full rounded border px-3 py-2">
              <option value="">Select shipment</option>
              <option v-for="s in shipments" :key="s.id" :value="s.id">{{ s.tracking_number }}</option>
            </select>
            <p v-if="form.errors.shipment_id" class="text-sm text-destructive mt-1">{{ form.errors.shipment_id }}</p>
          </div>

          <div class="flex flex-col space-y-6">
            <Label>Shelf</Label>
            <Input v-model="form.shelf" />
            <p v-if="form.errors.shelf" class="text-sm text-destructive mt-1">{{ form.errors.shelf }}</p>
          </div>

          <div class="flex flex-col space-y-6">
            <Label>Photo (optional)</Label>
            <input type="file" accept="image/*" @change="handleFileChange" class="w-full rounded border px-3 py-2" />
            <p v-if="form.errors.photo" class="text-sm text-destructive mt-1">{{ form.errors.photo }}</p>
            <div v-if="item.photo_path || item.photo_url" class="mt-2">
              <img :src="item.photo_url || `/storage/${item.photo_path}`" class="h-24 w-auto rounded border" alt="current photo" />
            </div>
          </div>

          <div class="flex gap-2">
            <Button :disabled="submitting" @click.prevent="submit">{{ submitting ? 'Saving...' : 'Save' }}</Button>
            <Button variant="secondary" @click="goBack">Cancel</Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
