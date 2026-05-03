<script setup>
import { Link } from '@inertiajs/vue3';
import { Package } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

defineProps({
    title: String,
    description: String
});

// Initialize theme logic
const { isDark } = useAppearance();
</script>

<template>
    <div class="min-h-screen bg-[var(--bg-base)] text-[var(--text-primary)] flex flex-col items-center justify-center p-6 relative overflow-hidden transition-colors duration-300">
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-radial-at-c from-[#6366f1]/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
        
        <div class="w-full max-w-[420px] flex flex-col items-center">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-3 mb-10 group">
                <div class="w-12 h-12 rounded-2xl bg-[#6366f1] flex items-center justify-center shadow-lg shadow-[#6366f1]/20 group-hover:scale-110 transition-transform duration-300">
                    <Package class="w-7 h-7 text-white" />
                </div>
                <span class="text-2xl font-bold tracking-tight text-[var(--text-primary)]">InvTracker.</span>
            </Link>

            <!-- Card -->
            <div class="w-full bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2rem] p-10 shadow-2xl relative transition-all duration-300">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-[var(--text-primary)] mb-3">{{ title }}</h1>
                    <p class="text-[var(--text-secondary)] text-sm leading-relaxed">{{ description }}</p>
                </div>

                <slot />
            </div>

            <p class="mt-8 text-[var(--text-tertiary)] text-[10px] text-center leading-relaxed font-bold uppercase tracking-widest opacity-80">
                Protected by InvTracker Shield™ <br/>
                Enterprise Grade Inventory Infrastructure
            </p>
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles removed - using global premium.css utilities */
</style>
