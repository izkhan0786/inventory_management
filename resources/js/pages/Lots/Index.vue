<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, X, Package, Database, Loader2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    batches: Array,
    variants: Array,
    warehouses: Array
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingBatch = ref(null);
const searchQuery = ref('');

const filteredBatches = computed(() => {
    if (!searchQuery.value) return props.batches;
    return props.batches.filter(b => 
        b.batch_number.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        b.variant?.product?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const form = useForm({
    batch_number: '',
    variant_id: '',
    warehouse_id: '',
    manufacturing_date: '',
    expiry_date: '',
    initial_qty: 0,
    current_qty: 0
});

const editForm = useForm({
    batch_number: '',
    variant_id: '',
    warehouse_id: '',
    manufacturing_date: '',
    expiry_date: '',
    current_qty: 0
});

const openEditModal = (batch) => {
    editingBatch.value = batch;
    editForm.batch_number = batch.batch_number;
    editForm.variant_id = batch.variant_id;
    editForm.warehouse_id = batch.warehouse_id || '';
    editForm.manufacturing_date = batch.manufacturing_date || '';
    editForm.expiry_date = batch.expiry_date || '';
    editForm.current_qty = batch.current_qty;
    showEditModal.value = true;
};

const submitAdd = () => {
    form.post(route('lots.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const submitEdit = () => {
    editForm.put(route('lots.update', editingBatch.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteBatch = (id) => {
    if (confirm('Are you sure you want to delete this batch record?')) {
        router.delete(route('lots.destroy', id));
    }
};

const getStatus = (expiryDate) => {
    if (!expiryDate) return { text: 'Unknown', class: 'bg-gray-500/10 text-gray-400 border-gray-500/20' };
    const days = Math.ceil((new Date(expiryDate) - new Date()) / (1000 * 60 * 60 * 24));
    if (days < 0) return { text: 'Expired', class: 'bg-rose-500/10 text-rose-500 border-rose-500/20' };
    if (days < 30) return { text: 'Expiring Soon', class: 'bg-amber-500/10 text-amber-500 border-amber-500/20' };
    return { text: 'Healthy', class: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' };
};
</script>

<template>
    <Head title="Lot Tracking" />
    <PremiumLayout>
        <div class="flex flex-col gap-8">
            <!-- Header Area -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">Lot & Batch Tracking</h1>
                    <p class="text-[var(--text-secondary)] mt-1 text-sm tracking-wide">Monitor item longevity, quality assurance, and expiration cycles.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="flex items-center gap-2 bg-[#6366f1] hover:bg-[#4f46e5] text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-[#6366f1]/20 transform active:scale-95"
                >
                    <Plus class="w-4 h-4" /> New Batch
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">Total Lots</span>
                    <div class="text-3xl font-bold mt-1 text-[var(--text-primary)]">{{ batches?.length || 0 }}</div>
                </div>
                <div class="stat-card border-rose-500/20">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">Expired / Critical</span>
                    <div class="text-3xl font-bold mt-1 text-rose-400">
                        {{ batches.filter(b => (new Date(b.expiry_date) - new Date()) / (1000*60*60*24) < 30).length }}
                    </div>
                </div>
                <div class="stat-card border-emerald-500/20">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">Healthy Inventory</span>
                     <div class="text-3xl font-bold mt-1 text-emerald-400">
                        {{ batches.filter(b => (new Date(b.expiry_date) - new Date()) / (1000*60*60*24) >= 30).length }}
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-[var(--border-color)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by lot number or product..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-[#6366f1] text-[var(--text-primary)] transition-all" 
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left font-sans">
                        <thead>
                            <tr class="bg-[var(--bg-base)] border-b border-[var(--border-color)] text-[var(--text-secondary)] text-[10px] uppercase font-bold tracking-widest">
                                <th class="px-6 py-4">Product Variant</th>
                                <th class="px-6 py-4">Lot ID</th>
                                <th class="px-6 py-4">Qty On Hand</th>
                                <th class="px-6 py-4">Expiration / Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                             <tr v-for="batch in filteredBatches" :key="batch.id" class="group hover:bg-[var(--bg-base)] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-[var(--bg-base)] flex items-center justify-center text-[#6366f1]">
                                            <Package class="w-5 h-5" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-[var(--text-primary)]">{{ batch.variant?.product?.name || 'Unknown' }}</span>
                                            <span class="text-[10px] text-[var(--text-tertiary)] tracking-tight">{{ batch.variant?.name || 'Default' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-bold text-[#6366f1] bg-[#6366f1]/10 px-2 py-1 rounded">{{ batch.batch_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-[var(--text-primary)]">{{ batch.current_qty }}</span>
                                        <span class="text-[9px] text-[var(--text-tertiary)] uppercase font-bold tracking-tighter">Initial: {{ batch.initial_qty }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium" :class="new Date(batch.expiry_date) < new Date() ? 'text-rose-400' : 'text-[var(--text-primary)]'">
                                            {{ batch.expiry_date || 'N/A' }}
                                        </span>
                                        <span class="w-min px-2 py-0.5 rounded-full text-[9px] font-black uppercase border tracking-widest whitespace-nowrap" :class="getStatus(batch.expiry_date).class">
                                            {{ getStatus(batch.expiry_date).text }}
                                        </span>
                                    </div>
                                </td>
                                 <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-100 transition-opacity">
                                        <button 
                                            @click="openEditModal(batch)"
                                            class="p-2 hover:bg-[#6366f1]/10 rounded-lg text-[var(--text-tertiary)] hover:text-[#6366f1] transition-all"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteBatch(batch.id)"
                                            class="p-2 hover:bg-red-500/10 rounded-lg text-[var(--text-tertiary)] hover:text-red-400 transition-all"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredBatches || filteredBatches.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-[var(--text-tertiary)] italic">
                                    <div class="flex flex-col items-center gap-3 py-10 opacity-40">
                                        <Database class="w-12 h-12" />
                                        <span>No batches found.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <Transition name="modal">
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/80 backdrop-blur-sm" @click="showAddModal = false"></div>
                <div class="relative w-full max-w-2xl bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl overflow-hidden anim-pop-in">
                    <div class="p-8 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30">
                        <div>
                            <h2 class="text-2xl font-bold text-[var(--text-primary)]">New Inventory Lot</h2>
                            <p class="text-[var(--text-secondary)] text-sm mt-0.5">Register a new product batch with manufacturing and expiry tracking.</p>
                        </div>
                        <button @click="showAddModal = false" class="w-10 h-10 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="w-5 h-5 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <form @submit.prevent="submitAdd" class="p-8">
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Lot / Batch #</label>
                                <input v-model="form.batch_number" type="text" placeholder="B-000-000" class="form-input !rounded-xl text-center font-mono font-bold tracking-widest uppercase" required />
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Product Variant</label>
                                <select v-model="form.variant_id" class="form-input !rounded-xl" required>
                                    <option value="" disabled>Select Product...</option>
                                    <option v-for="v in variants" :key="v.id" :value="v.id">{{ v.product?.name }} - {{ v.name }}</option>
                                </select>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Warehouse</label>
                                <select v-model="form.warehouse_id" class="form-input !rounded-xl" required>
                                    <option value="" disabled>Select location...</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
                                </select>
                            </div>
                            <div class="col-span-2 md:col-span-1 text-emerald-400">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Initial Quantity</label>
                                <input v-model="form.initial_qty" type="number" class="form-input !rounded-xl" required @input="form.current_qty = form.initial_qty" />
                            </div>

                             <div class="col-span-2 border-t border-[var(--border-color)] pt-6 my-2 font-black text-[10px] uppercase tracking-widest text-[var(--text-tertiary)]">Temporal Cycle Data</div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Manufacturing Date</label>
                                <input v-model="form.manufacturing_date" type="date" class="form-input !rounded-xl" />
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2 border-rose-500/50">Expiraion Date</label>
                                <input v-model="form.expiry_date" type="date" class="form-input !rounded-xl border-rose-500/20" />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" @click="showAddModal = false" class="flex-1 py-3.5 rounded-xl border border-[var(--border-color)] font-bold text-[var(--text-secondary)]">Cancel</button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 py-3.5 rounded-xl bg-[#6366f1] text-white font-bold hover:bg-[#4f46e5] shadow-lg shadow-[#6366f1]/20 transition-all flex items-center justify-center gap-2"
                            >
                                <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                Initialize Lot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <!-- Edit Modal -->
        <Transition name="modal">
            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/80 backdrop-blur-sm" @click="showEditModal = false"></div>
                <div class="relative w-full max-w-lg bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl overflow-hidden anim-pop-in">
                    <div class="p-8 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30">
                        <div>
                            <h2 class="text-2xl font-bold text-[var(--text-primary)]">Modify Lot</h2>
                            <p class="text-[var(--text-secondary)] text-sm mt-0.5">Update parameters for Lot #{{ editingBatch?.batch_number }}</p>
                        </div>
                        <button @click="showEditModal = false" class="w-10 h-10 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="w-5 h-5 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit" class="p-8">
                        <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Current Qty On Hand</label>
                                <input v-model="editForm.current_qty" type="number" class="form-input !rounded-xl" required />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Warehouse</label>
                                <select v-model="editForm.warehouse_id" class="form-input !rounded-xl" required>
                                    <option value="" disabled>Select location...</option>
                                    <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">{{ warehouse.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Manufacturing Date</label>
                                <input v-model="editForm.manufacturing_date" type="date" class="form-input !rounded-xl" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Updated Expiry Date</label>
                                <input v-model="editForm.expiry_date" type="date" class="form-input !rounded-xl" />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="button" @click="showEditModal = false" class="flex-1 py-3.5 rounded-xl border border-[var(--border-color)] font-bold text-[var(--text-secondary)]">Cancel</button>
                            <button 
                                type="submit" 
                                :disabled="editForm.processing"
                                class="flex-1 py-3.5 rounded-xl bg-[#6366f1] text-white font-bold hover:bg-[#4f46e5] shadow-lg shadow-[#6366f1]/20 transition-all flex items-center justify-center gap-2"
                            >
                                <Loader2 v-if="editForm.processing" class="w-4 h-4 animate-spin" />
                                Save Updates
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

    </PremiumLayout>
</template>

<style>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
@keyframes popIn { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.anim-pop-in { animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
</style>

