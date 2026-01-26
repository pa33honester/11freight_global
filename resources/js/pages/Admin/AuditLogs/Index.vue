<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
const props = defineProps<{ logs: any }>();

const navigate = (url: string | null) => {
  if (!url) return;
  window.location.href = url;
};
</script>

<template>
  <Head title="Audit Logs" />
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Audit Logs</h1>
    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full table-auto">
        <thead>
          <tr class="text-left">
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
          <tr v-for="log in props.logs.data" :key="log.id" class="border-t">
            <td class="p-2">{{ log.id }}</td>
            <td class="p-2">{{ log.user ? log.user.name : '—' }}</td>
            <td class="p-2">{{ log.action }}</td>
            <td class="p-2">{{ log.module }}</td>
            <td class="p-2">{{ log.ip_address }}</td>
            <td class="p-2">{{ new Date(log.created_at).toLocaleString() }}</td>
            <td class="p-2"><Link :href="`/admin/audit-logs/${log.id}`">View</Link></td>
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
        class="px-3 py-1 rounded border text-sm"
      ></button>
    </div>
    </div>
  </div>
</template>
