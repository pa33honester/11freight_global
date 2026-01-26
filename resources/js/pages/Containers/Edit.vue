<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

type Container = {
  id: number;
  container_code: string;
  status?: string | null;
  departure_date?: string | null;
  arrival_date?: string | null;
};

const props = defineProps<{
  container: Container;
}>();

const breadcrumbs = [
  { title: 'Containers', href: '/containers' },
  { title: 'Edit', href: `/containers/${props.container.id}/edit` },
];

const form = useForm({
  container_code: props.container.container_code,
  status: props.container.status || 'OPEN',
  departure_date: props.container.departure_date || '',
  arrival_date: props.container.arrival_date || '',
});

const clientErrors = reactive<Record<string, string>>({});

const validate = (): boolean => {
  Object.keys(clientErrors).forEach((k) => delete clientErrors[k]);

  if (!form.container_code || !String(form.container_code).trim()) {
    clientErrors.container_code = 'Container code is required.';
  } else if (String(form.container_code).length > 50) {
    clientErrors.container_code = 'Container code must be at most 50 characters.';
  }

  const dep = form.departure_date;
  const arr = form.arrival_date;

  if (dep && isNaN(Date.parse(String(dep)))) {
    clientErrors.departure_date = 'Invalid departure date.';
  }
  if (arr && isNaN(Date.parse(String(arr)))) {
    clientErrors.arrival_date = 'Invalid arrival date.';
  }
  if (!clientErrors.departure_date && !clientErrors.arrival_date && dep && arr) {
    if (Date.parse(String(arr)) < Date.parse(String(dep))) {
      clientErrors.arrival_date = 'Arrival date cannot be before departure date.';
    }
  }

  return Object.keys(clientErrors).length === 0;
};

const submit = () => {
  if (!validate()) return;
  (form as unknown as any).clearErrors?.();
  form.put(`/containers/${props.container.id}`, { preserveScroll: true });
};
</script>

<template>
  <Head title="Edit Container" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <div>
        <h1 class="text-3xl font-bold">Edit Container</h1>
        <p class="text-muted-foreground">Update container</p>
      </div>

      <Card class="max-w-2xl">
        <CardHeader>
          <CardTitle>Container Details</CardTitle>
          <CardDescription>Update fields below</CardDescription>
        </CardHeader>
        <form @submit.prevent="submit">
          <CardContent class="space-y-4 pb-8">
            <div class="space-y-2">
              <Label for="container_code">Container Code <span class="text-destructive">*</span></Label>
              <Input id="container_code" v-model="form.container_code" required :class="{ 'border-destructive': clientErrors.container_code || form.errors.container_code }" />
              <p v-if="clientErrors.container_code || form.errors.container_code" class="text-sm text-destructive">{{ clientErrors.container_code || form.errors.container_code }}</p>
            </div>

            <div class="space-y-2">
              <Label for="status">Status</Label>
              <select v-model="form.status" class="w-full rounded border px-3 py-2">
                <option value="OPEN">OPEN</option>
                <option value="SEALED">SEALED</option>
                <option value="IN_TRANSIT">IN_TRANSIT</option>
                <option value="ARRIVED">ARRIVED</option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="departure_date">Departure Date</Label>
                <Input id="departure_date" v-model="form.departure_date" type="date" :class="{ 'border-destructive': clientErrors.departure_date || form.errors.departure_date }" />
                <p v-if="clientErrors.departure_date || form.errors.departure_date" class="text-sm text-destructive">{{ clientErrors.departure_date || form.errors.departure_date }}</p>
              </div>
              <div class="space-y-2">
                <Label for="arrival_date">Arrival Date</Label>
                <Input id="arrival_date" v-model="form.arrival_date" type="date" :class="{ 'border-destructive': clientErrors.arrival_date || form.errors.arrival_date }" />
                <p v-if="clientErrors.arrival_date || form.errors.arrival_date" class="text-sm text-destructive">{{ clientErrors.arrival_date || form.errors.arrival_date }}</p>
              </div>
            </div>
          </CardContent>

          <CardFooter class="flex justify-between">
            <Button type="button" variant="outline" @click="$inertia.visit('/containers')">Cancel</Button>
            <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Updating...' : 'Update Container' }}</Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
