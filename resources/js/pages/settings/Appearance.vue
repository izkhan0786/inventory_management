<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/Layouts/PremiumLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6 bg-[var(--bg-surface)] p-8 rounded-3xl border border-[var(--border-color)] shadow-xl transition-colors duration-300">
                <div class="mb-2">
                    <h3 class="text-xl font-bold text-[var(--text-primary)]">System Appearance</h3>
                    <p class="text-[var(--text-secondary)] text-sm mt-1">Customize how the interface looks and feels on your device.</p>
                </div>
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
