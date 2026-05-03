<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    Scan, Search, Camera, X, CheckCircle2, 
    AlertTriangle, Package, ArrowRightLeft,
    Monitor, Keyboard, RefreshCcw
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { Html5QrcodeScanner } from 'html5-qrcode';

const props = defineProps({
    warehouses: Array
});

const scanResult = ref(null);
const manualBarcode = ref('');
const isScanning = ref(false);
const error = ref(null);
let html5QrcodeScanner = null;

const lookupProduct = async (code) => {
    const barcode = code || manualBarcode.value;
    if (!barcode) return;
    
    try {
        // In a real app, this would be: 
        // const response = await axios.get(`/api/products/lookup/${barcode}`);
        // scanResult.value = response.data;
        
        // Simulating a successful lookup
        scanResult.value = {
            barcode: barcode,
            name: "Premium Inventory Item",
            sku: "SKU-" + barcode.substring(0, 5),
            stock: 150,
            price: 29.99
        };
        isScanning.value = false;
        if (html5QrcodeScanner) html5QrcodeScanner.clear();
    } catch (err) {
        error.value = "Product not found.";
        setTimeout(() => error.value = null, 3000);
    }
};

const onScanSuccess = (decodedText, decodedResult) => {
    lookupProduct(decodedText);
};

const toggleCamera = () => {
    isScanning.value = !isScanning.value;
    if (isScanning.value) {
        setTimeout(() => {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { fps: 10, qrbox: {width: 250, height: 250} },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess);
        }, 100);
    } else {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(error => {
                console.error("Failed to clear html5QrcodeScanner. ", error);
            });
        }
    }
};

onUnmounted(() => {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear().catch(error => {
            console.error("Failed to clear html5QrcodeScanner. ", error);
        });
    }
});

</script>

