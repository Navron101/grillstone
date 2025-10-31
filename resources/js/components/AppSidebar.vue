<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, LayoutGrid, LineChart, Users, FileText, FilePlus, DollarSign, Home, ArrowDownToLine, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Helper function to check if user has access to a module
const hasModuleAccess = (module: string): boolean => {
    if (!user.value?.role?.permissions) return false;
    return user.value.role.permissions.some(permission => permission.module === module);
};

const mainNavItems = computed(() => {
    const items: NavItem[] = [];

    // POS menu - show if user has POS module access
    if (hasModuleAccess('POS')) {
        items.push({
            title: 'POS',
            icon: LayoutGrid,
            items: [
                {
                    title: 'Point of Sale',
                    href: '/pos',
                },
                {
                    title: 'Payout',
                    href: '/pos#payout',
                },
                {
                    title: 'Close Till',
                    href: '/pos#close-till',
                },
                {
                    title: 'Settlements',
                    href: '/settlements',
                },
            ],
        });
    }

    // Inventory menu - show if user has Inventory module access
    if (hasModuleAccess('Inventory')) {
        items.push({
            title: 'Inventory',
            href: '/inventory',
            icon: Folder,
        });
    }

    // HR menu - show if user has HR module access
    if (hasModuleAccess('HR')) {
        items.push({
            title: 'HR',
            icon: Users,
            items: [
                {
                    title: 'Dashboard',
                    href: '/hr',
                },
                {
                    title: 'Employees',
                    href: '/hr/employees',
                },
                {
                    title: 'Departments',
                    href: '/hr/departments',
                },
                {
                    title: 'Time & Attendance',
                    href: '/hr/time-logs',
                },
                {
                    title: 'Contracts',
                    href: '/hr/contracts',
                },
                {
                    title: 'Job Letters',
                    href: '/hr/job-letters',
                },
            ],
        });
    }

    // Finance menu - show if user has Finance module access
    if (hasModuleAccess('Finance')) {
        items.push({
            title: 'Finance',
            icon: DollarSign,
            items: [
                {
                    title: 'Payroll',
                    href: '/payroll',
                },
                {
                    title: 'Loyalty Program',
                    href: '/loyalty',
                },
            ],
        });
    }

    // Reports menu - show if user has Reports module access
    if (hasModuleAccess('Reports')) {
        items.push({
            title: 'Reports',
            href: '/reports',
            icon: LineChart,
        });
    }

    // Settings menu - show if user has Settings module access
    if (hasModuleAccess('Settings')) {
        items.push({
            title: 'Settings',
            icon: Home,
            items: [
                {
                    title: 'Profile',
                    href: '/settings/profile',
                },
                {
                    title: 'Appearance',
                    href: '/settings/appearance',
                },
                {
                    title: 'User Administration',
                    href: '/settings/users',
                },
            ],
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="border-r-2 border-gray-200">
        <SidebarHeader class="border-b border-gray-200 bg-gradient-to-r from-orange-50 to-red-50">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-white">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-gray-200 bg-gray-50">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
