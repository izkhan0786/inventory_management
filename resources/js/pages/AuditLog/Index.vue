<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { History, ShieldCheck } from 'lucide-vue-next';

defineProps({
    logs: Object
});
</script>

<template>
    <Head title="Audit Log" />
    <PremiumLayout>
        <div class="page-header mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">System Audit Log</h1>
                <p class="text-[var(--text-secondary)] mt-1">Integrity-checked records of all system modifications.</p>
            </div>
            <div class="px-4 py-2 bg-emerald-500/10 text-emerald-500 rounded-lg text-xs font-bold flex items-center gap-2 border border-emerald-500/20">
                <ShieldCheck class="w-4 h-4" /> Integrity Verified
            </div>
        </div>

        <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-6 transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Target</th>
                            <th>Details</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id">
                            <td class="text-[var(--text-primary)]"><span class="font-bold">{{ log.user?.name || 'System' }}</span></td>
                            <td>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="{
                                    'bg-blue-500/10 text-blue-400': log.action === 'create',
                                    'bg-amber-500/10 text-amber-400': log.action === 'update',
                                    'bg-red-500/10 text-red-400': log.action === 'delete'
                                }">{{ log.action }}</span>
                            </td>
                            <td><span class="text-xs text-[var(--text-secondary)] uppercase tracking-tighter">{{ log.model_type }} #{{ log.model_id }}</span></td>
                            <td class="max-w-xs truncate text-xs text-[var(--text-tertiary)]">{{ JSON.stringify(log.payload) }}</td>
                            <td class="text-xs text-[var(--text-tertiary)]">{{ new Date(log.created_at).toLocaleString() }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="5" class="text-center py-10 text-[var(--text-tertiary)]">Audit log is currently empty.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </PremiumLayout>
</template>
