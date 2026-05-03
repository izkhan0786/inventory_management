<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    Search, Plus, Filter, ShoppingBag, 
    User, Tag, Trash2, X, DollarSign, Edit, Clock, Package, CheckCircle2, AlertCircle,
    FileText, Download, MoreHorizontal, FileCheck
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    orders: Object
});

const showAddModal = ref(false);
const showDeleteModal = ref(false);
const orderToDelete = ref(null);
const searchQuery = ref('');

const form = useForm({
    type: 'Sales Order',
    customer_name: '',
    customer_email: '',
    status: 'Pending',
    total_amount: 0,
    tax_amount: 0,
    discount_amount: 0,
    currency: 'USD',
    notes: ''
});

const isEditing = ref(false);
const editingId = ref(null);

const openAdd = () => {
    isEditing.value = false;
    form.reset();
    showAddModal.value = true;
};

const submitAdd = () => {
    if (isEditing.value) {
        form.put(route('orders.update', editingId.value), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('orders.store'), {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            }
        });
    }
};

const editOrder = (order) => {
    isEditing.value = true;
    editingId.value = order.id;
    form.type = order.type;
    form.customer_name = order.customer_name;
    form.customer_email = order.customer_email;
    form.status = order.status;
    form.total_amount = order.total_amount;
    form.tax_amount = order.tax_amount;
    form.discount_amount = order.discount_amount;
    form.currency = order.currency;
    form.notes = order.notes;
    showAddModal.value = true;
};

const deleteOrder = (id) => {
    orderToDelete.value = id;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    form.delete(route('orders.destroy', orderToDelete.value), {
        onSuccess: () => {
            showDeleteModal.value = false;
            orderToDelete.value = null;
        }
    });
};

const getStatusColor = (status) => {
    const colors = {
        'Pending': 'bg-amber-500/10 text-amber-300 border-amber-400/20',
        'Processing': 'bg-sky-500/10 text-sky-300 border-sky-400/20',
        'Shipped': 'bg-indigo-500/10 text-indigo-300 border-indigo-400/20',
        'Delivered': 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
        'Completed': 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
        'Cancelled': 'bg-rose-500/10 text-rose-300 border-rose-400/20',
        'Draft': 'bg-slate-500/10 text-slate-300 border-slate-400/20'
    };
    return colors[status] || 'bg-slate-500/10 text-slate-300 border-slate-400/20';
};

const getTypeIcon = (type) => {
    if (type === 'Quotation') return FileText;
    if (type === 'Purchase Order') return ShoppingBag;
    return FileCheck;
};

const currency = (val) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);

const filteredOrders = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return props.orders.data || [];
    return (props.orders.data || []).filter(o => 
        (o.order_number?.toLowerCase() || '').includes(q) || 
        (o.customer_name?.toLowerCase() || '').includes(q)
    );
});
</script>

