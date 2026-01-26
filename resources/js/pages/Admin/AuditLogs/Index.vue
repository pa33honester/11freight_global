<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, DialogClose } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Log {
  id: number;
  action: string;
  module: string;
  ip_address?: string | null;
  created_at: string;
  user?: { id: number; name: string } | null;
}

const props = defineProps<{ logs: { data: Log[]; links?: any } }>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: '/admin' },
  { title: 'Audit Logs', href: '/admin/audit-logs' },
];

const navigate = (url: string | null) => {
  if (!url) return;
  window.location.href = url;
};

const dialogOpen = ref(false);
const loading = ref(false);
const selectedLog = ref<any | null>(null);

const view = async (id: number) => {
  loading.value = true;
  try {
    const res = await fetch(`/admin/audit-logs/${id}`, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Failed to load log');
    selectedLog.value = await res.json();
    dialogOpen.value = true;
  } catch (e) {
    console.error(e);
    alert('Unable to load audit log');
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <Head title="Audit Logs" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-4">Audit Logs</h1>

      <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow">
        <table class="w-full table-auto text-gray-800 text-sm dark:text-gray-200">
          <thead>
            <tr class="text-left bg-gray-50 dark:bg-gray-700">
              <th class="p-2">ID</th>
              <th class="p-2">User</th>
              <th class="p-2">Action</th>
              <th class="p-2">Module</th>
              <th class="p-2">IP</th>
              <th class="p-2">When</th>
              <th class="p-2">View</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="log in props.logs.data"
              :key="log.id"
              class="border-t border-gray-200 dark:border-gray-700 odd:bg-white even:bg-gray-50 dark:odd:bg-transparent dark:even:bg-gray-900"
            >
              <td class="p-2">{{ log.id }}</td>
              <td class="p-2">{{ log.user ? log.user.name : '—' }}</td>
              <td class="p-2">{{ log.action }}</td>
              <td class="p-2">{{ log.module }}</td>
              <td class="p-2">{{ log.ip_address }}</td>
              <td class="p-2">{{ new Date(log.created_at).toLocaleString() }}</td>
              <td class="p-2"><button @click.prevent="view(log.id)" class="cursor-pointer text-blue-600 hover:underline">View</button></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        <div v-if="props.logs.links" class="flex gap-2">
          <button
            v-for="link in props.logs.links"
            :key="link.label"
            v-html="link.label"
            :disabled="!link.url"
            @click.prevent="navigate(link.url)"
            class="px-3 py-1 rounded border text-sm border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 bg-white dark:bg-transparent"
          ></button>
        </div>
      </div>
      
      <Dialog :open="dialogOpen" @update:open="dialogOpen = $event">
        <DialogContent class="sm:max-w-2xl max-w-full">
          <DialogHeader class="border-b border-gray-200 dark:border-gray-700 pb-3 mb-3">
            <DialogTitle>Audit Log Details</DialogTitle>
            <DialogDescription v-if="selectedLog">
              Log #{{ selectedLog.id }} — {{ selectedLog.action }}
            </DialogDescription>
          </DialogHeader>

          <div class="px-0 sm:px-6">
            <div class="max-h-[60vh] overflow-y-auto">
              <div v-if="loading" class="p-4">Loading…</div>

              <div v-else-if="selectedLog" class="p-4 space-y-3">
                <p><strong>User:</strong> {{ selectedLog.user ? selectedLog.user.name : '—' }}</p>
                <p><strong>Action:</strong> {{ selectedLog.action }}</p>
                <p><strong>Module:</strong> {{ selectedLog.module }}</p>
                <p><strong>IP:</strong> {{ selectedLog.ip_address }}</p>
                <p><strong>Created:</strong> {{ new Date(selectedLog.created_at).toLocaleString() }}</p>

                <div>
                  <h4 class="font-medium">Old Data</h4>
                  <div class="text-sm font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded whitespace-pre-wrap break-words">{{ JSON.stringify(selectedLog.old_data, null, 2) }}</div>
                </div>

                <div>
                  <h4 class="font-medium">New Data</h4>
                  <div class="text-sm font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded whitespace-pre-wrap break-words">{{ JSON.stringify(selectedLog.new_data, null, 2) }}</div>
                </div>
              </div>
            </div>
          </div>

          <DialogFooter class="mt-4">
            <DialogClose as-child>
              <Button variant="secondary">Close</Button>
            </DialogClose>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
