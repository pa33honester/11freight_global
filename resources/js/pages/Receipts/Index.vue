<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{ receipts: any }>();

const startIndex = computed(() => {
    const meta = props.receipts?.meta;
    if (meta?.from) return meta.from;
    if (meta?.current_page && meta?.per_page) return (meta.current_page - 1) * meta.per_page + 1;
    return 1;
});

const imageViewerOpen = ref(false);
const selectedImageUrl = ref<string | null>(null);

const openImageViewer = (imageUrl: string) => {
    selectedImageUrl.value = imageUrl;
    imageViewerOpen.value = true;
};

</script>

<template>
    <Head title="Receipts" />
    <AppLayout :breadcrumbs="[{ title: 'Receipts', href: '/receipts' }]">
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">Receipts</h1>
                </div>
                <Link href="/receipts/create"><Button>Add Receipt</Button></Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Receipts</CardTitle>
                    <CardDescription>Immutable receipts</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-2">#</th>
                                    <th class="px-4 py-2">QR</th>
                                    <th class="px-4 py-2">Number</th>
                                    <th class="px-4 py-2">Type</th>
                                    <th class="px-4 py-2">Linked ID</th>
                                    <th class="px-4 py-2">Created</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(r, i) in receipts.data" :key="r.id" class="border-b hover:bg-muted/30">
                                    <td class="px-4 py-2">{{ startIndex + i }}</td>
                                    <td class="px-4 py-2">
                                        <img v-if="r.qr_code" :src="`/storage/${r.qr_code}`" alt="qr" class="w-12 h-12 object-contain rounded" />
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                    <td class="px-4 py-2">{{ r.receipt_number }}</td>
                                    <td class="px-4 py-2">{{ r.type }}</td>
                                    <td class="px-4 py-2">{{ r.linked_id || '-' }}</td>
                                    <td class="px-4 py-2">{{ r.created_at }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <Button variant="outline" size="sm" @click="openImageViewer(`/storage/receipts_images/${r.receipt_number}.png`)">View</Button>
                                    </td>
                                </tr>
                                <tr v-if="receipts.data.length === 0"><td colspan="7" class="p-6 text-center text-muted-foreground">No receipts found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <!-- Receipt Image Viewer Modal -->
            <Dialog :open="imageViewerOpen" @update:open="imageViewerOpen = $event">
                <DialogContent class="sm:max-w-4xl">
                    <DialogHeader>
                        <DialogTitle>Receipt Image</DialogTitle>
                    </DialogHeader>
                    <div class="flex items-center justify-center bg-muted rounded-lg p-4 min-h-96">
                        <img v-if="selectedImageUrl" :src="selectedImageUrl" class="max-w-full max-h-[600px] rounded" alt="Receipt image" />
                    </div>
                    <DialogFooter>
                        <DialogClose as-child>
                            <Button variant="secondary">Close</Button>
                        </DialogClose>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
