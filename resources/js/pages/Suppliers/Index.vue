<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Users, Plus, Search, Edit, Trash2, 
    X, Mail, Phone, MapPin, ExternalLink,
    UserCircle, MoreHorizontal, AlertCircle, Building2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: Object
});

const showModal = ref(false);
const showDeleteConfirm = ref(false);
const deleteTarget = ref(null);
const editingSupplier = ref(null);
const searchQuery = ref('');

const form = useForm({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
});

const openCreateModal = () => {
    editingSupplier.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (supplier) => {
    editingSupplier.value = supplier;
    form.name = supplier.name;
    form.contact_person = supplier.contact_person || '';
    form.email = supplier.email || '';
    form.phone = supplier.phone || '';
    form.address = supplier.address || '';
    form.notes = supplier.notes || '';
    showModal.value = true;
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('suppliers.update', editingSupplier.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteSupplier = (id) => {
    deleteTarget.value = props.suppliers.data.find(s => s.id === id);
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    
    router.delete(route('suppliers.destroy', deleteTarget.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteTarget.value = null;
        }
    });
};

const filteredSuppliers = computed(() => {
    const list = props.suppliers.data || [];
    if (!searchQuery.value) return list;
    return list.filter(s => 
        s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        s.contact_person?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        s.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Suppliers" />
    <PremiumLayout>
        <div class="flex flex-col gap-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-4xl font-black text-[var(--text-primary)] tracking-tighter flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#10b981]/10 flex items-center justify-center text-[#10b981]">
                            <Users class="w-7 h-7" />
                        </div>
                        Strategic Partners
                    </h1>
                    <p class="text-[var(--text-tertiary)] mt-2 text-xs font-bold uppercase tracking-[0.2em]">Supplier Directory & Contact Management</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 bg-[#10b981] hover:bg-[#059669] text-[#0f172a] px-8 py-4 rounded-2xl font-black transition-all shadow-xl shadow-[#10b981]/20 text-xs uppercase tracking-widest active:scale-95"
                >
                    <Plus class="w-4 h-4" /> Add New Supplier
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[3rem] overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-[var(--border-color)] flex flex-col md:flex-row md:items-center justify-between gap-6 bg-[var(--bg-surface-hover)]/30">
                    <div class="relative flex-1 max-w-md group">
                        <Search class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)] group-focus-within:text-[#10b981] transition-colors" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by company, person or email..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-[1.25rem] py-4 pl-14 pr-6 text-xs text-[var(--text-primary)] outline-none focus:border-[#10b981]/40 transition-all placeholder:text-[var(--text-tertiary)]" 
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[var(--border-color)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] bg-[var(--bg-surface-hover)]/20">
                                <th class="pl-10 pr-4 py-6">Company Entity</th>
                                <th class="px-4 py-6">Primary Contact</th>
                                <th class="px-4 py-6">Contact Channels</th>
                                <th class="px-4 py-6">Location</th>
                                <th class="pl-4 pr-10 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                            <tr v-for="supplier in filteredSuppliers" :key="supplier.id" class="group hover:bg-[#10b981]/5 transition-colors duration-300">
                                <td class="pl-10 pr-4 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-[1.25rem] bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center group-hover:bg-[#10b981] group-hover:text-white transition-all">
                                            <Building2 v-if="false" class="w-6 h-6" />
                                            <span class="text-lg font-black uppercase">{{ supplier.name.substring(0, 1) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-[var(--text-primary)] group-hover:text-[#10b981] transition-colors line-clamp-1">{{ supplier.name }}</span>
                                            <span class="text-[10px] font-bold text-[var(--text-tertiary)] uppercase tracking-wider opacity-60">Vendor Entity</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex items-center gap-2 text-[var(--text-secondary)]">
                                        <UserCircle class="w-4 h-4 opacity-40 text-[#10b981]" />
                                        <span class="text-xs font-bold">{{ supplier.contact_person || 'No Contact Person' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex flex-col gap-1.5">
                                        <div v-if="supplier.email" class="flex items-center gap-2 text-[10px] text-[var(--text-tertiary)] hover:text-[#10b981] transition-colors">
                                            <Mail class="w-3.5 h-3.5" />
                                            {{ supplier.email }}
                                        </div>
                                        <div v-if="supplier.phone" class="flex items-center gap-2 text-[10px] text-[var(--text-tertiary)] hover:text-[#10b981] transition-colors">
                                            <Phone class="w-3.5 h-3.5" />
                                            {{ supplier.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6 max-w-[200px]">
                                    <div class="flex items-start gap-2">
                                        <MapPin class="w-3.5 h-3.5 mt-0.5 text-[var(--text-tertiary)] shrink-0" />
                                        <span class="text-[10px] text-[var(--text-secondary)] line-clamp-2 leading-relaxed italic">{{ supplier.address || 'Address not listed' }}</span>
                                    </div>
                                </td>
                                <td class="pl-4 pr-10 py-6 text-right">
                                    <div class="flex justify-end gap-2.5">
                                        <button @click="openEditModal(supplier)" class="p-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] text-[var(--text-tertiary)] hover:text-[#10b981] hover:border-[#10b981]/50 transition-all active:scale-90">
                                            <Edit class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteSupplier(supplier.id)" class="p-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--bg-base)] text-[var(--text-tertiary)] hover:text-rose-500 hover:border-rose-500/50 transition-all active:scale-90">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredSuppliers.length === 0">
                                <td colspan="5" class="py-20 text-center">
                                    <div class="w-20 h-20 rounded-full bg-[var(--bg-base)] flex items-center justify-center mx-auto mb-4 border border-[var(--border-color)] text-[var(--text-tertiary)] opacity-30">
                                        <Users class="w-10 h-10" />
                                    </div>
                                    <p class="text-[var(--text-secondary)] font-black uppercase text-xs tracking-widest">No partners registered yet</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Supplier Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/80 backdrop-blur-md" @click="showModal = false"></div>
                <div class="relative w-full max-w-2xl transition-all">
                    <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[3rem] shadow-2xl overflow-hidden">
                        <div class="p-10 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30">
                            <div>
                                <h2 class="text-3xl font-black text-[var(--text-primary)] tracking-tighter">{{ editingSupplier ? 'Update Partner' : 'New Strategic Partner' }}</h2>
                                <p class="text-[var(--text-tertiary)] text-[9px] uppercase font-black tracking-[0.3em] mt-1">Entity Details & Supply Chain Profile</p>
                            </div>
                            <button @click="showModal = false" class="w-12 h-12 rounded-2xl hover:bg-[var(--bg-base)] flex items-center justify-center border border-[var(--border-color)] text-[var(--text-tertiary)] transition-all">
                                <X class="w-6 h-6" />
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="p-10">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">Entity Name</label>
                                        <input v-model="form.name" type="text" placeholder="Legal company name" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none" required />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">Contact Person</label>
                                        <input v-model="form.contact_person" type="text" placeholder="Point of contact name" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">Email Domain</label>
                                        <input v-model="form.email" type="email" placeholder="official@company.com" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none" />
                                    </div>
                                </div>
                                <div class="space-y-6">
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">Secure Line (Phone)</label>
                                        <input v-model="form.phone" type="text" placeholder="+1 (000) 000-0000" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">HQ Address</label>
                                        <textarea v-model="form.address" rows="1" placeholder="Physical location" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none resize-none"></textarea>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] ml-1">Strategic Notes</label>
                                        <textarea v-model="form.notes" rows="1" placeholder="Internal remarks..." class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-2xl px-5 py-4 text-xs focus:border-[#10b981]/50 outline-none resize-none"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <button type="button" @click="showModal = false" class="flex-1 py-5 rounded-[1.25rem] border border-[var(--border-color)] font-black text-[var(--text-tertiary)] text-[10px] uppercase tracking-[0.2em] hover:bg-[var(--bg-surface-hover)] transition-all">Cancel Operation</button>
                                <button type="submit" :disabled="form.processing" class="flex-[2] py-5 rounded-[1.25rem] bg-[#10b981] text-[#0f172a] font-black text-[10px] uppercase tracking-[0.2em] shadow-2xl shadow-[#10b981]/30 transition-all hover:bg-[#059669]">
                                    {{ editingSupplier ? 'Execute Update' : 'Initialize Partner Link' }}
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
                    <div class="w-20 h-20 rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 mb-8 mx-auto">
                        <AlertCircle class="w-10 h-10" />
                    </div>
                    <h3 class="text-xl font-black text-[var(--text-primary)] tracking-tighter mb-3">Terminate Link?</h3>
                    <p class="text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest leading-relaxed mb-10">
                        Are you sure you want to remove "{{ deleteTarget?.name }}"? This will archive all historical partner data.
                    </p>
                    
                    <div class="flex flex-col w-full gap-3">
                        <button @click="confirmDelete" class="w-full py-4 rounded-2xl bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-xl shadow-rose-500/20">
                            Confirm Termination
                        </button>
                        <button @click="showDeleteConfirm = false" class="w-full py-4 rounded-2xl bg-[var(--bg-base)] text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest hover:bg-[var(--border-color)] transition-all">
                            Keep Partner
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.4s cubic-bezier(0.2, 1, 0.2, 1); }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: translateY(20px) scale(0.98); }
</style>
