<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Package, Shield, Globe, Zap, ArrowRight, CheckCircle } from 'lucide-vue-next';
</script>

<template>
    <Head title="Enterprise Inventory Software" />
    
    <div class="min-h-screen bg-[#0f1115] text-[#f8fafc] font-sans selection:bg-[#6366f1]/30">
        <!-- Navigation -->
        <nav class="flex items-center justify-between px-8 lg:px-20 h-20 border-b border-white/5 bg-[#0f1115]/80 backdrop-blur-md sticky top-0 z-50">
            <div class="flex items-center gap-2">
                <Package class="w-8 h-8 text-[#6366f1]" />
                <span class="text-xl font-bold tracking-tight">InvTracker.</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-[#94a3b8]">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
                <a href="#demo" class="hover:text-white transition-colors">Live Demo</a>
            </div>
            
            <div class="flex items-center gap-4">
                <Link href="/login" class="text-sm font-bold text-[#94a3b8] hover:text-white transition-colors">Log In</Link>
                <Link href="/register" class="px-5 py-2.5 rounded-full bg-[#6366f1] hover:bg-[#4f46e5] text-white text-sm font-bold transition-all shadow-lg shadow-[#6366f1]/20">Get Started</Link>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative px-8 lg:px-20 pt-20 pb-32 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-radial-at-t from-[#6366f1]/20 to-transparent blur-3xl pointer-events-none -z-10"></div>
            
            <div class="max-w-4xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#6366f1]/10 border border-[#6366f1]/20 text-[#6366f1] text-[10px] font-bold uppercase tracking-widest mb-6 anim-fade-in">
                    <Zap class="w-3 h-3" /> Now with Offline LAN Sync
                </div>
                <h1 class="text-5xl lg:text-7xl font-bold tracking-tight mb-8 leading-[1.1] anim-slide-up">
                    Enterprise Inventory <br/> 
                    <span class="bg-gradient-to-r from-[#6366f1] via-[#a855f7] to-[#6366f1] bg-clip-text text-transparent">No Subscription Required.</span>
                </h1>
                <p class="text-xl text-[#94a3b8] mb-10 max-w-2xl mx-auto anim-slide-up delay-100">
                    Take full control of your assets, lots, and audits. Pay once, use forever on your own local network or cloud.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 anim-slide-up delay-200">
                    <Link href="/register" class="w-full sm:w-auto px-8 py-4 rounded-full bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold transition-all shadow-xl shadow-[#6366f1]/20 flex items-center justify-center gap-2">
                        Start Your Inventory <ArrowRight class="w-4 h-4" />
                    </Link>
                    <a href="#demo" class="w-full sm:w-auto px-8 py-4 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 font-bold transition-all flex items-center justify-center gap-2">
                        View Demo
                    </a>
                </div>
            </div>
            
            <!-- Mockup Preview -->
            <div class="mt-20 relative max-w-5xl mx-auto group anim-fade-in delay-300">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0f1115] via-transparent to-transparent z-10"></div>
                <div class="rounded-2xl border border-white/10 overflow-hidden shadow-2xl shadow-[#6366f1]/10 bg-[#1a1d24]">
                    <div class="h-10 border-b border-white/5 bg-white/5 flex items-center px-4 gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500/20"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-500/20"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-500/20"></div>
                    </div>
                    <div class="p-4 lg:p-10 opacity-60 grayscale group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-700">
                        <!-- Simulated Dashboard UI -->
                        <div class="grid grid-cols-4 gap-4 mb-8">
                            <div v-for="i in 4" :key="i" class="h-24 rounded-xl bg-white/5 border border-white/5"></div>
                        </div>
                        <div class="h-64 rounded-xl bg-white/5 border border-white/5"></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features -->
        <section id="features" class="px-8 lg:px-20 py-32 bg-[#1a1d24]/50">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#6366f1]/10 flex items-center justify-center border border-[#6366f1]/20">
                            <Shield class="w-6 h-6 text-[#6366f1]" />
                        </div>
                        <h3 class="text-xl font-bold">Data Sovereignty</h3>
                        <p class="text-[#94a3b8] leading-relaxed">No cloud dependencies. Your data stays on your local machine or private server. Perfect for sensitive inventory tracking.</p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#a855f7]/10 flex items-center justify-center border border-[#a855f7]/20">
                            <Globe class="w-6 h-6 text-[#a855f7]" />
                        </div>
                        <h3 class="text-xl font-bold">Multi-Location</h3>
                        <p class="text-[#94a3b8] leading-relaxed">Manage multiple warehouses, storerooms, and display areas from a single unified interface with real-time transfer tracking.</p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#10b981]/10 flex items-center justify-center border border-[#10b981]/20">
                            <CheckCircle class="w-6 h-6 text-[#10b981]" />
                        </div>
                        <h3 class="text-xl font-bold">Integrity Auditing</h3>
                        <p class="text-[#94a3b8] leading-relaxed">Every stock movement is cryptographically logged. Know exactly when, where, and who touched your inventory.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-white/5 px-8 lg:px-20 py-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-2">
                <Package class="w-5 h-5 text-[#6366f1]" />
                <span class="font-bold opacity-80">InvTracker.</span>
            </div>
            <p class="text-sm text-[#64748b]">© 2026 InvTracker Software. Professional Grade Portfolio Piece.</p>
        </footer>
    </div>
</template>

<style scoped>
.anim-fade-in { animation: fadeIn 1s ease-out forwards; }
.anim-slide-up { animation: slideUp 0.8s ease-out forwards; opacity: 0; }
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
