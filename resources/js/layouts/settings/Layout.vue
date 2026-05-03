<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];

const currentPath = window.location.pathname;
</script>

<template>
    <div class="px-2 py-6">
        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-[var(--text-primary)]">Settings</h2>
            <p class="text-[var(--text-secondary)] mt-1">Manage your professional profile and system preferences.</p>
        </div>

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full lg:w-64">
                <nav class="flex flex-col gap-1">
                    <Link
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all border border-transparent"
                        :class="currentPath === item.href 
                            ? 'bg-[var(--primary)] text-white shadow-lg shadow-[var(--primary)]/20 shadow-sky-500/10' 
                            : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-hover)] hover:text-[var(--text-primary)] hover:border-[var(--border-color)]'"
                    >
                        {{ item.title }}
                    </Link>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
