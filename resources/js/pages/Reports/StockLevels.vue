<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BarChart3 } from 'lucide-vue-next';
</script>

<template>
    <Head title="Stock Levels Report" />
    <PremiumLayout>
        <div class="page-header mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">Stock Levels Report</h1>
                <p class="text-[var(--text-secondary)] mt-1">Real-time overview of current inventory across all locations.</p>
            </div>
        </div>

        <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-12 flex flex-col items-center justify-center text-center transition-colors duration-300">
            <BarChart3 class="w-16 h-16 text-[var(--border-color)] mb-6" />
            <h2 class="text-xl font-bold text-[var(--text-primary)] mb-2">Generating Report...</h2>
            <p class="text-[var(--text-tertiary)] max-w-md mx-auto mb-8">
                We are calculating current stock levels based on historical movements and stock takes.
            </p>
            <div class="w-full max-w-xs bg-[var(--bg-base)] rounded-full h-2 mb-8">
                <div class="bg-[#6366f1] h-2 rounded-full w-2/3"></div>
            </div>
        </div>
    </PremiumLayout>
</template>
