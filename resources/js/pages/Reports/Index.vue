<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { FileBarChart, TrendingUp, DollarSign, Package } from 'lucide-vue-next';

defineProps({
    stockValue: Number,
    totalOrders: Number,
    recentMovements: Number
});
</script>

<template>
    <Head title="Reports & Analytics" />
    <PremiumLayout>
        <div class="page-header mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">System Reports</h1>
                <p class="text-[var(--text-secondary)] mt-1">Deep analytics and financial insights.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-emerald-500/10 text-emerald-500">
                    <DollarSign />
                </div>
                <p class="text-[var(--text-tertiary)] text-xs font-bold uppercase tracking-widest">Est. Inventory Value</p>
                <p class="text-3xl font-bold text-[var(--text-primary)] mt-1">${{ stockValue?.toLocaleString() || 0 }}</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon-wrapper bg-blue-500/10 text-blue-500">
                    <TrendingUp />
                </div>
                <p class="text-[var(--text-tertiary)] text-xs font-bold uppercase tracking-widest">Total Sales Requests</p>
                <p class="text-3xl font-bold text-[var(--text-primary)] mt-1">{{ totalOrders || 0 }}</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper bg-purple-500/10 text-purple-500">
                    <Package />
                </div>
                <p class="text-[var(--text-tertiary)] text-xs font-bold uppercase tracking-widest">Recent Movements</p>
                <p class="text-3xl font-bold text-[var(--text-primary)] mt-1">{{ recentMovements || 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-6 h-64 flex flex-col items-center justify-center text-center transition-colors duration-300">
                <FileBarChart class="w-12 h-12 text-[var(--border-color)] mb-4" />
                <h3 class="font-bold mb-2 text-[var(--text-primary)]">Profitability Report</h3>
                <p class="text-sm text-[var(--text-tertiary)]">Generate a detailed PDF of margins per category.</p>
                <button class="mt-4 px-4 py-2 bg-[#6366f1] text-white rounded-lg text-xs font-bold">Generate PDF</button>
            </div>

            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-6 h-64 flex flex-col items-center justify-center text-center transition-colors duration-300">
                <FileBarChart class="w-12 h-12 text-[var(--border-color)] mb-4" />
                <h3 class="font-bold mb-2 text-[var(--text-primary)]">Auditor's Full Export</h3>
                <p class="text-sm text-[var(--text-tertiary)]">Export all logs and stock takes for external audit.</p>
                <button class="mt-4 px-4 py-2 border border-[var(--border-color)] text-[var(--text-primary)] rounded-lg text-xs font-bold">Export Excel</button>
            </div>
        </div>
    </PremiumLayout>
</template>
