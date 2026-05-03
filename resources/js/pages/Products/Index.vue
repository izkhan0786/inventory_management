<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { 
    Search, Plus, Filter, MoreVertical, ShoppingBag, 
    Trash2, X, Tag, Hash, FileUp, FileDown, 
    MoreHorizontal, ChevronRight, AlertCircle, 
    Calendar, Box, CheckCircle2, Package, Edit2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Object,
    categories: Array
});

const showAddModal = ref(false);
const showDetailModal = ref(false);
const selectedProduct = ref(null);
const showDeleteConfirm = ref(false);
const deleteId = ref(null);
const searchQuery = ref('');

const form = useForm({
    name: '',
    sku: '',
    category_id: '',
    brand: '',
    unit: 'pcs',
    description: '',
    has_variants: false,
    variants: [
        {
            sku: '',
            name: 'Default',
            cost_price: 0,
            selling_price: 0,
            stock_qty: 0,
            min_stock_level: 5,
            barcode: '',
            expiry_date: ''
        }
    ]
});

const currency = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

const filteredProducts = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.products.data;
    return props.products.data.filter(p => 
        p.name.toLowerCase().includes(q) || 
        p.sku.toLowerCase().includes(q)
    );
});

const isEditing = ref(false);
const editingId = ref(null);

const openAdd = () => {
    isEditing.value = false;
    form.reset();
    showAddModal.value = true;
};

const openDetail = (product) => {
    selectedProduct.value = product;
    showDetailModal.value = true;
};

const editProduct = (product) => {
    isEditing.value = true;
    editingId.value = product.id;
    form.name = product.name;
    form.sku = product.sku;
    form.category_id = product.category_id;
    form.description = product.description;
    form.brand = product.brand;
    form.unit = product.unit;
    
    // Map variant data including ID to allow updates instead of re-creation
    if (product.variants && product.variants.length > 0) {
        const v = product.variants[0];
        form.variants = [{
            id: v.id,
            sku: v.sku,
            name: v.name || 'Default',
            cost_price: v.cost_price,
            selling_price: v.selling_price,
            stock_qty: v.stock_qty,
            min_stock_level: v.min_stock_level,
            expiry_date: v.expiry_date ? v.expiry_date.split('T')[0] : ''
        }];
    }
    
    showAddModal.value = true;
};

