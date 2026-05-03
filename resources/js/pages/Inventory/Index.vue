<script setup>
import InventoryScanner from '@/components/InventoryScanner.vue';
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    ArrowDownLeft, ArrowRightLeft, ArrowUpRight, Barcode, Database, 
    Hash, Loader2, Package, ScanLine, Search, TriangleAlert, 
    Warehouse, X, Plus, Layers, Tag, DollarSign, Info, Trash2
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps({ movements: Array, variants: Array, warehouses: Array, summary: Object, categories: Array });

const showModal = ref(false);
const showDeleteModal = ref(false);
const movementToDelete = ref(null);
const searchQuery = ref('');
const variantSearch = ref('');
const scanFeedback = ref('');
const editingMovement = ref(null);

const form = useForm({
    variant_id: '',
    warehouse_id: '',
    type: 'IN',
    quantity: 1,
    reference_no: '',
    notes: '',
});

const productForm = useForm({}); // Unused now

// Product modal logic removed

const summaryCards = computed(() => [
    { title: 'Ledger Entries', value: props.summary?.movementCount ?? 0, helper: 'Stock movements', tone: 'text-cyan-300 bg-cyan-500/10 border-cyan-400/20', icon: Database },
    { title: 'Inbound Today', value: props.summary?.inboundToday ?? 0, helper: 'Units received', tone: 'text-emerald-300 bg-emerald-500/10 border-emerald-400/20', icon: ArrowUpRight },
    { title: 'Outbound Today', value: props.summary?.outboundToday ?? 0, helper: 'Units dispatched', tone: 'text-rose-300 bg-rose-500/10 border-rose-400/20', icon: ArrowDownLeft },
    { title: 'Low Stock', value: props.summary?.lowStockVariants ?? 0, helper: 'Needs replenishment', tone: 'text-amber-300 bg-amber-500/10 border-amber-400/20', icon: TriangleAlert },
]);

const filteredMovements = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return props.movements;

    return props.movements.filter((movement) => {
        const haystack = [
            movement.variant?.product?.name ?? '',
            movement.variant?.sku ?? '',
            movement.reference_no ?? '',
            movement.warehouse?.name ?? '',
        ].join(' ').toLowerCase();

        return haystack.includes(query);
    });
});

const variantResults = computed(() => {
    const query = variantSearch.value.trim().toLowerCase();
    const variants = props.variants.filter((variant) => {
        if (!query) return true;

        return [
            variant.product?.name ?? '',
            variant.sku ?? '',
            variant.barcode ?? '',
            variant.name ?? '',
        ].join(' ').toLowerCase().includes(query);
    });

    return variants.slice(0, 8);
});

const selectedVariant = computed(() => props.variants.find((variant) => Number(variant.id) === Number(form.variant_id)) ?? null);

watch(showModal, (open) => {
    if (!open) {
        variantSearch.value = '';
        scanFeedback.value = '';
    }
});

const selectVariant = (variant) => {
    form.variant_id = String(variant.id);
    variantSearch.value = `${variant.product?.name || 'Unknown'} - ${variant.sku}`;
    scanFeedback.value = variant.barcode ? `Matched barcode ${variant.barcode}` : `Selected SKU ${variant.sku}`;
};

const cancelEdit = () => {
    editingMovement.value = null;
    showModal.value = false;
    form.reset();
    form.type = 'IN';
    form.quantity = 1;
    variantSearch.value = '';
    scanFeedback.value = '';
};

const startEdit = (movement) => {
    editingMovement.value = movement;
    form.variant_id = String(movement.variant_id);
    form.warehouse_id = String(movement.warehouse_id);
    form.type = movement.type;
    form.quantity = movement.quantity;
    form.reference_no = movement.reference_no || '';
    form.notes = movement.notes || '';
    variantSearch.value = `${movement.variant?.product?.name || 'Unknown'} - ${movement.variant?.sku || ''}`;
    scanFeedback.value = `Editing movement #${movement.id}`;
    showModal.value = true;
};

