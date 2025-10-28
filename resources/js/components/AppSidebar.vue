<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Folder, LayoutGrid, LineChart, Users, FileText, FilePlus, DollarSign, Home, ArrowDownToLine, XCircle } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
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
    },
    {
        title: 'Inventory',
        href: '/inventory',
        icon: Folder,
    },
    {
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
    },
    {
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
    },
    {
        title: 'Reports',
        href: '/reports',
        icon: LineChart,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
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
