<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    LayoutGrid,
    Users,
    Zap,
    Calculator,
    UserCog,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
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
import * as clientsModule from '@/routes/clients';
import * as projectsModule from '@/routes/projects';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const permissions = page.props.auth.permissions ?? [];

    return [
        {
            title: 'Resumen',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(permissions.includes('clients.view')
            ? [
                  {
                      title: 'Mis prospectos',
                      href: clientsModule.index(),
                      icon: Users,
                  },
              ]
            : []),
        ...(permissions.includes('receipts.import')
            ? [
                  {
                      title: 'Dimensionador',
                      href: '/engineering/interconnection',
                      icon: Calculator,
                  },
              ]
            : []),
        ...(permissions.includes('users.view')
            ? [{ title: 'Usuarios', href: usersIndex(), icon: UserCog }]
            : []),
        ...(permissions.includes('projects.view')
            ? [
                  {
                      title: 'Proyectos internos',
                      href: projectsModule.index(),
                      icon: Zap,
                  },
              ]
            : []),
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Documentación',
        href: '/documentation',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader
            class="h-16 justify-center border-b border-sidebar-border"
        >
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

        <SidebarFooter class="border-t border-sidebar-border">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
