<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/Layouts/PremiumLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6 bg-[var(--bg-surface)] p-8 rounded-3xl border border-[var(--border-color)] shadow-xl transition-colors duration-300">
                <div class="mb-2">
                    <h3 class="text-xl font-bold text-[var(--text-primary)]">Profile Information</h3>
                    <p class="text-[var(--text-secondary)] text-sm mt-1">Update your name and professional email address.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name" class="text-[var(--text-secondary)] font-bold text-xs uppercase tracking-widest">Full Name</Label>
                        <Input id="name" class="bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-primary)] focus:ring-[var(--primary)] h-12 rounded-xl" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="text-[var(--text-secondary)] font-bold text-xs uppercase tracking-widest">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-primary)] focus:ring-[var(--primary)] h-12 rounded-xl"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="mt-2 text-sm text-neutral-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="focus:outline-hidden rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button 
                            :disabled="form.processing"
                            class="px-8 py-3 rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-white font-bold transition-all shadow-lg shadow-[var(--primary)]/20 disabled:opacity-50"
                        >
                            Save Changes
                        </button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-emerald-500 font-bold">Successfully saved.</p>
                        </TransitionRoot>
                    </div>
                </form>
            </div>

            <div class="bg-rose-500/5 border border-rose-500/10 p-8 rounded-3xl transition-colors duration-300">
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
