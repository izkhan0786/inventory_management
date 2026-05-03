<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Tag, Plus, Search, Edit, Trash2, 
    Layers, Link as LinkIcon, X, Loader2,
    Database, ChevronRight, AlertCircle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    categories: Array
});

const showModal = ref(false);
const showDeleteConfirm = ref(false);
const deleteTarget = ref(null);
const deleteError = ref('');
const isDeleting = ref(false);
const editingCategory = ref(null);
const searchQuery = ref('');

const form = useForm({
    name: '',
    description: '',
    color_hex: '#6366f1',
    parent_id: '',
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description || '';
    form.color_hex = category.color_hex || '#6366f1';
    form.parent_id = category.parent_id || '';
    showModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteCategory = (category) => {
    deleteTarget.value = category;
    if (category.products_count > 0) {
        deleteError.value = 'Cannot delete category with associated products. Move or delete products first.';
    } else {
        deleteError.value = '';
    }
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    
    isDeleting.value = true;
    router.delete(route('categories.destroy', deleteTarget.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteTarget.value = null;
        },
        onFinish: () => {
            isDeleting.value = false;
        }
    });
};

const filteredCategories = computed(() => {
    const list = props.categories || [];
    if (!searchQuery.value) return list;
    return list.filter(c => 
        c.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Categories" />
    <PremiumLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--text-primary)] flex items-center gap-2.5">
                        <Tag class="w-6 h-6 text-[#6366f1]" />
                        Catalog Categories
                    </h1>
                    <p class="text-[var(--text-secondary)] mt-0.5 text-xs">Organize your inventory catalog.</p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="flex items-center justify-center gap-2 bg-[#6366f1] hover:bg-[#4f46e5] text-white px-4 py-2 rounded-xl font-bold transition-all shadow-lg shadow-[#6366f1]/20 text-xs"
                >
                    <Plus class="w-4 h-4" /> Add Category
                </button>
            </div>

            <!-- Content Area -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 border-b border-[var(--border-color)] flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[var(--bg-surface-hover)]/30">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Find category..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2 pl-10 pr-4 text-xs focus:outline-none focus:border-[#6366f1] text-[var(--text-primary)] transition-all" 
                        />
                    </div>
                </div>

                <div class="p-4 overflow-x-auto">
                    <div v-if="filteredCategories.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="category in filteredCategories" 
                            :key="category.id"
                            class="group relative bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl p-4 hover:border-[#6366f1]/50 transition-all hover:shadow-md"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div 
                                    class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-sm"
                                    :style="{ backgroundColor: category.color_hex || '#6366f1' }"
                                >
                                    <Layers class="w-5 h-5" />
                                </div>
                                <div class="flex gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEditModal(category)" class="p-1.5 hover:bg-[#6366f1]/10 rounded-lg text-[var(--text-tertiary)] hover:text-[#6366f1]">
                                        <Edit class="w-3.5 h-3.5" />
                                    </button>
                                    <button @click="deleteCategory(category)" class="p-1.5 hover:bg-red-500/10 rounded-lg text-[var(--text-tertiary)] hover:text-red-400">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>

                            <h3 class="text-sm font-bold text-[var(--text-primary)] mb-1 group-hover:text-[#6366f1] transition-colors truncate">{{ category.name }}</h3>
                            <p class="text-[10px] text-[var(--text-secondary)] mb-4 line-clamp-2 h-7 overflow-hidden uppercase font-black opacity-60 tracking-wider">
                                {{ category.products_count }} products
                            </p>

                            <div class="pt-3 border-t border-[var(--border-color)] flex items-center justify-between">
                                <span class="text-[9px] font-black uppercase tracking-widest text-[var(--text-tertiary)]">Catalog Group</span>
                                <div 
                                    class="w-2.5 h-2.5 rounded-full"
                                    :style="{ backgroundColor: category.color_hex || '#6366f1' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/90 backdrop-blur-md" @click="showModal = false"></div>
                <div class="relative w-full max-w-md transition-all">
                    <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl overflow-hidden">
                        <div class="p-6 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30">
                            <div>
                                <h2 class="text-xl font-black text-[var(--text-primary)] tracking-tight">Category Details</h2>
                                <p class="text-[var(--text-secondary)] text-[10px] uppercase font-bold mt-0.5">Label Configuration</p>
                            </div>
                            <button @click="showModal = false" class="w-9 h-9 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center border border-[var(--border-color)]">
                                <X class="w-4 h-4 text-[var(--text-tertiary)]" />
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="p-6 space-y-5">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] mb-2">Category Name</label>
                                <div class="relative flex items-center">
                                    <Tag class="absolute !left-3 w-4 h-4 text-[var(--text-tertiary)] pointer-events-none" />
                                    <input v-model="form.name" type="text" placeholder="e.g. Computing" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl !pl-10 py-2.5 text-xs focus:border-[#6366f1] transition-all" required />
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] mb-2">Accent Color</label>
                                <div class="flex items-center gap-3">
                                    <input v-model="form.color_hex" type="color" class="w-10 h-10 rounded-lg bg-[var(--bg-base)] border border-[var(--border-color)] p-0.5 cursor-pointer" />
                                    <input v-model="form.color_hex" type="text" class="flex-1 bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl py-2 px-3 text-[10px] font-mono uppercase focus:border-[#6366f1]" />
                                </div>
                            </div>

                            <div class="pt-4 flex gap-3">
                                <button type="button" @click="showModal = false" class="flex-1 py-3 animate-none rounded-xl border border-[var(--border-color)] font-bold text-[var(--text-secondary)] text-[10px] uppercase tracking-widest">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="flex-[2] py-3 rounded-xl bg-[#6366f1] text-white font-bold text-[10px] uppercase tracking-widest shadow-lg shadow-[#6366f1]/20 transition-all">
                                    {{ editingCategory ? 'Update' : 'Create' }}
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
                <div class="relative w-full max-w-sm bg-[var(--bg-surface)] rounded-2xl border border-[var(--border-color)] p-8 shadow-2xl overflow-hidden">
                    <div v-if="deleteError" class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 mb-6">
                            <AlertCircle class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-primary)] tracking-tight mb-2">Action Blocked</h3>
                        <p class="text-[var(--text-secondary)] text-[10px] uppercase font-bold tracking-widest mb-8 leading-relaxed">
                            {{ deleteError }}
                        </p>
                        <button @click="showDeleteConfirm = false" class="w-full py-3 rounded-xl bg-amber-500 text-white text-[10px] font-black uppercase tracking-[0.2em] hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20 active:scale-95">
                            Understood
                        </button>
                    </div>

                    <div v-else class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 mb-6">
                            <Trash2 class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-black text-[var(--text-primary)] tracking-tight mb-2">Delete Category?</h3>
                        <p class="text-[var(--text-secondary)] text-[10px] uppercase font-bold tracking-widest mb-8 leading-relaxed">
                            Are you sure you want to delete "{{ deleteTarget?.name }}"? This action cannot be undone.
                        </p>
                        
                        <div class="flex flex-col w-full gap-2">
                            <button @click="confirmDelete" :disabled="isDeleting" class="w-full py-3 rounded-xl bg-red-500 text-white text-[10px] font-black uppercase tracking-[0.2em] hover:bg-red-600 transition-all shadow-lg shadow-red-500/20 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <Loader2 v-if="isDeleting" class="w-3 h-3 animate-spin" />
                                {{ isDeleting ? 'Processing...' : 'Delete Permanently' }}
                            </button>
                            <button @click="showDeleteConfirm = false" :disabled="isDeleting" class="w-full py-3 rounded-xl bg-[var(--bg-base)] text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-[0.2em] hover:bg-[var(--border-color)] transition-all active:scale-95 disabled:opacity-50">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }
</style>