const submitAdd = () => {
    if (isEditing.value) {
        form.put(route('products.update', editingId.value), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteProduct = (id) => {
    deleteId.value = id;
    showDeleteConfirm.value = true;
};

const confirmDelete = () => {
    if (!deleteId.value) return;
    
    router.delete(route('products.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteId.value = null;
        }
    });
};

const importFile = ref(null);
const triggerImport = () => importFile.value.click();
const handleImport = (e) => {
    const file = e.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('file', file);
        
        router.post(route('products.import'), formData, {
            forceFormData: true,
            onSuccess: () => {
                alert('Import completed successfully!');
                e.target.value = ''; // Reset input
            },
            onError: (err) => {
                alert('Import failed: ' + (err.file || 'Unknown error'));
            }
        });
    }
};
const exportProducts = () => {
    const data = props.products.data.map(p => ({
        Name: p.name,
        SKU: p.sku,
        Category: p.category?.name || 'Uncategorized',
        Stock: p.variants_sum_stock_qty || 0,
        Value: p.valuation || 0
    }));
    
    const csvContent = "data:text/csv;charset=utf-8," 
        + ["Name,SKU,Category,Stock,Value", ...data.map(r => [
            `"${r.Name}"`,
            `"${r.SKU}"`,
            `"${r.Category}"`,
            r.Stock,
            r.Value
        ].join(","))].join("\n");
        
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `stockly_catalog_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head title="Catalog" />

    <PremiumLayout>
        <div class="flex flex-col gap-6">
            <!-- Top Toolbar -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-[var(--text-primary)]">Products</h1>
                    <p class="text-[var(--text-tertiary)] text-xs font-bold uppercase mt-1">{{ props.products.total }} products</p>
                </div>
                <div class="flex items-center gap-3">
                    <input type="file" ref="importFile" class="hidden" @change="handleImport" />
                    <button @click="triggerImport" class="px-4 py-2 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-xl text-xs font-bold text-[var(--text-secondary)] hover:text-white transition-all flex items-center gap-2 uppercase tracking-widest active:scale-95">
                        <FileUp class="w-4 h-4" /> Import
                    </button>
                    <button @click="exportProducts" class="px-4 py-2 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-xl text-xs font-bold text-[var(--text-secondary)] hover:text-white transition-all flex items-center gap-2 uppercase tracking-widest active:scale-95">
                        <FileDown class="w-4 h-4" /> Export
                    </button>
                    <button @click="openAdd" class="px-6 py-2.5 bg-[#10b981] text-[#0f172a] rounded-xl text-xs font-black uppercase tracking-widest hover:bg-[#059669] transition-all flex items-center gap-2 shadow-lg shadow-[#10b981]/20">
                        <Plus class="w-4 h-4" /> Add Product
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2.5rem] overflow-hidden shadow-2xl">
                <!-- Filters Row -->
                <div class="p-4 border-b border-[var(--border-color)] flex items-center gap-4">
                    <div class="relative flex-1">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                        <input v-model="searchQuery" type="text" placeholder="Search products..." class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] py-2.5 pl-11 pr-4 rounded-xl text-xs text-[var(--text-primary)] outline-none focus:border-[#10b981]/40" />
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <Filter class="absolute left-3.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[var(--text-tertiary)]" />
                            <select class="bg-[var(--bg-base)] border border-[var(--border-color)] py-2 pl-9 pr-8 rounded-xl text-[10px] font-black uppercase tracking-widest text-[var(--text-secondary)] outline-none focus:border-[#10b981]/40">
                                <option>All Categories</option>
                            </select>
                        </div>
                        <select class="bg-[var(--bg-base)] border border-[var(--border-color)] py-2 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest text-[var(--text-secondary)] outline-none focus:border-[#10b981]/40">
                            <option>All Status</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-[var(--border-color)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)]">
                                <th class="pl-8 pr-4 py-5"><input type="checkbox" class="rounded bg-black border-white/10 w-4 h-4" /></th>
                                <th class="px-4 py-5">Products <span class="ml-1 text-[8px] opacity-40">↑↓</span></th>
                                <th class="px-4 py-5">SKU</th>
                                <th class="px-4 py-5">Category</th>
                                <th class="px-4 py-5">Quantity <span class="ml-1 text-[8px] opacity-40">↑↓</span></th>
                                <th class="px-4 py-5">Status</th>
                                <th class="px-4 py-5">Expiry Date</th>
                                <th class="px-4 py-5 text-right">Total Value <span class="ml-1 text-[8px] opacity-40">↑↓</span></th>
                                <th class="pl-4 pr-8 py-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)] text-[var(--text-secondary)]">
                            <tr v-for="product in filteredProducts" :key="product.id" @click="openDetail(product)" class="group hover:bg-[#10b981]/5 transition-all duration-500 cursor-pointer border-b border-[var(--border-color)]/50 last:border-0">
                                <td class="pl-8 pr-4 py-6" @click.stop><input type="checkbox" class="rounded-lg bg-black/20 border-white/10 w-4 h-4 checked:bg-[#10b981]" /></td>
                                <td class="px-4 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="relative w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center p-2 group-hover:scale-105 group-hover:border-[#10b981]/50 transition-all duration-500 shadow-xl overflow-hidden">
                                            <img v-if="product.image_path" :src="product.image_path" class="w-full h-full object-cover rounded-lg" />
                                            <Package v-else class="w-6 h-6 text-[#10b981] opacity-40" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-black text-[var(--text-primary)] group-hover:text-[#10b981] transition-colors line-clamp-1 tracking-tight">{{ product.name }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-black/30 border border-white/5 text-[var(--text-tertiary)] uppercase tracking-tighter">{{ product.unit || 'pcs' }}</span>
                                                <span class="text-[9px] font-medium text-[var(--text-tertiary)] opacity-60 italic">Node #{{ product.id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6 font-mono text-[11px] font-black text-[#10b981] opacity-80 group-hover:opacity-100 transition-opacity">{{ product.sku }}</td>
                                <td class="px-4 py-6">
                                    <div v-if="product.category" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/5 border border-white/10 rounded-xl group-hover:border-[#10b981]/40 transition-all">
                                        <div class="w-2 h-2 rounded-full shadow-[0_0_10px_rgba(16,185,129,0.4)]" :style="{ backgroundColor: product.category.color_hex || '#10b981' }"></div>
                                        <span class="text-[10px] font-black text-[var(--text-primary)] uppercase tracking-widest">{{ product.category.name }}</span>
                                    </div>
                                    <span v-else class="text-[10px] text-[var(--text-tertiary)] opacity-30 font-bold uppercase tracking-widest">Uncategorized</span>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-[var(--text-primary)]">{{ product.variants_sum_stock_qty || 0 }} </span>
                                        <div class="w-12 h-1 bg-white/5 rounded-full mt-1.5 overflow-hidden">
                                            <div class="h-full bg-[#10b981]" :style="{ width: Math.min((product.variants_sum_stock_qty || 0) / 10, 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border transition-all" 
                                        :class="(product.variants_sum_stock_qty || 0) > 0 
                                            ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                                            : 'bg-rose-500/10 text-rose-400 border-rose-500/20'">
                                        <div class="w-1.5 h-1.5 rounded-full" :class="(product.variants_sum_stock_qty || 0) > 0 ? 'bg-emerald-400' : 'bg-rose-400'"></div>
                                        <span class="text-[9px] font-black uppercase tracking-widest">{{ (product.variants_sum_stock_qty || 0) > 0 ? 'In Stock' : 'Out of Stock' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <span class="text-[10px] font-bold text-[var(--text-tertiary)] uppercase tracking-tight flex items-center gap-1.5">
                                        <Calendar class="w-3 h-3 opacity-40" />
                                        {{ product.expiry_date || 'No Expiry' }}
                                    </span>
                                </td>
                                <td class="px-4 py-6 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-mono text-sm font-black text-white group-hover:text-[#10b981] transition-colors">{{ currency(product.valuation || 0) }}</span>
                                        <span class="text-[8px] font-bold text-[var(--text-tertiary)] uppercase mt-0.5 tracking-tighter">Total Valuation</span>
                                    </div>
                                </td>
                                <td class="pl-4 pr-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                                        <button @click.stop="editProduct(product)" class="p-2.5 bg-white/5 border border-white/10 rounded-xl transition-all text-[var(--text-tertiary)] hover:text-[#10b981] hover:bg-[#10b981]/10 hover:border-[#10b981]/30">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click.stop="deleteProduct(product.id)" class="p-2.5 bg-white/5 border border-white/10 rounded-xl transition-all text-[var(--text-tertiary)] hover:text-rose-500 hover:bg-rose-500/10 hover:border-rose-500/30">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- NEW PRODUCT DRAWER - THEME SYNCED -->
        <Transition name="drawer">
            <div v-if="showAddModal" class="fixed inset-0 z-[100] overflow-hidden">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="showAddModal = false"></div>
                <div class="absolute inset-y-0 right-0 max-w-2xl w-full flex">
                    <div class="relative w-full bg-[var(--bg-surface)] shadow-2xl flex flex-col border-l border-[var(--border-color)]">
                        <!-- Drawer Header -->
                        <div class="p-8 border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--primary)]/5 px-10">
                            <div>
                                <h2 class="text-3xl font-black text-[var(--text-primary)] tracking-tighter">{{ isEditing ? 'Edit Product' : 'New Product' }}</h2>
                                <p class="text-[var(--text-tertiary)] text-xs mt-1 font-bold">{{ isEditing ? 'Update catalog specifications.' : 'Define catalog specifications.' }}</p>
                            </div>
                            <button @click="showAddModal = false" class="w-12 h-12 rounded-2xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-primary)] hover:bg-[var(--border-color)] transition-all">
                                <X class="w-6 h-6" />
                            </button>
                        </div>
                        
                        <!-- Body -->
                        <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
                             <form @submit.prevent="submitAdd" class="space-y-12 pb-40">
                                <!-- Section 1: General -->
                                <section class="space-y-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg bg-[#10b981]/10 flex items-center justify-center text-[#10b981]">
                                            <Package class="w-4 h-4" />
                                        </div>
                                        <p class="text-[10px] font-black uppercase text-[var(--text-primary)] tracking-[0.2em]">General Information</p>
                                    </div>
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-4 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Product Name</label>
                                            <input v-model="form.name" required type="text" placeholder="e.g. Fresh Orange Juice" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all" />
                                            <p v-if="form.errors.name" class="text-[9px] text-rose-500 font-bold">{{ form.errors.name }}</p>
                                        </div>
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Universal SKU</label>
                                            <input v-model="form.sku" required type="text" placeholder="OJ-US-001" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all font-mono" :class="{ 'border-rose-500/50': form.errors.sku }" />
                                            <p v-if="form.errors.sku" class="text-[9px] text-rose-500 font-bold ml-1">{{ form.errors.sku }}</p>
                                        </div>
                                        <div class="col-span-6 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Description</label>
                                            <textarea v-model="form.description" rows="3" placeholder="Describe clinical or retail specifications..." class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all resize-none"></textarea>
                                        </div>
                                    </div>
                                </section>

                                <!-- Section 2: Organization -->
                                <section class="space-y-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg bg-[#10b981]/10 flex items-center justify-center text-[#10b981]">
                                            <Tag class="w-4 h-4" />
                                        </div>
                                        <p class="text-[10px] font-black uppercase text-[var(--text-primary)] tracking-[0.2em]">Categorization & Units</p>
                                    </div>
                                    <div class="grid grid-cols-6 gap-6">
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Category</label>
                                            <select v-model="form.category_id" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all appearance-none" :class="{ 'border-rose-500/50': form.errors.category_id }">
                                                <option :value="null">Select Category</option>
                                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                            </select>
                                            <p v-if="form.errors.category_id" class="text-[9px] text-rose-500 font-bold ml-1">{{ form.errors.category_id }}</p>
                                        </div>
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Measurement Unit</label>
                                            <select v-model="form.unit" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all">
                                                <option value="pcs">Pieces (pcs)</option>
                                                <option value="kg">Kilograms (kg)</option>
                                                <option value="g">Grams (g)</option>
                                                <option value="liters">Liters (l)</option>
                                                <option value="boxes">Boxes</option>
                                                <option value="cartons">Cartons</option>
                                                <option value="pallets">Pallets</option>
                                                <option value="pairs">Pairs</option>
                                            </select>
                                        </div>
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Brand / Manufacture</label>
                                            <input v-model="form.brand" type="text" placeholder="e.g. Stockly Labs" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all" />
                                        </div>
                                    </div>
                                </section>

                                <!-- Section 3: Pricing & Inventory -->
                                <section class="space-y-6">
                                    <div class="flex items-center gap-3 mb-6">
                                        <div class="w-8 h-8 rounded-lg bg-[#10b981]/10 flex items-center justify-center text-[#10b981]">
                                            <Hash class="w-4 h-4" />
                                        </div>
                                        <p class="text-[10px] font-black uppercase text-[var(--text-primary)] tracking-[0.2em]">Pricing & Stock Control</p>
                                    </div>
                                    <div class="grid grid-cols-6 gap-6 bg-[var(--bg-base)]/50 p-6 rounded-[2rem] border border-[var(--border-color)]">
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-emerald-500 ml-1">Cost Price ($)</label>
                                            <input v-model="form.variants[0].cost_price" type="number" step="0.01" class="w-full bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all" />
                                        </div>
                                        <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-emerald-500 ml-1">Selling Price ($)</label>
                                            <input v-model="form.variants[0].selling_price" type="number" step="0.01" class="w-full bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all" :class="{ 'border-rose-500/50': form.errors['variants.0.selling_price'] }" />
                                            <p v-if="form.errors['variants.0.selling_price']" class="text-[9px] text-rose-500 font-bold ml-1">{{ form.errors['variants.0.selling_price'] }}</p>
                                        </div>
                                         <div class="col-span-2 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-[var(--text-tertiary)] ml-1">Initial Stock</label>
                                            <input v-model="form.variants[0].stock_qty" type="number" class="w-full bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl py-3.5 px-5 text-sm text-[var(--text-primary)] focus:border-[#10b981]/50 outline-none transition-all" />
                                        </div>
                                        <div class="col-span-3 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-rose-500 ml-1">Low Stock Warning Level</label>
                                            <input v-model="form.variants[0].min_stock_level" type="number" class="w-full bg-rose-500/5 border border-rose-500/20 rounded-2xl py-3.5 px-5 text-sm text-rose-600 dark:text-rose-100 focus:border-rose-500/50 outline-none transition-all" />
                                        </div>
                                        <div class="col-span-3 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-amber-500 ml-1">Expiry Date Tracking</label>
                                            <div class="relative">
                                                <input v-model="form.variants[0].expiry_date" type="date" class="w-full bg-amber-500/5 border border-amber-500/20 rounded-2xl py-3.5 px-5 text-sm text-amber-600 dark:text-amber-100 focus:border-amber-500/50 outline-none transition-all appearance-none" />
                                                <Calendar class="absolute right-5 top-1/2 -translate-y-1/2 w-4 h-4 text-amber-500/50 pointer-events-none" />
                                            </div>
                                        </div>
                                        <div class="col-span-6 flex flex-col gap-2">
                                            <label class="text-[10px] font-black uppercase text-sky-500 ml-1">Product Barcode (for Scanner)</label>
                                            <input v-model="form.variants[0].barcode" type="text" placeholder="Scan or type barcode here..." class="w-full bg-sky-500/5 border border-sky-500/20 rounded-2xl py-3.5 px-5 text-sm text-sky-600 dark:text-sky-100 focus:border-sky-500/50 outline-none transition-all font-mono" />
                                        </div>
                                    </div>
                                </section>

                                <!-- Footer -->
                                <div class="fixed bottom-0 right-0 w-full max-w-2xl p-10 bg-gradient-to-t from-[var(--bg-surface)] to-transparent pointer-events-none z-50">
                                    <div class="pointer-events-auto flex gap-4">
                                        <button type="button" @click="showAddModal = false" class="flex-1 py-4 rounded-2xl bg-[var(--bg-base)] border border-[var(--border-color)] text-xs font-black uppercase tracking-widest text-[var(--text-tertiary)] hover:bg-[var(--border-color)] transition-all">Cancel</button>
                                        <button type="submit" class="flex-[2] py-4 rounded-2xl bg-[#10b981] text-[#0f172a] text-xs font-black uppercase tracking-widest shadow-2xl shadow-[#10b981]/20 hover:bg-[#059669] transition-all">
                                            {{ isEditing ? 'Update Product Details' : 'Create Product Catalog Entry' }}
                                        </button>
                                    </div>
                                </div>
                             </form>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
        <!-- PRODUCT DETAIL MODAL -->
        <Transition name="drawer">
            <div v-if="showDetailModal" class="fixed inset-0 z-[120] overflow-hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" @click="showDetailModal = false"></div>
                <div class="absolute inset-y-0 right-0 max-w-2xl w-full flex">
                    <div class="relative w-full bg-[#0f172a] shadow-2xl flex flex-col border-l border-white/10">
                        <div class="p-8 border-b border-white/5 flex justify-between items-center bg-white/5">
                            <div>
                                <h2 class="text-3xl font-black text-white tracking-tighter">{{ selectedProduct?.name }}</h2>
                                <p class="text-[var(--text-tertiary)] text-[10px] font-black uppercase tracking-widest mt-1">Detailed Technical Specifications</p>
                            </div>
                            <button @click="showDetailModal = false" class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-white hover:bg-white/10 transition-all">
                                <X class="w-6 h-6" />
                            </button>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-10 space-y-10 custom-scrollbar">
                            <!-- Visual Assets Section -->
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <p class="text-[10px] font-black uppercase text-[#10b981] tracking-[0.2em]">QR Code Identity</p>
                                    <div class="aspect-square rounded-3xl bg-white p-4 shadow-2xl">
                                        <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + (selectedProduct?.variants[0]?.barcode || selectedProduct?.sku)" class="w-full h-full object-contain" alt="QR Identity" />
                                    </div>
                                </div>
                                <div class="space-y-4 flex flex-col justify-end">
                                    <p class="text-[10px] font-black uppercase text-[#10b981] tracking-[0.2em]">Visual Barcode</p>
                                    <div class="bg-white rounded-3xl p-6 flex items-center justify-center h-full shadow-inner">
                                        <img v-if="selectedProduct?.variants[0]?.barcode" :src="'https://barcodeapi.org/api/128/' + selectedProduct.variants[0].barcode" class="w-full object-contain h-20" />
                                        <p v-else class="text-[10px] text-black/20 font-bold italic">No Barcode Set</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Specs Grid -->
                            <div class="grid grid-cols-2 gap-6">
                                <div class="p-6 rounded-3xl bg-white/5 border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-1">SKU Identification</p>
                                    <p class="text-xl font-mono font-black text-[#10b981] tracking-tighter">{{ selectedProduct?.sku }}</p>
                                </div>
                                <div class="p-6 rounded-3xl bg-white/5 border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-1">Current Inventory</p>
                                    <p class="text-xl font-black text-white tracking-tighter">{{ selectedProduct?.variants_sum_stock_qty || 0 }} <span class="text-xs text-white/40 uppercase">{{ selectedProduct?.unit }}</span></p>
                                </div>
                                <div class="p-6 rounded-3xl bg-white/5 border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-1">Category Group</p>
                                    <p class="text-lg font-black text-white tracking-tighter">{{ selectedProduct?.category?.name || 'Uncategorized' }}</p>
                                </div>
                                <div class="p-6 rounded-3xl bg-white/5 border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-white/40 tracking-widest mb-1">Selling Price</p>
                                    <p class="text-xl font-black text-emerald-400 tracking-tighter">{{ currency(selectedProduct?.variants[0]?.selling_price || 0) }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="space-y-4">
                                <p class="text-[10px] font-black uppercase text-[#10b981] tracking-[0.2em]">Product Bio / Notes</p>
                                <div class="p-8 rounded-3xl bg-white/5 border border-white/10">
                                    <p class="text-sm leading-8 text-white/70 font-medium">{{ selectedProduct?.description || 'No detailed description available for this catalog item.' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 border-t border-white/5 bg-black/20">
                             <div class="flex gap-4">
                                <button @click="showDetailModal = false" class="flex-1 py-4 rounded-2xl bg-white/5 border border-white/10 text-xs font-black uppercase tracking-widest text-white hover:bg-white/10 transition-all">Close Details</button>
                                <button @click="editProduct(selectedProduct); showDetailModal = false" class="flex-1 py-4 rounded-2xl bg-[#10b981] text-[#0f172a] text-xs font-black uppercase tracking-widest hover:bg-[#059669] transition-all">Edit Catalog Entry</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="drawer">
            <div v-if="showDeleteConfirm" class="fixed inset-0 z-[110] flex items-center justify-center p-4 overflow-hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" @click="showDeleteConfirm = false"></div>
                <div class="relative w-full max-w-md bg-[var(--bg-surface)] rounded-[2.5rem] border border-[var(--border-color)] p-10 shadow-2xl overflow-hidden">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 blur-3xl -z-10 rounded-full"></div>
                    
                    <div class="flex flex-col items-center text-center">
                        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 flex items-center justify-center text-rose-500 mb-8 animate-pulse">
                            <AlertCircle class="w-10 h-10" />
                        </div>
                        <h3 class="text-2xl font-black text-[var(--text-primary)] tracking-tighter mb-4">Final Confirmation</h3>
                        <p class="text-[var(--text-tertiary)] text-sm font-bold leading-relaxed mb-10">
                            Are you absolutely sure you want to delete this product? This action is permanent and will remove all associated variants and records.
                        </p>
                        
                        <div class="flex flex-col w-full gap-3">
                            <button @click="confirmDelete" class="w-full py-4 rounded-2xl bg-rose-500 text-white text-xs font-black uppercase tracking-[0.2em] hover:bg-rose-600 transition-all shadow-xl shadow-rose-500/20 active:scale-95">
                                Delete Permanently
                            </button>
                            <button @click="showDeleteConfirm = false" class="w-full py-4 rounded-2xl bg-[var(--bg-base)] text-[var(--text-tertiary)] text-xs font-black uppercase tracking-[0.2em] hover:bg-[var(--border-color)] transition-all active:scale-95">
                                Keep Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 20px; }

.drawer-enter-active, .drawer-leave-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
.drawer-enter-from .relative, .drawer-leave-to .relative { transform: translateX(100%); }
</style>
