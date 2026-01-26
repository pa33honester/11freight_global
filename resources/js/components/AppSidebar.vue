<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    Truck,
    Archive,
    Box,
    CreditCard,
    UserCheck,
    MessageCircle,
    FileText,
    Clipboard,
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Customers',
        href: '/customers',
        icon: Users,
    },
    {
        title: 'Payments',
        href: '/payments',
        icon: CreditCard,
    },
    {
        title: 'Shipments',
        href: '/shipments',
        icon: Truck,
    },
    {
        title: 'Warehouse Inventory',
        href: '/warehouse-inventory',
        icon: Archive,
    },
    {
        title: 'Receipts',
        href: '/receipts',
        icon: FileText,
    },
    {
        title: 'Containers',
        href: '/containers',
        icon: Box,
    },
    {
        title: 'Staff & Roles',
        href: '/admin/staff',
        icon: UserCheck,
    },
    {
        title: 'WhatsApp Conversations',
        href: '/admin/whatsapp-conversations',
        icon: MessageCircle,
    },
    {
        title: 'Audit Logs',
        href: '/admin/audit-logs',
        icon: Clipboard,
    },
];

const footerNavItems: NavItem[] = [

];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
