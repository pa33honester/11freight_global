<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
const props = defineProps<{ log: any }>();
</script>

<template>
  <Head :title="`Audit Log #${props.log.id}`" />
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Audit Log #{{ props.log.id }}</h1>
    <div class="bg-white rounded shadow p-4">
      <p><strong>User:</strong> {{ props.log.user ? props.log.user.name : '—' }}</p>
      <p><strong>Action:</strong> {{ props.log.action }}</p>
      <p><strong>Module:</strong> {{ props.log.module }}</p>
      <p><strong>IP:</strong> {{ props.log.ip_address }}</p>
      <p><strong>Created:</strong> {{ new Date(props.log.created_at).toLocaleString() }}</p>

      <div class="mt-4">
        <h3 class="font-medium">Old Data</h3>
        <pre class="text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(props.log.old_data, null, 2) }}</pre>
      </div>

      <div class="mt-4">
        <h3 class="font-medium">New Data</h3>
        <pre class="text-sm bg-gray-100 p-2 rounded">{{ JSON.stringify(props.log.new_data, null, 2) }}</pre>
      </div>

      <div class="mt-4">
        <Link href="/admin/audit-logs"><button class="px-3 py-1 rounded border">Back</button></Link>
      </div>
    </div>
  </div>
</template>