<template>
    <Head title="Barcode Scanner" />

    <PremiumLayout>
        <div class="flex flex-col gap-8 max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-3xl font-black text-white">Barcode Scanner</h1>
                <p class="text-[var(--text-tertiary)] text-sm">Scan product barcodes or QR codes for instant lookup and stock operations.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Left: Controls -->
                <div class="flex flex-col gap-6">
                    <!-- Manual Entry Card -->
                    <div class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] shadow-2xl relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-[var(--primary)]/5 blur-3xl -mr-10 -mt-10"></div>
                        
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[var(--primary)]">
                                <Keyboard class="w-5 h-5" />
                            </div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-white">Manual Entry</h3>
                        </div>

                        <div class="space-y-4">
                            <p class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Enter barcode manually</p>
                            <div class="flex gap-3">
                                <input 
                                    v-model="manualBarcode" 
                                    type="text" 
                                    placeholder="e.g. 123456789012" 
                                    class="flex-1 bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-5 text-sm text-white focus:border-[var(--primary)] outline-none transition-all placeholder:text-[var(--text-tertiary)]"
                                    @keyup.enter="lookupProduct"
                                />
                                <button @click="lookupProduct" class="px-6 bg-[var(--primary)] text-[#0f172a] rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-[#059669] transition-all flex items-center gap-2 shadow-lg shadow-[var(--primary)]/20">
                                    <Search class="w-4 h-4" /> Search
                                </button>
                            </div>
                            
                            <Transition name="fade">
                                <div v-if="error" class="flex items-center gap-2 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold">
                                    <AlertTriangle class="w-3 h-3" /> {{ error }}
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Camera Section -->
                    <div class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] shadow-2xl">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-[var(--primary)]">
                                <Camera class="w-5 h-5" />
                            </div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-white">Camera Scanner</h3>
                        </div>

                        <p class="text-xs text-[var(--text-tertiary)] mb-8 leading-relaxed">Point your camera at a barcode or QR code to scan. Make sure there is enough light and the code is clearly visible.</p>

                        <div v-if="!isScanning" class="flex flex-col items-center justify-center p-12 border-2 border-dashed border-[var(--border-color)] rounded-[2rem] bg-[var(--bg-base)]/50 group transition-all hover:border-[var(--primary)]/30">
                            <div class="w-20 h-20 rounded-full bg-[var(--primary)]/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                                <Scan class="w-10 h-10 text-[var(--var-primary)] opacity-40" />
                            </div>
                            <p class="text-[10px] font-black uppercase text-[var(--text-tertiary)] tracking-widest mb-8">Enable camera to scan barcodes</p>
                            <button @click="toggleCamera" class="px-8 py-4 bg-[#10b981] text-[#0f172a] rounded-[1.25rem] text-xs font-black uppercase tracking-widest hover:bg-[#059669] transition-all flex items-center gap-3 shadow-xl shadow-[#10b981]/30">
                                <Camera class="w-5 h-5" /> Enable Camera
                            </button>
                        </div>

                        <div v-else class="relative w-full rounded-[2rem] bg-black overflow-hidden border-2 border-[var(--primary)] shadow-[0_0_50px_rgba(16,185,129,0.2)]">
                            <div id="reader" style="width: 100%"></div>
                            
                            <!-- Control Overlays -->
                            <button @click="toggleCamera" class="absolute top-6 right-6 z-10 w-10 h-10 rounded-full bg-black/50 border border-white/10 flex items-center justify-center text-white hover:bg-black/80 transition-all">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Results & Quick Actions -->
                <div class="sticky top-24">
                     <div v-if="!scanResult" class="p-10 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] border-dashed flex flex-col items-center justify-center text-center h-[500px]">
                        <div class="w-16 h-16 rounded-2xl bg-[var(--bg-base)] flex items-center justify-center mb-6">
                            <Monitor class="w-8 h-8 text-[var(--text-tertiary)] opacity-30" />
                        </div>
                        <h4 class="text-sm font-black text-white uppercase tracking-widest mb-2">Scan Barcode</h4>
                        <p class="text-xs text-[var(--text-tertiary)] max-w-[200px]">Enter barcode manually or use camera to view results and perform operations.</p>
                     </div>

                     <div v-else class="p-8 rounded-[2.5rem] bg-[var(--bg-surface)] border border-[var(--border-color)] shadow-2xl animate-in fade-in slide-in-from-right-4 duration-500">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-500">
                                    <CheckCircle2 class="w-5 h-5" />
                                </div>
                                <h3 class="text-sm font-black uppercase tracking-widest text-white">Item Found</h3>
                            </div>
                            <button @click="scanResult = null" class="text-xs font-black uppercase text-[var(--text-tertiary)] hover:text-white transition-colors">Clear</button>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4 p-5 rounded-3xl bg-[var(--bg-base)] border border-[var(--border-color)]">
                                <div class="w-12 h-12 rounded-2xl bg-[var(--primary)]/10 flex items-center justify-center text-[var(--primary)]">
                                    <Package class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-white">{{ scanResult.name }}</h4>
                                    <p class="text-xs text-[var(--text-tertiary)]">{{ scanResult.sku }} • {{ scanResult.barcode }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 rounded-3xl bg-[var(--bg-base)] border border-[var(--border-color)]">
                                    <p class="text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-1">Current Stock</p>
                                    <p class="text-xl font-black text-white">{{ scanResult.stock }} <span class="text-[10px] font-medium text-[var(--text-tertiary)]">Units</span></p>
                                </div>
                                <div class="p-5 rounded-3xl bg-[var(--bg-base)] border border-[var(--border-color)]">
                                    <p class="text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-1">Unit Price</p>
                                    <p class="text-xl font-black text-white">${{ scanResult.price }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <p class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Quick Actions</p>
                                <button class="w-full py-4 bg-white/[0.03] hover:bg-white/[0.08] border border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest text-white transition-all flex items-center justify-center gap-2">
                                    <ArrowRightLeft class="w-4 h-4" /> Record Movement
                                </button>
                                <button class="w-full py-4 bg-[var(--primary)] text-[#0f172a] rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-[#059669] transition-all flex items-center justify-center gap-2 shadow-lg shadow-[var(--primary)]/20">
                                    View Full Details
                                </button>
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>

<style scoped>
@keyframes scan {
  from { top: 0; }
  to { top: 100%; }
}
.animate-scan {
  animation: scan 3s linear infinite;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
