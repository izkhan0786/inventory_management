<script setup>
import PremiumAuthLayout from '@/layouts/PremiumAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus, LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create Account" />
    <PremiumAuthLayout title="Get Started" description="Create your administrative master account.">
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block text-sm font-bold text-[#f8fafc] mb-2">Full Name</label>
                <input type="text" v-model="form.name" required class="form-input" placeholder="Admin Name" />
                <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-[#f8fafc] mb-2">Email Address</label>
                <input type="email" v-model="form.email" required class="form-input" placeholder="admin@inventory.local" />
                <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-[#f8fafc] mb-2">Secure Password</label>
                <input type="password" v-model="form.password" required class="form-input" placeholder="••••••••" />
                <div v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-[#f8fafc] mb-2">Confirm Password</label>
                <input type="password" v-model="form.password_confirmation" required class="form-input" placeholder="••••••••" />
            </div>

            <button type="submit" class="auth-button" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                <UserPlus v-else class="w-4 h-4" />
                Create Master Account
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-[#2e3340] text-center">
            <p class="text-sm text-[#94a3b8]">
                Already registered? 
                <Link href="/login" class="text-[#f8fafc] font-bold hover:text-[#6366f1] transition-colors">Sign In</Link>
            </p>
        </div>
    </PremiumAuthLayout>
</template>