<template>
    <Head title="Orders" />

    <PremiumLayout>
        <div class="flex flex-col gap-6">
            <section class="flex flex-col gap-6 rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,_rgba(8,15,30,0.95),_rgba(17,24,39,0.9))] p-8 shadow-[0_30px_80px_rgba(0,0,0,0.1)] lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-white md:text-4xl">Orders History</h1>
                    <p class="mt-2 text-slate-300 text-sm">Track your sales orders and fulfillment status in one place.</p>
                </div>
                <button @click="openAdd" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-500 transition-all">
                    <Plus class="h-4 w-4" />
                    New Order
                </button>
            </section>

            <section class="rounded-xl border border-[var(--border-color)] bg-[var(--bg-surface)] p-4 shadow-sm">
                <div class="mb-4 relative max-w-md">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--text-tertiary)]" />
                    <input v-model="searchQuery" type="text" placeholder="Search orders..." class="w-full rounded-lg border border-[var(--border-color)] bg-[var(--bg-base)] py-2 pl-10 text-xs text-white" />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-[var(--border-color)] text-[10px] font-black uppercase text-[var(--text-secondary)]">
                                <th class="px-4 py-3">Order #</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-right">Date</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-[var(--bg-base)]/50 transition-colors group/row">
                                <td class="px-4 py-4 text-sm font-black text-slate-900 group-hover/row:text-indigo-600 transition-colors">{{ order.order_number || ('SO-' + order.id) }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <component :is="getTypeIcon(order.type)" class="w-3.5 h-3.5 text-indigo-400" />
                                        <span class="text-[10px] font-bold text-slate-300 uppercase">{{ order.type }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs text-[var(--text-secondary)]">{{ order.customer_name }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span :class="['px-2 py-1 rounded text-[10px] font-bold uppercase border', getStatusColor(order.status)]">{{ order.status }}</span>
                                </td>
                                <td class="px-4 py-4 text-right text-xs font-bold text-emerald-400">{{ currency(order.total_amount) }}</td>
                                <td class="px-4 py-4 text-right text-[10px] text-[var(--text-tertiary)]">{{ new Date(order.created_at).toLocaleDateString() }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3 text-[var(--text-tertiary)]">
                                        <a :href="route('orders.pdf', order.id)" target="_blank" class="p-1.5 hover:bg-white/5 rounded-lg transition-colors" title="Download PDF">
                                            <Download class="w-4 h-4 hover:text-indigo-400" />
                                        </a>
                                        <Edit @click="editOrder(order)" class="w-4 h-4 hover:text-indigo-600 cursor-pointer transition-colors" />
                                        <Trash2 @click="deleteOrder(order.id)" class="w-4 h-4 hover:text-rose-500 cursor-pointer transition-colors" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Add Order Modal -->
        <Transition name="drawer">
            <div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showAddModal = false"></div>
                <div class="relative w-full max-w-lg bg-[var(--bg-surface)] rounded-2xl border border-[var(--border-color)] p-6 shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-black text-white">{{ isEditing ? 'Edit Order' : 'Create New Order' }}</h2>
                        <X @click="showAddModal = false" class="w-5 h-5 cursor-pointer text-slate-400" />
                    </div>
                    <form @submit.prevent="submitAdd" class="space-y-4">
                        <div v-if="!isEditing" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Document Type</label>
                                    <select v-model="form.type" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-900 font-black outline-none appearance-none">
                                        <option value="Sales Order" class="text-slate-900">Sales Order</option>
                                        <option value="Quotation" class="text-slate-900">Quotation</option>
                                        <option value="Purchase Order" class="text-slate-900">Purchase Order</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Status</label>
                                    <select v-model="form.status" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-900 font-black outline-none appearance-none">
                                        <option value="Draft" class="text-slate-900">Draft</option>
                                        <option value="Pending" class="text-slate-900">Pending</option>
                                        <option value="Processing" class="text-slate-900">Processing</option>
                                        <option value="Completed" class="text-slate-900">Completed</option>
                                        <option value="Cancelled" class="text-slate-900">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Customer Name</label>
                                    <input v-model="form.customer_name" required type="text" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2 px-3 text-xs text-slate-900 font-bold outline-none" />
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Customer Email</label>
                                    <input v-model="form.customer_email" type="email" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2 px-3 text-xs text-slate-900 font-bold outline-none" />
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Subtotal</label>
                                    <input v-model="form.total_amount" required type="number" step="0.01" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-900 font-black outline-none" />
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Tax</label>
                                    <input v-model="form.tax_amount" type="number" step="0.01" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-900 font-black outline-none" />
                                </div>
                                <div>
                                    <label class="text-[10px] font-black uppercase text-slate-500">Discount</label>
                                    <input v-model="form.discount_amount" type="number" step="0.01" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-900 font-black outline-none" />
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase text-slate-500">Notes</label>
                                <textarea v-model="form.notes" rows="2" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-xl py-2 px-3 text-xs text-slate-900 font-bold outline-none"></textarea>
                            </div>
                        </div>

                        <!-- EDIT MODE: ONLY STATUS -->
                        <div v-else class="py-10">
                            <label class="text-[10px] font-black uppercase text-slate-500 mb-4 block text-center">Update Order Lifecycle Status</label>
                            <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto">
                                <button v-for="st in ['Pending', 'Processing', 'Completed', 'Cancelled', 'Delivered']" :key="st" 
                                    type="button"
                                    @click="form.status = st"
                                    :class="['py-3 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all', 
                                        form.status === st ? 'bg-indigo-600 border-indigo-400 text-white shadow-lg' : 'bg-white/5 border-white/10 text-slate-400 hover:bg-white/10']"
                                >
                                    {{ st }}
                                </button>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showAddModal = false" class="flex-1 py-3 text-xs font-bold text-slate-600 bg-[var(--bg-base)] rounded-xl border border-[var(--border-color)] transition-colors hover:bg-white/5">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 py-3 text-xs font-black uppercase tracking-widest text-white bg-indigo-600 rounded-xl hover:bg-indigo-500 transition-all disabled:opacity-50">
                                {{ isEditing ? 'Update Status' : 'Generate Order' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
        <!-- DELETE CONFIRMATION MODAL -->
        <Transition name="modal">
            <div v-if="showDeleteModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="showDeleteModal = false"></div>
                <div class="relative w-full max-w-sm bg-[#0f172a] rounded-3xl border border-white/10 p-8 shadow-2xl">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-500 mb-6 border border-rose-500/20">
                            <Trash2 class="w-8 h-8" />
                        </div>
                        <h3 class="text-xl font-black text-white mb-2">Delete Order?</h3>
                        <p class="text-slate-400 text-xs font-medium leading-relaxed mb-8">
                            This will permanently remove the order record from history. This action cannot be undone.
                        </p>
                        
                        <div class="flex w-full gap-3">
                            <button @click="executeDelete" :disabled="form.processing" class="flex-1 py-3 rounded-xl bg-rose-500 text-white text-xs font-black uppercase tracking-widest hover:bg-rose-600 transition-all disabled:opacity-50">
                                Delete
                            </button>
                            <button @click="showDeleteModal = false" class="flex-1 py-3 rounded-xl bg-white/5 text-slate-300 text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">
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
.modal-enter-active, .modal-leave-active { transition: all 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
.drawer-enter-active, .drawer-leave-active { transition: opacity 0.3s ease; }
.drawer-enter-from, .drawer-leave-to { opacity: 0; }
</style>
