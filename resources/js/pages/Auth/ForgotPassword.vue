<script setup>
import PremiumAuthLayout from '@/layouts/PremiumAuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, LoaderCircle, ArrowLeft } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Reset Password" />
    <PremiumAuthLayout title="Recover Access" description="Enter your email to receive a recovery link.">
        <div v-if="status" class="mb-4 text-sm font-medium text-emerald-500 bg-emerald-500/10 p-3 rounded-lg border border-emerald-500/20">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="mb-6">
                <label class="block text-sm font-bold text-[#f8fafc] mb-2">Registration Email</label>
                <input type="email" v-model="form.email" required class="form-input" placeholder="admin@company.com" />
                <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
            </div>

            <button type="submit" class="auth-button" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                <Mail v-else class="w-4 h-4" />
                Send Recovery Link
            </button>
        </form>

        <div class="mt-8 text-center">
            <Link :href="route('login')" class="text-xs font-bold text-[#94a3b8] hover:text-[#6366f1] flex items-center justify-center gap-1 transition-colors">
                <ArrowLeft class="w-3 h-3" /> Back to Login
            </Link>
        </div>
    </PremiumAuthLayout>
</template>
