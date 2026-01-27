<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
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

const page = usePage();
const roles: string[] = (page.props.auth?.roles ?? []).map((r: any) => String(r).toLowerCase());

// Build nav items based on roles. Admin sees everything.
const allItems: Record<string, NavItem> = {
    overview: { title: 'Overview', href: dashboard(), icon: LayoutGrid },
    customers: { title: 'Customers', href: '/customers', icon: Users },
    shipments: { title: 'Shipments', href: '/shipments', icon: Truck },
    warehouse: { title: 'Warehouse Inventory', href: '/warehouse-inventory', icon: Archive },
    containers: { title: 'Containers', href: '/containers', icon: Box },
    payments: { title: 'Pay Suppliers', href: '/payments', icon: CreditCard },
    staff: { title: 'Staff & Roles', href: '/admin/staff', icon: UserCheck },
    whatsapp: { title: 'WhatsApp Conversations', href: '/admin/whatsapp-conversations', icon: MessageCircle },
    audit: { title: 'Audit Logs', href: '/admin/audit-logs', icon: Clipboard },
    supplier_settlements: { title: 'Supplier Settlements', href: '/supplier-settlements', icon: CreditCard },
    receipts: { title: 'Receipts', href: '/receipts', icon: FileText },
};

const navSet = new Map<string, NavItem>();

const add = (key: string) => { if (allItems[key] && !navSet.has(key)) navSet.set(key, allItems[key]); };

if (roles.includes('admin')) {
    // admin: full access
    Object.keys(allItems).forEach(k => add(k));
} else {
    // default overview for authenticated users
    add('overview');

    if (roles.includes('operation_manager')) {
        add('shipments');
        add('containers');
    }

    if (roles.includes('finance')) {
        add('supplier_settlements');
        add('payments');
    }

    if (roles.includes('warehouse_staff')) {
        // warehouse intake not implemented — show warehouse inventory as placeholder
        add('warehouse');
    }

    // staff management and admin items are reserved; if user has role 'staff_manager' or similar you can add checks here
}

// Ensure some common items like customers/receipts are available to admin or specific roles if desired
// Convert Map to array
const mainNavItems: NavItem[] = Array.from(navSet.values());

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
