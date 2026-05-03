<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ClipboardList } from 'lucide-vue-next';
</script>

<template>
    <Head title="Stock Take" />
    <PremiumLayout>
        <div class="page-header mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">Stock Take</h1>
                <p class="text-[var(--text-secondary)] mt-1">Conduct physical stock counts and reconciliations.</p>
            </div>
        </div>

        <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-12 flex flex-col items-center justify-center text-center transition-colors duration-300">
            <ClipboardList class="w-16 h-16 text-[var(--border-color)] mb-6" />
            <h2 class="text-xl font-bold text-[var(--text-primary)] mb-2">No active stock take sessions</h2>
            <p class="text-[var(--text-tertiary)] max-w-md mx-auto mb-8">
                Start a new stock take to verify physical inventory levels against the system records.
            </p>
            <button class="px-6 py-3 rounded-xl bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold transition-all shadow-lg shadow-[#6366f1]/20">
                Start New Session
            </button>
        </div>
    </PremiumLayout>
</template>
