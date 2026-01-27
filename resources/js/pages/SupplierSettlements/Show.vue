<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

// Redirect direct visits to the index — viewing is handled in a modal now.
onMounted(() => {
    router.get('/supplier-settlements');
});
</script>

<template>
  <!-- Redirecting to the settlements list; details are shown in a modal on the index page. -->
  <div />
</template>