const handleScannerResult = (result) => {
    const needle = result.trim().toLowerCase();
    const match = props.variants.find((variant) => [variant.sku ?? '', variant.barcode ?? ''].some((value) => value.toLowerCase() === needle));

    if (match) {
        selectVariant(match);
        scanFeedback.value = `Scanner matched ${match.product?.name || 'item'} (${match.sku})`;
        return;
    }

    variantSearch.value = result;
    scanFeedback.value = `No exact barcode match for "${result}". Showing closest SKU results.`;
};

const submit = () => {
    if (editingMovement.value) {
        form.put(route('inventory.update', editingMovement.value.id), {
            onSuccess: () => {
                cancelEdit();
            },
        });

        return;
    }

    form.post(route('inventory.movement'), {
        onSuccess: () => {
            cancelEdit();
        },
    });
};

const confirmDelete = (movement) => {
    movementToDelete.value = movement;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    if (!movementToDelete.value) return;
    
    form.delete(route('inventory.destroy', movementToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            movementToDelete.value = null;
        },
    });
};

const movementModalTitle = computed(() => editingMovement.value ? 'Edit stock movement' : 'Record stock movement');
const movementButtonLabel = computed(() => editingMovement.value ? 'Update Movement' : 'Save Movement');

const getStatusClass = (type) => ({
    IN: 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
    OUT: 'bg-rose-500/10 text-rose-300 border-rose-400/20',
    ADJUSTMENT: 'bg-amber-500/10 text-amber-300 border-amber-400/20',
    TRANSFER: 'bg-sky-500/10 text-sky-300 border-sky-400/20',
}[type] || 'bg-[var(--bg-base)] text-[var(--text-tertiary)] border-[var(--border-color)]');
</script>

