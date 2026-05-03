<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Package, Plus, Trash2, Layers, DollarSign, Barcode, ChevronLeft, Save } from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    categories: Array
});

const form = useForm({
    name: props.product.name,
    sku: props.product.sku,
    category_id: props.product.category_id || '',
    brand: props.product.brand || '',
    description: props.product.description || '',
    has_variants: !!props.product.has_variants,
    variants: props.product.variants && props.product.variants.length > 0 
        ? props.product.variants.map(v => ({
            id: v.id,
            sku: v.sku,
            name: v.name,
            cost_price: v.cost_price,
            selling_price: v.selling_price,
            stock_qty: v.stock_qty,
            min_stock_level: v.min_stock_level,
            barcode: v.barcode || ''
          }))
        : [{ sku: '', name: 'Default', cost_price: 0, selling_price: 0, stock_qty: 0, min_stock_level: 5, barcode: '' }]
});

const addVariant = () => {
    form.variants.push({
        sku: '',
        name: '',
        cost_price: 0,
        selling_price: 0,
        stock_qty: 0,
        min_stock_level: 5,
        barcode: ''
    });
};

const removeVariant = (index) => {
    if (form.variants.length > 1) {
        form.variants.splice(index, 1);
    }
};

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head :title="`Edit ${product.name} | Nexus Stock`" />
    <PremiumLayout>
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center gap-4">
                    <Link :href="route('products.index')" class="p-2 rounded-xl bg-[var(--bg-surface)] border border-[var(--border-color)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-all">
                        <ChevronLeft class="w-6 h-6" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-[var(--text-primary)] flex items-center gap-3">
                            <Package class="w-8 h-8 text-sky-400" />
                            Edit Product
                        </h1>
                        <p class="text-[var(--text-secondary)] mt-1">Update specifications for <span class="text-sky-400 font-mono">{{ product.sku }}</span></p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <Link :href="route('products.index')" class="px-6 py-2 rounded-xl border border-[var(--border-color)] hover:bg-[var(--bg-surface-hover)] text-[var(--text-primary)] transition-all flex items-center">Cancel</Link>
                    <button @click="submit" :disabled="form.processing" class="px-6 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white font-bold transition-all shadow-lg shadow-sky-500/20 disabled:opacity-50 flex items-center gap-2">
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <Save v-else class="w-4 h-4" />
                        Save Changes
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Primary Details -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-[var(--bg-surface)] backdrop-blur-xl border border-[var(--border-color)] rounded-3xl p-6 shadow-xl transition-colors duration-300">
                        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-6 flex items-center gap-2">
                            <Layers class="w-5 h-5 text-sky-400" />
                            General Information
                        </h2>
                        
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Product Name</label>
                                <input v-model="form.name" type="text" :class="{'border-rose-500': form.errors.name}" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl px-4 py-3 focus:outline-none focus:border-sky-500 transition-all font-bold" placeholder="e.g. iPhone 15 Pro Max">
                                <p v-if="form.errors.name" class="mt-1 text-xs text-rose-500 font-medium">{{ form.errors.name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Primary SKU</label>
                                <input v-model="form.sku" type="text" :class="{'border-rose-500': form.errors.sku}" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl px-4 py-3 focus:outline-none focus:border-sky-500 transition-all font-mono" placeholder="PROD-001">
                                <p v-if="form.errors.sku" class="mt-1 text-xs text-rose-500 font-medium">{{ form.errors.sku }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Category</label>
                                <select v-model="form.category_id" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl px-4 py-3 focus:outline-none focus:border-sky-500 transition-all appearance-none">
                                    <option value="">Select Category</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Brand</label>
                                <input v-model="form.brand" type="text" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl px-4 py-3 focus:outline-none focus:border-sky-500 transition-all" placeholder="e.g. Apple">
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Description</label>
                                <textarea v-model="form.description" rows="4" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] text-[var(--text-primary)] rounded-xl px-4 py-3 focus:outline-none focus:border-sky-500 transition-all" placeholder="Enter product details..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Variants Section -->
                    <div class="bg-[var(--bg-surface)] backdrop-blur-xl border border-[var(--border-color)] rounded-3xl p-6 shadow-xl transition-colors duration-300">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-[var(--text-primary)] flex items-center gap-2">
                                <Plus class="w-5 h-5 text-sky-400" />
                                Product Variants
                            </h2>
                            <button @click="addVariant" class="text-sm text-sky-400 hover:text-sky-300 font-medium flex items-center gap-1">
                                <Plus class="w-4 h-4" /> Add Variant
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(variant, index) in form.variants" :key="index" class="p-4 bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl group hover:border-[var(--primary)] transition-all">
                                <div class="grid grid-cols-4 gap-4 mb-4">
                                    <div class="col-span-2">
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold">Variant Name (e.g. Red / 128GB)</label>
                                        <input v-model="variant.name" type="text" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold">SKU</label>
                                        <input v-model="variant.sku" type="text" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                    <div class="flex justify-end text-rose-500">
                                        <button v-if="form.variants.length > 1" @click="removeVariant(index)" class="p-2 hover:bg-rose-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-4 gap-4">
                                    <div>
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold flex items-center gap-1"><DollarSign class="w-3 h-3"/> Cost</label>
                                        <input v-model="variant.cost_price" type="number" step="0.01" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold flex items-center gap-1"><DollarSign class="w-3 h-3"/> Selling</label>
                                        <input v-model="variant.selling_price" type="number" step="0.01" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold">In Stock</label>
                                        <input v-model="variant.stock_qty" type="number" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase tracking-wider text-[var(--text-tertiary)] font-bold flex items-center gap-1"><Barcode class="w-3 h-3"/> Barcode</label>
                                        <input v-model="variant.barcode" type="text" class="w-full bg-transparent border-b border-[var(--border-color)] py-1 focus:outline-none focus:border-sky-500 text-[var(--text-primary)]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings & Media -->
                <div class="space-y-6">
                    <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-3xl p-6 shadow-xl transition-colors duration-300">
                        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-6">Product Image</h2>
                        <div class="aspect-square rounded-2xl border-2 border-dashed border-[var(--border-color)] flex flex-col items-center justify-center cursor-pointer hover:border-sky-500/50 transition-all bg-[var(--bg-base)]">
                            <Plus class="w-8 h-8 text-[var(--text-tertiary)] mb-2" />
                            <p class="text-sm text-[var(--text-tertiary)]">Change Image</p>
                        </div>
                    </div>

                    <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-3xl p-6 shadow-xl transition-colors duration-300">
                        <h2 class="text-lg font-semibold text-[var(--text-primary)] mb-4">Inventory Settings</h2>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative">
                                    <input v-model="form.has_variants" type="checkbox" class="sr-only peer">
                                    <div class="w-10 h-6 bg-[var(--bg-base)] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
                                </div>
                                <span class="text-sm text-[var(--text-secondary)] group-hover:text-[var(--text-primary)] transition-colors">Has Multiple Variants</span>
                            </label>
                            
                            <p class="text-xs text-[var(--text-tertiary)]">Enable this if the product comes in different sizes, colors, or versions.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PremiumLayout>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
