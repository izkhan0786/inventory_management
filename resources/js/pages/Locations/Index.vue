<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    MapPin, Plus, Search, Edit, Trash2, 
    X, Navigation, CheckCircle2, Building2, AlertCircle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    locations: Object
});

const showModal = ref(false);
const showDeleteConfirm = ref(false);
const deleteTarget = ref(null);
const deleteError = ref('');
const isDeleting = ref(false);
const editingLocation = ref(null);
const searchQuery = ref('');

const form = useForm({
    name: '',
    location: '',
    is_default: false,
});

const openCreateModal = () => {
    editingLocation.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (loc) => {
    editingLocation.value = loc;
    form.name = loc.name;
    form.location = loc.location || '';
    form.is_default = loc.is_default || false;
    showModal.value = true;
};

const submit = () => {
    if (editingLocation.value) {
        form.put(route('locations.update', editingLocation.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('locations.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteLocation = (loc) => {
    deleteTarget.value = loc;
    if (loc.is_default) {
        deleteError.value = 'Cannot delete the default warehouse location. Reassign default status to another location first.';
    } else {
        deleteError.value = '';
    }
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    
    isDeleting.value = true;
    router.delete(route('locations.destroy', deleteTarget.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const filteredLocations = computed(() => {
    const list = props.locations.data || [];
    if (!searchQuery.value) return list;
    return list.filter(c => 
        c.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        c.location?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Locations" />
    <PremiumLayout>
        <div class="max-w-6xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-[var(--text-primary)] tracking-tighter flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[#10b981]/10 flex items-center justify-center text-[#10b981]">
                            <MapPin class="w-6 h-6" />
                        </div>
                        Storage Locations
                    </h1>
                    <p class="text-[var(--text-tertiary)] mt-1.5 text-xs font-bold uppercase tracking-wider">Manage warehouses and inventory zones.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 bg-[#10b981] hover:bg-[#059669] text-[#0f172a] px-6 py-3 rounded-2xl font-black transition-all shadow-xl shadow-[#10b981]/20 text-[10px] uppercase tracking-widest"
                >
                    <Plus class="w-4 h-4" /> Add Location
                </button>
            </div>

            <!-- Content Area -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2.5rem] overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-[var(--border-color)] flex flex-col md:flex-row md:items-center justify-between gap-6 bg-[var(--bg-surface-hover)]/30">
                    <div class="relative flex-1 max-w-sm group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)] transition-colors group-focus-within:text-[#10b981]" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Find location or address..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 pl-12 pr-4 text-xs focus:outline-none focus:border-[#10b981] text-[var(--text-primary)] transition-all placeholder:text-[var(--text-tertiary)]" 
                        />
                    </div>
                </div>

                <div class="p-6 overflow-x-auto">
                    <div v-if="filteredLocations.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="loc in filteredLocations" 
                            :key="loc.id"
                            class="group relative bg-[var(--bg-base)] border border-[var(--border-color)] rounded-3xl p-6 hover:border-[#10b981]/50 transition-all hover:shadow-xl hover:-translate-y-1"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-[#10b981]/10 flex items-center justify-center text-[#10b981] group-hover:bg-[#10b981] group-hover:text-white transition-all duration-300">
                                    <Building2 class="w-6 h-6" />
                                </div>
                                <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEditModal(loc)" class="p-2 hover:bg-[#10b981]/10 rounded-xl text-[var(--text-tertiary)] hover:text-[#10b981] transition-all">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button v-if="!loc.is_default" @click="deleteLocation(loc)" class="p-2 hover:bg-rose-500/10 rounded-xl text-[var(--text-tertiary)] hover:text-rose-400 transition-all">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-sm font-black text-[var(--text-primary)] group-hover:text-[#10b981] transition-colors truncate">{{ loc.name }}</h3>
                                <div v-if="loc.is_default" class="bg-[#10b981]/10 text-[#10b981] px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-tighter border border-[#10b981]/20">DEFAULT</div>
                            </div>
                            <p class="text-[10px] text-[var(--text-tertiary)] mb-6 flex items-center gap-1.5 font-bold">
                                <Navigation class="w-3 h-3 opacity-50" />
                                {{ loc.location || 'No address set' }}
                            </p>

                            <div class="pt-6 border-t border-[var(--border-color)] flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)]">Reliability</span>
                                    <div class="flex gap-0.5 mt-1">
                                        <div v-for="i in 5" :key="i" class="w-3 h-1 rounded-full bg-[#10b981]" :class="{ 'opacity-20': i > 4 }"></div>
                                    </div>
                                </div>
                                <button class="text-[var(--text-tertiary)] hover:text-[#10b981] transition-colors">
                                    <ChevronRight class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                     <div v-else class="py-20 text-center">
                        <div class="w-20 h-20 rounded-full bg-[var(--bg-base)] flex items-center justify-center mx-auto mb-4 border border-[var(--border-color)]">
                            <MapPin class="w-8 h-8 text-[var(--text-tertiary)] opacity-30" />
                        </div>
                        <p class="text-[var(--text-secondary)] font-bold">No locations found matching search.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/80 backdrop-blur-md" @click="showModal = false"></div>
                <div class="relative w-full max-w-md transition-all">
                    <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2.5rem] shadow-2xl overflow-hidden p-1">
                        <div class="p-8 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30 rounded-t-[2.4rem]">
                            <div>
                                <h2 class="text-2xl font-black text-[var(--text-primary)] tracking-tighter">{{ editingLocation ? 'Edit Location' : 'New Location' }}</h2>
                                <p class="text-[var(--text-tertiary)] text-[9px] uppercase font-black tracking-widest mt-1">Storage Node Configuration</p>
                            </div>
                            <button @click="showModal = false" class="w-10 h-10 rounded-2xl hover:bg-[var(--bg-base)] flex items-center justify-center border border-[var(--border-color)] text-[var(--text-tertiary)]">
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="p-8 space-y-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] ml-1">Warehouse Name</label>
                                <div class="relative flex items-center group">
                                    <Building2 class="absolute left-4 w-4 h-4 text-[var(--text-tertiary)] transition-colors group-focus-within:text-[#10b981]" />
                                    <input v-model="form.name" type="text" placeholder="e.g. Main Distribution Center" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl pl-12 pr-4 py-3.5 text-xs focus:outline-none focus:border-[#10b981]/50 transition-all" required />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] ml-1">Physical Address</label>
                                <div class="relative flex items-center group">
                                    <MapPin class="absolute left-4 w-4 h-4 text-[var(--text-tertiary)] transition-colors group-focus-within:text-[#10b981]" />
                                    <input v-model="form.location" type="text" placeholder="e.g. 123 Logistics Way, NY" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl pl-12 pr-4 py-3.5 text-xs focus:outline-none focus:border-[#10b981]/50 transition-all" />
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-4 bg-[var(--bg-base)] rounded-2xl border border-[var(--border-color)]">
                                <input v-model="form.is_default" type="checkbox" id="is_default" class="w-5 h-5 rounded-lg border-[var(--border-color)] bg-[var(--bg-base)] text-[#10b981] focus:ring-[#10b981]/50" />
                                <label for="is_default" class="text-xs font-bold text-[var(--text-secondary)] select-none">Set as Primary Default Location</label>
                            </div>

                            <div class="pt-4 flex gap-4">
                                <button type="button" @click="showModal = false" class="flex-1 py-4 rounded-2xl border border-[var(--border-color)] font-black text-[var(--text-tertiary)] text-[10px] uppercase tracking-widest hover:bg-[var(--bg-surface-hover)] transition-all">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="flex-[2] py-4 rounded-2xl bg-[#10b981] text-[#0f172a] font-black text-[10px] uppercase tracking-widest shadow-xl shadow-[#10b981]/20 transition-all hover:bg-[#059669]">
                                    {{ editingLocation ? 'Update Node' : 'Initialize Node' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="modal">
            <div v-if="showDeleteConfirm" class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-hidden">
                <div class="absolute inset-0 bg-[#0f1115]/90 backdrop-blur-md transition-opacity" @click="showDeleteConfirm = false"></div>
                <div class="relative w-full max-w-sm bg-[var(--bg-surface)] rounded-[2.5rem] border border-[var(--border-color)] p-10 shadow-2xl overflow-hidden text-center">
                    <div v-if="deleteError" class="flex flex-col items-center text-center">
                        <div class="w-20 h-20 rounded-3xl bg-amber-500/10 flex items-center justify-center text-amber-500 mb-8 mx-auto">
                            <AlertCircle class="w-10 h-10" />
                        </div>
                        <h3 class="text-xl font-black text-[var(--text-primary)] tracking-tighter mb-3">Action Blocked</h3>
                        <p class="text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest leading-relaxed mb-10">
                            {{ deleteError }}
                        </p>
                        <button @click="showDeleteConfirm = false" class="w-full py-4 rounded-2xl bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 transition-all shadow-xl shadow-amber-500/20">
                            Understood
                        </button>
                    </div>

                    <div v-else class="flex flex-col items-center text-center">
                        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 mb-8 mx-auto">
                            <AlertCircle class="w-10 h-10" />
                        </div>
                        <h3 class="text-xl font-black text-[var(--text-primary)] tracking-tighter mb-3">Decommission Node?</h3>
                        <p class="text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest leading-relaxed mb-10">
                            Are you sure you want to remove "{{ deleteTarget?.name }}"? All records linked to this location will be affected.
                        </p>
                        
                        <div class="flex flex-col w-full gap-3">
                            <button @click="confirmDelete" :disabled="isDeleting" class="w-full py-4 rounded-2xl bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-xl shadow-rose-500/20 disabled:opacity-50 flex items-center justify-center gap-2">
                                <Loader2 v-if="isDeleting" class="w-3 h-3 animate-spin" />
                                {{ isDeleting ? 'Processing...' : 'Confirm Deletion' }}
                            </button>
                            <button @click="showDeleteConfirm = false" :disabled="isDeleting" class="w-full py-4 rounded-2xl bg-[var(--bg-base)] text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest hover:bg-[var(--border-color)] transition-all disabled:opacity-50">
                                Keep Location
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
</style>
