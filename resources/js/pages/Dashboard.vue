<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Package, ShoppingCart, 
    ArrowUpRight, TrendingUp, AlertTriangle, 
    Layers, Tag, MapPin, Search, Bell, Settings,
    Box, Briefcase, Activity, CheckCircle2,
    Calendar, TrendingDown, DollarSign, Users,
    BarChart3, PieChart, ArrowLeftRight
} from 'lucide-vue-next';
import HeroSlider from '@/components/HeroSlider.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    auth: Object,
    categoryDistribution: Array,
    movementTrend: Array,
    health: Object
});

const user = computed(() => props.auth?.user || { name: 'Demo User' });

const movementChartData = computed(() => ({
    labels: props.movementTrend.map(d => d.label),
    datasets: [
        {
            label: 'Inbound',
            backgroundColor: '#10b981',
            data: props.movementTrend.map(d => d.inbound),
            borderRadius: 8,
        },
        {
            label: 'Outbound',
            backgroundColor: '#ef4444',
            data: props.movementTrend.map(d => d.outbound),
            borderRadius: 8,
        }
    ]
}));

const categoryChartData = computed(() => ({
    labels: props.categoryDistribution.map(c => c.name),
    datasets: [
        {
            backgroundColor: props.categoryDistribution.map(c => c.color || '#4f46e5'),
            data: props.categoryDistribution.map(c => c.quantity),
            borderWidth: 0,
            hoverOffset: 10
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 12, weight: 'bold' },
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 12,
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b', font: { size: 10 } } }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            padding: 12,
            cornerRadius: 12,
        }
    },
    cutout: '70%'
};
</script>

<template>
    <Head title="Dashboard" />

    <PremiumLayout>
        <div class="flex flex-col gap-8">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-[var(--text-primary)] px-2">Welcome back, {{ user.name.split(' ')[0] }}</h1>
                    <p class="text-[var(--text-tertiary)] px-2 mt-1 text-sm">Here's what's happening with your inventory today.</p>
                </div>
            </div>

            <HeroSlider :stats="stats" />

            <!-- Main Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Products Card -->
                <div class="p-6 rounded-[2rem] bg-[var(--bg-surface)] border border-[var(--border-color)] group hover:border-[var(--primary)] transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-sky-500/10 flex items-center justify-center text-sky-400 group-hover:scale-110 transition-transform">
                            <Package class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)]">Products</p>
                            <p class="text-2xl font-black text-[var(--text-primary)]">{{ stats.totalItems }}</p>
                        </div>
                    </div>
                </div>
                <!-- Total Value Card -->
                <div class="p-6 rounded-[2rem] bg-[var(--bg-surface)] border border-[var(--border-color)] group hover:border-[var(--primary)] transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <DollarSign class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)]">Total Value</p>
                            <p class="text-2xl font-black text-[var(--text-primary)]">${{ stats.totalValue.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
                <!-- Locations Card -->
                <div class="p-6 rounded-[2rem] bg-[var(--bg-surface)] border border-[var(--border-color)] group hover:border-[var(--primary)] transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                            <MapPin class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)]">Locations</p>
                            <p class="text-2xl font-black text-[var(--text-primary)]">{{ stats.activeLocations }}</p>
                        </div>
                    </div>
                </div>
                <!-- Categories Card -->
                <div class="p-6 rounded-[2rem] bg-[var(--bg-surface)] border border-[var(--border-color)] group hover:border-[var(--primary)] transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                            <Tag class="w-6 h-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)]">Categories</p>
                            <p class="text-2xl font-black text-[var(--text-primary)]">{{ stats.categoriesCount || stats.categoryDistribution?.length || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insights Row -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Inventory Overview -->
                <div class="xl:col-span-2 p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] overflow-hidden relative">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-[var(--text-primary)]">Inventory Overview</h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="w-2 h-2 rounded-full bg-[var(--primary)]"></span>
                                <span class="text-[10px] font-bold text-[var(--text-tertiary)] uppercase tracking-tight">Stock Distribution by Category</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase tracking-wider">Low</span>
                            <div class="w-24 h-1.5 bg-white/5 rounded-full overflow-hidden flex">
                                <div class="w-1/3 bg-emerald-500/10 h-full"></div>
                                <div class="w-1/3 bg-emerald-500/30 h-full"></div>
                                <div class="w-1/3 bg-emerald-500 h-full"></div>
                            </div>
                            <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase tracking-wider">High</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 h-64">
                        <div v-for="cat in categoryDistribution.slice(0, 3)" :key="cat.name" 
                             class="rounded-2xl p-6 flex flex-col justify-between shadow-lg"
                             :style="{ backgroundColor: cat.color + '20', border: `1px solid ${cat.color}40` }">
                            <span class="text-4xl font-black" :style="{ color: cat.color }">{{ cat.quantity }}</span>
                            <span class="text-[10px] font-black uppercase" :style="{ color: cat.color }">{{ cat.name }}</span>
                        </div>
                    </div>

                    <!-- Micro Stats Footer -->
                    <div class="grid grid-cols-4 gap-4 mt-8 pt-6 border-t border-[var(--border-color)]/50">
                        <div class="flex items-center gap-3">
                            <Package class="w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase">Products</span>
                                <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.totalItems }}</span>
                            </div>
                        </div>
                         <div class="flex items-center gap-3">
                            <DollarSign class="w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase">Total Value</span>
                                <span class="text-xs font-black text-[var(--text-primary)]">${{ stats.totalValue.toLocaleString() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <MapPin class="w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase">Locations</span>
                                <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.activeLocations }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Tag class="w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                            <div class="flex flex-col">
                                <span class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase">Categories</span>
                                <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.categoryDistribution?.length || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Level Trend -->
                <div class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)]">
                    <h3 class="text-xs font-black uppercase tracking-widest text-[#94a3b8] mb-6">Stock Level Trend</h3>
                    <div class="relative h-64 w-full mt-8">
                        <BarChart :chartData="movementChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <!-- Category Distribution -->
                <div class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-[#94a3b8] mb-1">Category Distribution</h3>
                        <div class="mt-4 flex gap-3">
                             <div v-for="cat in categoryDistribution.slice(0, 3)" :key="cat.name" 
                                  class="flex flex-col pr-4 border-r border-white/5 last:border-0">
                                <span class="text-[9px] font-bold text-[#64748b] uppercase tracking-tighter">{{ cat.name }}</span>
                                <span class="text-lg font-black text-white">{{ cat.quantity }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-32 h-32 relative">
                        <DoughnutChart :chartData="categoryChartData" :options="doughnutOptions" />
                    </div>
                </div>

                <!-- Inventory Health -->
                <div class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)]">
                    <h3 class="text-xs font-black uppercase tracking-widest text-[#94a3b8] mb-1">Inventory Health</h3>
                    <div class="mt-10">
                        <div class="flex items-center justify-between mb-3 text-[10px] font-black tracking-widest uppercase">
                            <span class="text-[10px] font-bold text-emerald-400">Inventory Status</span>
                            <span class="text-[var(--text-primary)]">Active</span>
                        </div>
                        <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" :style="{ width: '100%' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Stats Grid (As seen in the bottom of middle row) -->
             <div class="grid grid-cols-2 md:grid-cols-5 gap-4 px-2 py-4 border-t border-white/5">
                 <div class="flex items-center gap-3">
                    <Package class="w-4 h-4 text-[#64748b]" />
                    <div class="flex flex-col">
                        <span class="text-[8px] font-bold text-[#64748b] uppercase font-bold">Total Items</span>
                        <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.totalItems }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <AlertTriangle class="w-4 h-4 text-[#64748b]" />
                    <div class="flex flex-col">
                        <span class="text-[8px] font-bold text-[#64748b] uppercase font-bold">Low Stock</span>
                        <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.lowStock }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Calendar class="w-4 h-4 text-[#64748b]" />
                    <div class="flex flex-col">
                        <span class="text-[8px] font-bold text-[#64748b] uppercase font-bold">Expiring</span>
                        <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.expiringBatches }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <ArrowLeftRight class="w-4 h-4 text-[#64748b]" />
                    <div class="flex flex-col">
                        <span class="text-[8px] font-bold text-[#64748b] uppercase font-bold">Pending Orders</span>
                        <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.pendingOrders }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Layers class="w-4 h-4 text-[#64748b]" />
                    <div class="flex flex-col">
                        <span class="text-[8px] font-bold text-[#64748b] uppercase font-bold">Warehouses</span>
                        <span class="text-xs font-black text-[var(--text-primary)]">{{ stats.activeLocations }}</span>
                    </div>
                </div>
             </div>
        </div>
    </PremiumLayout>
</template>
