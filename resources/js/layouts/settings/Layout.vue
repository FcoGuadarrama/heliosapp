<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editCompany } from '@/routes/company';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import { index as indexUsers } from '@/routes/users';
import type { NavItem } from '@/types';

withDefaults(
    defineProps<{
        /**
         * Las páginas de ajustes son formularios estrechos por defecto.
         * Las que muestran tablas de datos (usuarios) necesitan todo el ancho.
         */
        wide?: boolean;
    }>(),
    { wide: false },
);

const page = usePage();
const permissions = computed(
    () => (page.props.auth as { permissions?: string[] })?.permissions ?? [],
);

const sidebarNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Profile',
            href: editProfile(),
        },
        {
            title: 'Security',
            href: editSecurity(),
        },
        {
            title: 'Appearance',
            href: editAppearance(),
        },
    ];

    if (permissions.value.includes('settings.view')) {
        items.push({
            title: 'Empresa',
            href: editCompany(),
        });
    }

    if (permissions.value.includes('users.view')) {
        items.push({
            title: 'Usuarios',
            href: indexUsers(),
        });
    }

    return items;
});

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="space-y-6">
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Settings"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div
                :class="[
                    'surface-panel flex-1',
                    wide ? 'min-w-0' : 'md:max-w-2xl',
                ]"
            >
                <section :class="['space-y-12', wide ? 'w-full' : 'max-w-xl']">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
