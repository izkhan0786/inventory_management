<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/Layouts/PremiumLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';

interface Props {
    className?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement>();
const currentPasswordInput = ref<HTMLInputElement>();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6 bg-[var(--bg-surface)] p-8 rounded-3xl border border-[var(--border-color)] shadow-xl transition-colors duration-300">
                <div class="mb-2">
                    <h3 class="text-xl font-bold text-[var(--text-primary)]">Change Password</h3>
                    <p class="text-[var(--text-secondary)] text-sm mt-1">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="current_password" class="text-[var(--text-secondary)] font-bold text-xs uppercase tracking-widest">Current Password</Label>
                        <Input
                            id="current_password"
                            ref="currentPasswordInput"
                            v-model="form.current_password"
                            type="password"
                            class="bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-primary)] focus:ring-[var(--primary)] h-12 rounded-xl"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError :message="form.errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-[var(--text-secondary)] font-bold text-xs uppercase tracking-widest">New password</Label>
                        <Input
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-primary)] focus:ring-[var(--primary)] h-12 rounded-xl"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-[var(--text-secondary)] font-bold text-xs uppercase tracking-widest">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-primary)] focus:ring-[var(--primary)] h-12 rounded-xl"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button 
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold transition-all shadow-lg shadow-[var(--primary)]/20 disabled:opacity-50"
                        >
                            Update Password
                        </button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-emerald-500 font-bold">Successfully updated.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
