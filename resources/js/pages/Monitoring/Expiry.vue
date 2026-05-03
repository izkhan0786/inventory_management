<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Clock, Calendar, AlertTriangle, CheckCircle2, 
    XCircle, Search, Filter, ArrowUpRight, Package
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    batches: Array
});

const searchQuery = ref('');

const getStatusStyles = (status) => {
    switch (status) {
        case 'Expired':
            return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        case 'Warning':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        default:
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'Expired': return XCircle;
        case 'Warning': return AlertTriangle;
        default: return CheckCircle2;
    }
};

const filteredBatches = computed(() => {
    const q = searchQuery.value.toLowerCase();
    return props.batches.filter(b => 
        b.product_name.toLowerCase().includes(q) ||
        b.batch_number.toLowerCase().includes(q)
    );
});
</script>

<template>
    <Head title="Expiry Tracking" />

    <PremiumLayout>
        <div class="flex flex-col gap-6">
            <!-- Header Section -->
            <section class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 p-10 shadow-2xl">
                <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 rounded-xl bg-indigo-500/20 border border-indigo-500/30">
                                <Clock class="w-5 h-5 text-indigo-400" />
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-400">Monitoring Core</span>
                        </div>
                        <h1 class="text-4xl font-black tracking-tight text-white md:text-5xl">Expiry Tracking</h1>
                        <p class="mt-4 max-w-xl text-lg font-medium text-slate-400 leading-relaxed">
                            Proactively manage perishable inventory and batch lifecycles to minimize waste and ensure quality.
                        </p>
                    </div>
                    
                    <div class="flex gap-4">
                        <div class="rounded-3xl border border-white/5 bg-white/5 p-6 backdrop-blur-xl text-center min-w-[120px]">
                            <div class="text-2xl font-black text-rose-400">{{ batches.filter(b => b.status === 'Expired').length }}</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Expired</div>
                        </div>
                        <div class="rounded-3xl border border-white/5 bg-white/5 p-6 backdrop-blur-xl text-center min-w-[120px]">
                            <div class="text-2xl font-black text-amber-400">{{ batches.filter(b => b.status === 'Warning').length }}</div>
                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Expiring Soon</div>
                        </div>
                    </div>
                </div>
                
                <!-- Abstract Background Elements -->
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-[100px]"></div>
                <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-rose-500/5 blur-[100px]"></div>
            </section>

            <!-- Main Inventory Table -->
            <section class="rounded-[2rem] border border-white/10 bg-[#0f172a]/60 backdrop-blur-xl shadow-2xl overflow-hidden">
                <div class="p-8 border-b border-white/5 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative w-full max-w-md group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors" />
                        <input v-model="searchQuery" type="text" placeholder="Search batch or product..." class="w-full rounded-2xl border border-white/10 bg-white/5 py-4 pl-12 pr-4 text-sm text-white placeholder:text-slate-600 outline-none focus:border-indigo-500/50 focus:bg-white/10 transition-all" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/5 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                                <th class="pl-8 pr-4 py-6">Product Details</th>
                                <th class="px-4 py-6 text-center">Batch ID</th>
                                <th class="px-4 py-6 text-center">Current Qty</th>
                                <th class="px-4 py-6">Expiry Timeline</th>
                                <th class="px-4 py-6 text-center">Status</th>
                                <th class="pl-4 pr-8 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="batch in filteredBatches" :key="batch.id" class="hover:bg-white/[0.02] transition-colors group/row border-b border-white/[0.02] last:border-0">
                                <td class="pl-8 pr-4 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 flex items-center justify-center border border-white/10 shadow-lg">
                                            <Package class="w-6 h-6 text-indigo-400" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-white group-hover/row:text-indigo-400 transition-colors">{{ batch.product_name }}</span>
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">{{ batch.variant_name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-[10px] font-black text-slate-300 tracking-widest uppercase italic">
                                        {{ batch.batch_number }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-black text-white">{{ batch.current_qty }}</span>
                                        <span class="text-[9px] font-bold text-slate-600 uppercase tracking-tighter">Units In Stock</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-2">
                                            <Calendar class="w-3.5 h-3.5 text-slate-500" />
                                            <span class="text-xs font-bold text-slate-200">{{ new Date(batch.expiry_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                                        </div>
                                        <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden">
                                            <div :class="['h-full rounded-full transition-all duration-1000', 
                                                batch.days_left < 0 ? 'w-full bg-rose-500' : 
                                                batch.days_left < 90 ? 'w-[75%] bg-amber-500' : 'w-[30%] bg-emerald-500'
                                            ]"></div>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-widest" :class="batch.days_left < 0 ? 'text-rose-400' : 'text-slate-500'">
                                            {{ batch.days_left < 0 ? 'Already Expired' : `${batch.days_left} Days Remaining` }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div :class="['inline-flex items-center gap-2 px-4 py-2 rounded-2xl border text-[10px] font-black uppercase tracking-widest transition-all shadow-lg', getStatusStyles(batch.status)]">
                                        <component :is="getStatusIcon(batch.status)" class="w-3.5 h-3.5" />
                                        {{ batch.status }}
                                    </div>
                                </td>
                                <td class="pl-4 pr-8 py-6 text-right">
                                    <button class="p-3 bg-white/5 border border-white/10 rounded-2xl transition-all text-slate-400 hover:text-indigo-400 hover:bg-indigo-400/10 hover:border-indigo-400/30">
                                        <ArrowUpRight class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </PremiumLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
</style>
