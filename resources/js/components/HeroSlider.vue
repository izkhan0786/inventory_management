<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronLeft, ChevronRight, Package, TrendingUp, AlertTriangle, ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
    stats: Object
});

const currentSlide = ref(0);
let timer = null;

const slides = [
    {
        title: "Enterprise Inventory Control",
        description: `Your central command for ${props.stats.totalItems.toLocaleString()} high-velocity stock items and ${props.stats.activeLocations} fulfillment centers.`,
        bg: "from-[#0f172a] via-[#1e293b] to-[#0f172a]",
        accent: "text-cyan-400",
        icon: Package,
        button: "View Catalog",
        link: "/products"
    },
    {
        title: "Stock Pressure Alert",
        description: `Surfacing ${props.stats.lowStock} reorder risks and ${props.stats.expiringBatches} expiry deadlines across your stock ledger.`,
        bg: "from-[#1e1b4b] via-[#312e81] to-[#1e1b4b]",
        accent: "text-amber-400",
        icon: AlertTriangle,
        button: "Fix Stock Issues",
        link: "/inventory"
    },
    {
        title: "Revenue Insights",
        description: `Real-time valuation of $${props.stats.totalValue.toLocaleString()} in assets and ${props.stats.pendingOrders} pending order queues.`,
        bg: "from-[#064e3b] via-[#065f46] to-[#064e3b]",
        accent: "text-emerald-400",
        icon: TrendingUp,
        button: "Open Analytics",
        link: "/reports"
    }
];

const next = () => {
    currentSlide.value = (currentSlide.value + 1) % slides.length;
};

const prev = () => {
    currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
};

onMounted(() => {
    timer = setInterval(next, 8000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <div class="relative group overflow-hidden rounded-[2.5rem] border border-white/10 shadow-[0_40px_100px_rgba(0,0,0,0.3)] bg-slate-900 h-[380px] md:h-[420px]">
        <!-- Slides -->
        <div v-for="(slide, index) in slides" :key="index" 
            class="absolute inset-0 transition-all duration-1000 ease-in-out flex items-center px-12"
            :class="[
                index === currentSlide ? 'opacity-100 translate-x-0 z-10' : 'opacity-0 translate-x-12 pointer-events-none',
                `bg-gradient-to-br ${slide.bg}`
            ]"
        >
            <div class="max-w-2xl">
                <div :class="['mb-6 inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-current/20 bg-white/5 font-black uppercase tracking-[0.2em] text-[10px]', slide.accent]">
                    <component :is="slide.icon" class="w-3 h-3" />
                    <span>Live Intelligence</span>
                </div>
                <h2 class="text-4xl md:text-6xl font-black text-white tracking-tight leading-[1.1] mb-6">
                    {{ slide.title }}
                </h2>
                <p class="text-slate-300 text-lg leading-relaxed mb-10 max-w-xl">
                    {{ slide.description }}
                </p>
                <div class="flex items-center gap-4">
                    <a :href="slide.link" class="px-8 py-4 bg-white text-black font-black rounded-2xl hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                        <span>{{ slide.button }}</span>
                        <ArrowUpRight class="w-5 h-5" />
                    </a>
                </div>
            </div>

            <!-- Visual Element -->
            <div class="hidden lg:block absolute right-20 top-1/2 -translate-y-1/2 opacity-20">
                <component :is="slide.icon" class="w-64 h-64 text-white" stroke-width="1" />
            </div>
        </div>

        <!-- Controls -->
        <div class="absolute bottom-10 right-12 z-20 flex items-center gap-3">
            <button @click="prev" class="p-3 rounded-full bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all">
                <ChevronLeft class="w-5 h-5" />
            </button>
            <div class="flex gap-2 mx-4">
                <div v-for="(_, i) in slides" :key="i" 
                    @click="currentSlide = i"
                    class="h-1.5 rounded-full transition-all cursor-pointer"
                    :class="i === currentSlide ? 'w-8 bg-white' : 'w-2 bg-white/20'"
                ></div>
            </div>
            <button @click="next" class="p-3 rounded-full bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all">
                <ChevronRight class="w-5 h-5" />
            </button>
        </div>
        
        <!-- Decorative Glow -->
        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.05),transparent)] pointer-events-none"></div>
    </div>
</template>

<style scoped>
.translate-x-12 { transform: translateX(3rem); }
</style>