<template>
    <Head title="Inventory" />

    <PremiumLayout>
        <div class="flex flex-col gap-6">
            <section class="flex flex-col gap-6 rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.18),_transparent_30%),linear-gradient(135deg,_rgba(8,15,30,0.96),_rgba(17,24,39,0.9))] p-8 shadow-[0_30px_80px_rgba(6,182,212,0.12)] lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <div class="mb-4 flex flex-wrap gap-2">
                        <span class="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-cyan-300">Scanner workflow</span>
                        <span class="rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-emerald-300">Movement ledger</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-white md:text-4xl">Inventory Operations</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">Record stock changes, track warehouse movements, and audit the full history of your inventory assets.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <Link :href="route('products.index')" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-bold text-white transition-all hover:bg-white/10">
                        <Plus class="h-4 w-4" />
                        Add Product
                    </Link>
                    <button @click="showModal = true" class="inline-flex items-center justify-center gap-2 rounded-xl border border-cyan-400/20 bg-cyan-500/10 px-5 py-3 text-sm font-bold text-cyan-100 transition-all hover:bg-cyan-500/20">
                        <ScanLine class="h-4 w-4" />
                        Movement
                    </button>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article v-for="card in summaryCards" :key="card.title" class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-surface)] p-4 shadow-sm">
                    <div :class="['mb-4 inline-flex rounded-xl border p-2.5', card.tone]">
                        <component :is="card.icon" class="h-4 w-4" />
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.1em] text-[var(--text-tertiary)]">{{ card.title }}</p>
                    <p class="mt-1 text-2xl font-black text-[var(--text-primary)]">{{ card.value }}</p>
                </article>
            </section>

            <section class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-surface)] p-4 shadow-sm">
                <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative w-full max-w-md">
                        <Search class="absolute left-3.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-[var(--text-tertiary)]" />
                        <input v-model="searchQuery" type="text" placeholder="Find records..." class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] py-2 pl-10 pr-4 text-xs text-[var(--text-primary)] outline-none focus:border-cyan-400/40" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead>
                            <tr class="border-b border-[var(--border-color)] text-[10px] font-black uppercase tracking-widest text-[var(--text-secondary)]">
                                <th class="px-3 py-3">Item</th>
                                <th class="px-3 py-3">Facility</th>
                                <th class="px-3 py-3">Reference</th>
                                <th class="px-3 py-3 text-center">Type</th>
                                <th class="px-3 py-3 text-right">Qty</th>
                                <th class="px-3 py-3 text-right">Timestamp</th>
                                <th class="px-3 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                            <tr v-for="move in filteredMovements" :key="move.id" class="transition-colors hover:bg-[var(--bg-base)]/50 group">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] text-[var(--text-tertiary)] group-hover:text-cyan-400 transition-colors">
                                            <Package class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-[var(--text-primary)]">{{ move.variant?.product?.name || 'Unknown' }}</p>
                                            <p class="mt-0.5 text-[9px] font-bold text-[var(--text-tertiary)] uppercase tracking-tighter">{{ move.variant?.sku || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="inline-flex items-center gap-1.5 text-[10px] text-[var(--text-secondary)]">
                                        <Warehouse class="h-3 w-3" />
                                        {{ move.warehouse?.name || 'Main' }}
                                    </div>
                                </td>
                                <td class="px-3 py-3"><span class="text-[10px] font-bold text-cyan-300 bg-cyan-500/5 px-2 py-0.5 rounded border border-cyan-400/10">{{ move.reference_no || '-' }}</span></td>
                                <td class="px-3 py-3 text-center"><span class="text-[9px] px-2 py-0.5 rounded-full border border-current opacity-80" :class="getStatusClass(move.type)">{{ move.type }}</span></td>
                                <td class="px-3 py-3 text-right text-xs font-bold" :class="move.type === 'OUT' ? 'text-rose-300' : 'text-emerald-300'">
                                    {{ move.type === 'OUT' ? '-' : '+' }}{{ move.quantity }}
                                </td>
                                <td class="px-3 py-3 text-right text-[10px] text-[var(--text-tertiary)]">{{ new Date(move.created_at).toLocaleDateString() }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2 transition-all">
                                        <button @click="startEdit(move)" class="p-2 hover:bg-cyan-500/10 rounded-xl text-cyan-400 border border-white/5 transition-all">
                                            <Edit class="h-4 w-4" />
                                        </button>
                                        <button @click="confirmDelete(move)" class="p-2 hover:bg-rose-500/10 rounded-xl text-rose-400 border border-white/5 transition-all">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Add Product Modal Removed -->

        <!-- Record Movement Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/90 backdrop-blur-md" @click="showModal = false"></div>
                <div class="relative max-h-[92vh] w-full max-w-4xl overflow-hidden rounded-3xl border border-[var(--border-color)] bg-[var(--bg-surface)] shadow-2xl">
                    <div class="flex items-center justify-between border-b border-[var(--border-color)] bg-[var(--bg-surface-hover)]/30 px-6 py-4">
                        <div>
                            <h2 class="text-xl font-black text-[var(--text-primary)] tracking-tight">{{ movementModalTitle }}</h2>
                            <p class="text-[10px] text-[var(--text-secondary)] uppercase font-bold tracking-wider">Stock Ledger Entry</p>
                        </div>
                        <button @click="showModal = false" class="w-9 h-9 rounded-lg hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="h-4 w-4 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <div class="grid max-h-[calc(92vh-88px)] lg:grid-cols-[0.9fr,1.1fr] overflow-y-auto">
                        <aside class="p-6 bg-[var(--bg-base)]/60 border-b lg:border-b-0 lg:border-r border-[var(--border-color)]">
                            <p class="text-[10px] font-black uppercase text-cyan-400 mb-4">Scanner Input</p>
                            <InventoryScanner @result="handleScannerResult" />
                            <div v-if="scanFeedback" class="mt-4 p-4 rounded-2xl bg-sky-500/10 border border-sky-500/20 flex items-center gap-3 animate-pulse">
                                <Barcode class="w-5 h-5 text-sky-600" />
                                <span class="text-xs font-black text-sky-700 uppercase tracking-widest">{{ scanFeedback }}</span>
                            </div>
                        </aside>

                        <div class="p-6">
                            <form @submit.prevent="submit" class="space-y-5">
                                <div>
                                    <label class="block text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-2">Item Lookup</label>
                                    <div class="relative">
                                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-[var(--text-tertiary)]" />
                                        <input v-model="variantSearch" type="text" placeholder="Search item..." class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] pl-10 py-2.5 text-xs text-[var(--text-primary)] outline-none focus:border-cyan-400/40" />
                                    </div>
                                    <div class="mt-2 grid gap-1.5 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                                        <button v-for="variant in variantResults" :key="variant.id" type="button" @click="selectVariant(variant)" class="flex items-center justify-between rounded-xl border p-2.5 text-left transition-all text-xs" :class="Number(form.variant_id) === Number(variant.id) ? 'border-cyan-400/30 bg-cyan-500/10' : 'border-[var(--border-color)] bg-[var(--bg-base)]/60 hover:bg-[var(--bg-base)]'">
                                            <div>
                                                <p class="font-bold text-[var(--text-primary)]">{{ variant.product?.name }}</p>
                                                <p class="text-[9px] text-[var(--text-tertiary)] uppercase font-mono">{{ variant.sku }}</p>
                                            </div>
                                            <p class="text-[10px] font-black text-cyan-400">{{ variant.stock_qty }}</p>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-2">Type</label>
                                        <div class="flex gap-2">
                                            <button type="button" @click="form.type = 'IN'" class="flex-1 py-2 text-[10px] font-black rounded-lg border transition-all" :class="form.type === 'IN' ? 'border-emerald-400/30 bg-emerald-500/10 text-emerald-200' : 'border-[var(--border-color)] text-[var(--text-tertiary)]'">IN</button>
                                            <button type="button" @click="form.type = 'OUT'" class="flex-1 py-2 text-[10px] font-black rounded-lg border transition-all" :class="form.type === 'OUT' ? 'border-rose-400/30 bg-rose-500/10 text-rose-200' : 'border-[var(--border-color)] text-[var(--text-tertiary)]'">OUT</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-2">Warehouse</label>
                                        <select v-model="form.warehouse_id" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] px-3 py-2 text-xs text-[var(--text-primary)] outline-none" required>
                                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-2">Quantity</label>
                                        <input v-model="form.quantity" type="number" min="1" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] px-3 py-2.5 text-center text-lg font-black text-[var(--text-primary)] outline-none" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[var(--text-tertiary)] mb-2">Ref #</label>
                                        <div class="relative">
                                            <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-cyan-300" />
                                            <input v-model="form.reference_no" type="text" placeholder="PO-123" class="w-full rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] pl-9 py-2.5 text-xs text-[var(--text-primary)] outline-none" />
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 pt-2">
                                    <button type="button" @click="cancelEdit" class="flex-1 py-3 rounded-xl border border-[var(--border-color)] text-[10px] font-black uppercase tracking-widest text-[var(--text-secondary)]">Cancel</button>
                                    <button 
                                        @click="submit" 
                                        :disabled="form.processing || !form.variant_id || !form.warehouse_id" 
                                        class="flex-[2] py-4 rounded-2xl bg-[#10b981] text-[#0f172a] text-xs font-black uppercase tracking-widest shadow-2xl shadow-[#10b981]/20 hover:bg-[#059669] disabled:opacity-30 disabled:cursor-not-allowed transition-all"
                                    >
                                        {{ form.processing ? 'Processing...' : 'Confirm & Save Movement' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
        <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="showDeleteModal = false"></div>
                <div class="relative w-full max-w-md bg-[#0f172a] rounded-[2.5rem] border border-white/10 p-10 shadow-2xl">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 mb-8 animate-pulse border border-rose-500/20">
                            <TriangleAlert class="w-10 h-10" />
                        </div>
                        <h3 class="text-2xl font-black text-white tracking-tighter mb-4">Revert Movement?</h3>
                        <p class="text-slate-400 text-sm font-bold leading-relaxed mb-10">
                            Deleting this record will automatically adjust the current stock of <span class="text-white">"{{ movementToDelete?.variant?.product?.name }}"</span>. This action cannot be undone.
                        </p>
                        
                        <div class="flex flex-col w-full gap-3">
                            <button @click="executeDelete" :disabled="form.processing" class="w-full py-4 rounded-2xl bg-rose-500 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-xl shadow-rose-500/20 disabled:opacity-50">
                                {{ form.processing ? 'Reverting Stock...' : 'Yes, Delete Record' }}
                            </button>
                            <button @click="showDeleteModal = false" class="w-full py-4 rounded-2xl bg-white/5 text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">
                                Keep Record
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style>
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.drawer-enter-active, .drawer-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
.drawer-enter-from .relative, .drawer-leave-to .relative { transform: translateX(100%); }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.2); border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.1); }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.4); }
</style>
