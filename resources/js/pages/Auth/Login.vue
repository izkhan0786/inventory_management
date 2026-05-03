<script setup>
import PremiumAuthLayout from '@/layouts/PremiumAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LogIn, LoaderCircle } from 'lucide-vue-next';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log In" />
    <PremiumAuthLayout title="Welcome Back" description="Login to your enterprise inventory dashboard.">
        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-500">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="mb-5">
                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Email Address</label>
                <input 
                    type="email" 
                    v-model="form.email" 
                    required 
                    class="form-input" 
                    placeholder="name@company.com" 
                />
                <div v-if="form.errors.email" class="text-xs text-red-400 mt-1">{{ form.errors.email }}</div>
            </div>

            <div class="mb-5">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-bold text-[var(--text-primary)]">Password</label>
                    <Link :href="route('password.request')" class="text-xs font-bold text-[#6366f1] hover:underline">Forgot?</Link>
                </div>
                <input 
                    type="password" 
                    v-model="form.password" 
                    required 
                    class="form-input" 
                    placeholder="••••••••" 
                />
                <div v-if="form.errors.password" class="text-xs text-red-400 mt-1">{{ form.errors.password }}</div>
            </div>

            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-[var(--border-color)] bg-[var(--bg-base)] text-[#6366f1] focus:ring-[#6366f1]" />
                <span class="text-sm text-[var(--text-secondary)] group-hover:text-[var(--text-primary)] transition-colors">Keep me signed in</span>
            </label>

            <button type="submit" class="auth-button" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                <LogIn v-else class="w-4 h-4" />
                Log In to System
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-[var(--border-color)] text-center">
            <p class="text-sm text-[var(--text-secondary)]">
                New here? 
                <Link href="/register" class="text-[var(--text-primary)] font-bold hover:text-[#6366f1] transition-colors">Create Account</Link>
            </p>
        </div>
    </PremiumAuthLayout>
</template>
