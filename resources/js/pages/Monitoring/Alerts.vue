<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head } from '@inertiajs/vue3';
import { 
    Bell, AlertCircle, ShoppingCart, Clock, 
    ArrowRight, CheckCircle2, MoreVertical, ShieldAlert
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    alerts: Array
});

const getSeverityStyles = (severity) => {
    switch (severity) {
        case 'Critical': return 'from-rose-500/20 to-rose-600/20 border-rose-500/30 text-rose-400';
        case 'High': return 'from-orange-500/20 to-orange-600/20 border-orange-500/30 text-orange-400';
        default: return 'from-indigo-500/20 to-indigo-600/20 border-indigo-500/30 text-indigo-400';
    }
};

const getIcon = (type) => {
    if (type === 'Stock') return ShoppingCart;
    if (type === 'Expiry') return Clock;
    return AlertCircle;
};
</script>

<template>
    <Head title="System Alerts" />

    <PremiumLayout>
        <div class="max-w-6xl mx-auto flex flex-col gap-8">
            <header class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/20">
                        <Bell class="w-5 h-5 text-rose-500" />
                    </div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Active Alerts</h1>
                </div>
                <p class="text-slate-500 text-sm font-medium">Monitoring engine detected {{ alerts.length }} items requiring immediate attention.</p>
            </header>

            <div v-if="alerts.length === 0" class="flex flex-col items-center justify-center py-20 rounded-[2.5rem] border border-white/5 bg-white/[0.02]">
                <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500 mb-6">
                    <CheckCircle2 class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-black text-white">System Secured</h3>
                <p class="text-slate-500 text-sm mt-2">No critical stock or expiry issues detected.</p>
            </div>

            <div class="grid gap-4">
                <div v-for="alert in alerts" :key="alert.id" 
                    class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-r p-6 transition-all hover:scale-[1.01] hover:shadow-2xl shadow-rose-500/5"
                    :class="getSeverityStyles(alert.severity)"
                >
                    <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-start gap-5">
                            <div class="mt-1 h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10 shrink-0">
                                <component :is="getIcon(alert.type)" class="w-6 h-6" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-black text-white leading-none">{{ alert.title }}</h3>
                                    <span class="px-2 py-0.5 rounded-full bg-white/10 text-[9px] font-black uppercase tracking-widest border border-white/10">{{ alert.severity }}</span>
                                </div>
                                <p class="text-sm font-medium text-white/70 max-w-2xl leading-relaxed mt-1">
                                    {{ alert.message }}
                                </p>
                                <div class="flex items-center gap-4 mt-3">
                                    <div class="flex items-center gap-1.5 opacity-60">
                                        <Clock class="w-3 h-3" />
                                        <span class="text-[10px] font-bold uppercase tracking-tighter">{{ new Date(alert.date).toLocaleString() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 opacity-60">
                                        <ShieldAlert class="w-3 h-3" />
                                        <span class="text-[10px] font-bold uppercase tracking-tighter">{{ alert.type }} Domain</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-4 md:mt-0">
                            <button 
                                @click="router.get(route('inventory.index'))"
                                class="px-5 py-2.5 rounded-xl bg-white text-slate-950 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-2 shadow-lg"
                            >
                                Resolve Now
                                <ArrowRight class="w-3 h-3" />
                            </button>
                            <button class="p-2.5 rounded-xl bg-white/10 border border-white/10 hover:bg-white/20 transition-all">
                                <MoreVertical class="w-5 h-5 text-white" />
                            </button>
                        </div>
                    </div>

                    <!-- Decorative Background Gradient -->
                    <div class="absolute -right-10 top-0 h-full w-40 bg-gradient-to-l from-white/10 to-transparent skew-x-12 transform translate-x-20 group-hover:translate-x-0 transition-transform duration-700"></div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>
