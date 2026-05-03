<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Wrench, Plus, Search, Filter, 
    Edit, Trash2, X, Tag, Hash, 
    MapPin, User, Calendar, DollarSign, 
    Database, Loader2, Info
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    assets: Array,
    users: Array
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingAsset = ref(null);
const searchQuery = ref('');

const filteredAssets = computed(() => {
    if (!searchQuery.value) return props.assets;
    return props.assets.filter(a => 
        a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        a.asset_tag.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const form = useForm({
    name: '',
    asset_tag: '',
    serial_number: '',
    purchase_date: new Date().toISOString().split('T')[0],
    purchase_cost: 0,
    status: 'Available',
    location: '',
    user_id: '',
    notes: ''
});

const editForm = useForm({
    name: '',
    asset_tag: '',
    serial_number: '',
    purchase_date: '',
    purchase_cost: 0,
    status: '',
    location: '',
    user_id: '',
    notes: ''
});

const openEditModal = (asset) => {
    editingAsset.value = asset;
    editForm.name = asset.name;
    editForm.asset_tag = asset.asset_tag;
    editForm.serial_number = asset.serial_number || '';
    editForm.purchase_date = asset.purchase_date || '';
    editForm.purchase_cost = asset.purchase_cost || 0;
    editForm.status = asset.status;
    editForm.location = asset.location || '';
    editForm.user_id = asset.user_id || '';
    editForm.notes = asset.notes || '';
    showEditModal.value = true;
};

const submitAdd = () => {
    form.post(route('assets.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const submitEdit = () => {
    editForm.put(route('assets.update', editingAsset.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        }
    });
};

const deleteAsset = (id) => {
    if (confirm('Are you sure you want to delete this asset?')) {
        router.delete(route('assets.destroy', id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Available': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
        case 'In Use': return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20';
        case 'In Maintenance': return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
        case 'Retired': return 'bg-gray-500/10 text-gray-400 border-gray-500/20';
        case 'Broken': return 'bg-rose-500/10 text-rose-400 border-rose-500/20';
        default: return 'bg-[var(--bg-base)] text-[var(--text-tertiary)]';
    }
};
</script>

<template>
    <Head title="Asset Management" />
    <PremiumLayout>
        <div class="flex flex-col gap-8">
            <!-- Header Area -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">Equipment & Fixed Assets</h1>
                    <p class="text-[var(--text-secondary)] mt-1 text-sm tracking-wide">Track company tools, hardware, and furniture assignments.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="flex items-center gap-2 bg-[#6366f1] hover:bg-[#4f46e5] text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-lg shadow-[#6366f1]/20 transform active:scale-95"
                >
                    <Plus class="w-4 h-4" /> Add Asset
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">Total Assets</span>
                    <div class="text-3xl font-bold mt-1 text-[var(--text-primary)]">{{ assets?.length || 0 }}</div>
                </div>
                <div class="stat-card">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">Assigned</span>
                    <div class="text-3xl font-bold mt-1 text-emerald-400">{{ assets.filter(a => a.status === 'In Use').length }}</div>
                </div>
                <div class="stat-card border-amber-500/20">
                    <span class="text-[var(--text-secondary)] text-xs font-bold uppercase tracking-widest">In Maintenance</span>
                    <div class="text-3xl font-bold mt-1 text-amber-400">{{ assets.filter(a => a.status === 'In Maintenance').length }}</div>
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
                            placeholder="Search by tag or asset name..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:border-[#6366f1] text-[var(--text-primary)] transition-all" 
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[var(--bg-base)] border-b border-[var(--border-color)] text-[var(--text-secondary)] text-[10px] uppercase font-bold tracking-widest">
                                <th class="px-6 py-4">Asset Details</th>
                                <th class="px-6 py-4">Tag / Serial</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Assignment</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                             <tr v-for="asset in filteredAssets" :key="asset.id" class="group hover:bg-[var(--bg-base)] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-[var(--bg-base)] flex items-center justify-center text-[#6366f1]">
                                            <Wrench class="w-5 h-5" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-[var(--text-primary)]">{{ asset.name }}</span>
                                            <span class="text-[10px] text-[var(--text-tertiary)] tracking-tight">{{ asset.location || 'Unknown Location' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-mono font-bold text-[#6366f1]">{{ asset.asset_tag }}</span>
                                        <span class="text-[10px] text-[var(--text-tertiary)] font-mono">{{ asset.serial_number || 'No Serial' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border tracking-widest" :class="getStatusClass(asset.status)">
                                        {{ asset.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="asset.user" class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-500/20 flex items-center justify-center text-[10px] font-bold text-indigo-400">
                                            {{ asset.user.name.charAt(0) }}
                                        </div>
                                        <span class="text-xs font-medium text-[var(--text-primary)]">{{ asset.user.name }}</span>
                                    </div>
                                    <span v-else class="text-[10px] text-[var(--text-tertiary)] italic">Unassigned</span>
                                </td>
                                 <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-100 transition-opacity">
                                        <button 
                                            @click="openEditModal(asset)"
                                            class="p-2 hover:bg-[#6366f1]/10 rounded-lg text-[var(--text-tertiary)] hover:text-[#6366f1] transition-all"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button 
                                            @click="deleteAsset(asset.id)"
                                            class="p-2 hover:bg-red-500/10 rounded-lg text-[var(--text-tertiary)] hover:text-red-400 transition-all"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredAssets || filteredAssets.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-[var(--text-tertiary)]">
                                    <div class="flex flex-col items-center gap-3 py-10 opacity-40">
                                        <Database class="w-12 h-12" />
                                        <span>No equipment found.</span>
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
                            <h2 class="text-2xl font-bold text-[var(--text-primary)]">Register Asset</h2>
                            <p class="text-[var(--text-secondary)] text-sm mt-0.5">Add new hardware or equipment to the corporate registry.</p>
                        </div>
                        <button @click="showAddModal = false" class="w-10 h-10 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="w-5 h-5 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <form @submit.prevent="submitAdd" class="p-8">
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Asset Name</label>
                                <div class="relative">
                                    <Wrench class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input v-model="form.name" type="text" placeholder="e.g. Dell Monitor 27\" class="form-input pl-11 !rounded-xl" required />
                                </div>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Asset Tag #</label>
                                <div class="relative">
                                    <Hash class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input v-model="form.asset_tag" type="text" placeholder="TAG-12345" class="form-input pl-11 !rounded-xl" required />
                                </div>
                            </div>
                             <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Serial Number</label>
                                <input v-model="form.serial_number" type="text" placeholder="SN-XXXX-XXXX" class="form-input !rounded-xl" />
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Current Status</label>
                                <select v-model="form.status" class="form-input !rounded-xl">
                                    <option>Available</option>
                                    <option>In Use</option>
                                    <option>In Maintenance</option>
                                    <option>Retired</option>
                                    <option>Broken</option>
                                </select>
                            </div>

                             <div class="col-span-2 border-t border-[var(--border-color)] pt-6 my-2 font-black text-[10px] uppercase tracking-widest text-[var(--text-tertiary)]">Assignment & Fiscal Details</div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Assign to User</label>
                                <select v-model="form.user_id" class="form-input !rounded-xl">
                                    <option value="">Unassigned</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                </select>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Location</label>
                                <div class="relative">
                                    <MapPin class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input v-model="form.location" type="text" placeholder="Office / Room / Dept" class="form-input pl-11 !rounded-xl" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Purchase Date</label>
                                <input v-model="form.purchase_date" type="date" class="form-input !rounded-xl" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Purchase Cost</label>
                                <div class="relative">
                                    <DollarSign class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input v-model="form.purchase_cost" type="number" step="0.01" class="form-input pl-11 !rounded-xl" />
                                </div>
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
                                Register Asset
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
                            <h2 class="text-2xl font-bold text-[var(--text-primary)]">Update Asset</h2>
                            <p class="text-[var(--text-secondary)] text-sm mt-0.5">Modify record for {{ editingAsset?.asset_tag }}</p>
                        </div>
                        <button @click="showEditModal = false" class="w-10 h-10 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="w-5 h-5 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit" class="p-8">
                        <div class="flex flex-col gap-5 mb-8">
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Asset Name</label>
                                <input v-model="editForm.name" type="text" class="form-input !rounded-xl" required />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Status</label>
                                    <select v-model="editForm.status" class="form-input !rounded-xl">
                                        <option>Available</option>
                                        <option>In Use</option>
                                        <option>In Maintenance</option>
                                        <option>Retired</option>
                                        <option>Broken</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Assigned To</label>
                                    <select v-model="editForm.user_id" class="form-input !rounded-xl">
                                        <option value="">Unassigned</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Location</label>
                                <input v-model="editForm.location" type="text" class="form-input !rounded-xl" />
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-2">Notes</label>
                                <textarea v-model="editForm.notes" class="form-input !rounded-2xl h-24 resize-none pt-3"></textarea>
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

