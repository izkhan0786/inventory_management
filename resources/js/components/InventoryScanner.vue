<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Html5QrcodeScanner } from "html5-qrcode";
import { Camera, XCircle } from 'lucide-vue-next';

const emit = defineEmits(['result']);
const scanner = ref(null);
const isActive = ref(false);

const startScanner = () => {
    isActive.value = true;
    scanner.value = new Html5QrcodeScanner(
        "reader", 
        { 
            fps: 25, 
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                return { width: viewfinderWidth * 0.8, height: viewfinderHeight * 0.6 };
            },
            formatsToSupport: [ 
                0, // QR_CODE
                1, // AZTEC
                2, // CODABAR
                3, // CODE_39
                4, // CODE_93
                5, // CODE_128
                6, // DATA_MATRIX
                7, // EAN_8
                8, // EAN_13
                9, // ITF
                10, // MAXICODE
                11, // PDF_417
                12, // RSS_14
                13, // RSS_EXPANDED
                14  // UPC_A
            ],
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true
            },
            rememberLastUsedCamera: true
        },
        false
    );
    
    scanner.value.render((decodedText) => {
        emit('result', decodedText);
        stopScanner();
    }, (error) => {
        // Handle scan errors silently
    });
};

const stopScanner = () => {
    if (scanner.value) {
        scanner.value.clear();
        isActive.value = false;
    }
};

onUnmounted(() => {
    stopScanner();
});
</script>

<template>
    <div class="relative">
        <button 
            @click="isActive ? stopScanner() : startScanner()" 
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-white/10 hover:bg-white/5 transition-all text-sm font-medium"
            :class="isActive ? 'text-rose-400' : 'text-sky-400'"
        >
            <Camera v-if="!isActive" class="w-4 h-4" />
            <XCircle v-else class="w-4 h-4" />
            {{ isActive ? 'Stop Scanner' : 'Live Camera Scan' }}
        </button>

        <!-- Scanner Container -->
        <div v-show="isActive" class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-black/40 backdrop-blur-md p-4">
            <div id="reader" class="w-full"></div>
            <p class="text-center text-xs text-slate-500 mt-2">Position the Barcode/QR code within the frame</p>
        </div>
    </div>
</template>

<style>
/* Styling for the 3rd party library to match our theme */
#reader {
    border: none !important;
}
#reader__dashboard_section_csr button {
    background: #0ea5e9 !important;
    border: none !important;
    color: white !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
    cursor: pointer;
}
#reader__scan_region video {
    border-radius: 12px !important;
}
</style>
