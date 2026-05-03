<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    ArrowUpRight, ArrowDownLeft, Plus, X, 
    Calendar, DollarSign, Tag, FileText, Loader2,
    List, LayoutDashboard
} from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    transactions: Object
});

const showModal = ref(false);
const isCompact = ref(localStorage.getItem('transactions_compact') === 'true');

const toggleCompact = () => {
    isCompact.value = !isCompact.value;
    localStorage.setItem('transactions_compact', isCompact.value.toString());
};

const form = useForm({
    type: 'INCOME',
    amount: '',
    category: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0]
});

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Financial Transactions" />
    <PremiumLayout>
        <div class="page-header mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[var(--text-primary)] tracking-tight">Transactions</h1>
                <p class="text-[var(--text-secondary)] mt-1">Financial oversight of income and expenses.</p>
            </div>
            <div class="flex items-center gap-3">
                <button 
                    @click="toggleCompact"
                    class="p-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--bg-surface)] text-[var(--text-tertiary)] hover:text-[var(--text-primary)] transition-all"
                    :title="isCompact ? 'Standard View' : 'Compact View'"
                >
                    <LayoutDashboard v-if="isCompact" class="w-5 h-5" />
                    <List v-else class="w-5 h-5" />
                </button>
                <button 
                    @click="showModal = true"
                    class="px-5 py-2.5 rounded-xl bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold transition-all shadow-lg shadow-[#6366f1]/20 text-sm flex items-center gap-2 transform active:scale-95"
                >
                    <Plus class="w-4 h-4" /> Add Transaction
                </button>
            </div>
        </div>

        <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl p-6 transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="premium-table" :class="{ 'is-compact': isCompact }">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr v-for="t in transactions.data" :key="t.id" class="transition-colors hover:bg-[var(--bg-base)] group" :class="{ 'compact-row': isCompact }">
                            <td><span class="font-mono text-[#6366f1] text-sm">{{ t.transaction_no }}</span></td>
                            <td class="text-[var(--text-primary)] font-medium">{{ t.description || 'No description' }}</td>
                            <td>
                                <div class="flex items-center gap-2" :class="t.type === 'INCOME' ? 'text-emerald-400' : 'text-rose-400'">
                                    <div class="rounded-full flex items-center justify-center bg-current/10" :class="isCompact ? 'w-5 h-5' : 'w-6 h-6'">
                                        <ArrowUpRight v-if="t.type === 'INCOME'" class="w-3 h-3" />
                                        <ArrowDownLeft v-else class="w-3 h-3" />
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider">{{ t.type }}</span>
                                </div>
                            </td>
                             <td class="font-bold" :class="[t.type === 'INCOME' ? 'text-emerald-400' : 'text-rose-400', isCompact ? 'text-base' : 'text-lg']">
                                {{ t.type === 'INCOME' ? '+' : '-' }}${{ parseFloat(t.amount).toLocaleString() }}
                             </td>
                            <td class="text-sm text-[var(--text-tertiary)]">{{ t.transaction_date }}</td>
                        </tr>

                         <tr v-if="transactions.data.length === 0">
                            <td colspan="5" class="text-center py-20 text-[var(--text-tertiary)]">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-[var(--bg-base)] flex items-center justify-center">
                                        <FileText class="w-8 h-8 opacity-20" />
                                    </div>
                                    <p class="font-medium">No transactions recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[#0f1115]/80 backdrop-blur-sm" @click="showModal = false"></div>
                
                <div class="relative w-full max-w-lg bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2rem] shadow-2xl overflow-hidden anim-pop-in">
                    <div class="border-b border-[var(--border-color)] flex justify-between items-center bg-[var(--bg-surface-hover)]/30" :class="isCompact ? 'p-5' : 'p-8'">
                        <div>
                            <h2 class="font-bold text-[var(--text-primary)]" :class="isCompact ? 'text-lg' : 'text-2xl'">New Transaction</h2>
                            <p class="text-[var(--text-secondary)] text-sm mt-0.5" v-if="!isCompact">Record a new financial entry.</p>
                        </div>
                        <button @click="showModal = false" class="w-10 h-10 rounded-xl hover:bg-[var(--bg-base)] flex items-center justify-center transition-colors">
                            <X class="w-5 h-5 text-[var(--text-tertiary)]" />
                        </button>
                    </div>

                    <form @submit.prevent="submit" :class="isCompact ? 'p-6' : 'p-8'">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4 mb-6" :class="{ '!gap-y-3': isCompact }">
                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-1.5" :class="{ 'text-xs': isCompact }">Transaction Type</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        type="button" 
                                        @click="form.type = 'INCOME'"
                                        class="flex items-center justify-center gap-2 rounded-xl border-2 transition-all font-bold text-sm"
                                        :class="[
                                            form.type === 'INCOME' ? 'bg-emerald-500/10 border-emerald-500 text-emerald-500' : 'bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-tertiary)] hover:border-[var(--text-tertiary)]',
                                            isCompact ? 'py-2' : 'py-3'
                                        ]"
                                    >
                                        <ArrowUpRight class="w-4 h-4" /> INCOME
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="form.type = 'EXPENSE'"
                                        class="flex items-center justify-center gap-2 rounded-xl border-2 transition-all font-bold text-sm"
                                        :class="[
                                            form.type === 'EXPENSE' ? 'bg-rose-500/10 border-rose-500 text-rose-500' : 'bg-[var(--bg-base)] border-[var(--border-color)] text-[var(--text-tertiary)] hover:border-[var(--text-tertiary)]',
                                            isCompact ? 'py-2' : 'py-3'
                                        ]"
                                    >
                                        <ArrowDownLeft class="w-4 h-4" /> EXPENSE
                                    </button>
                                </div>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-1.5" :class="{ 'text-xs': isCompact }">Amount ($)</label>
                                <div class="relative">
                                    <DollarSign class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        v-model="form.amount" 
                                        class="form-input pl-11 !rounded-xl" 
                                        :class="{ 'py-2 text-xs': isCompact }"
                                        placeholder="0.00" 
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.amount" class="text-[10px] text-rose-500 mt-1 font-bold italic">{{ form.errors.amount }}</p>
                            </div>

                            <div class="relative">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-1.5" :class="{ 'text-xs': isCompact }">Date</label>
                                <div class="relative">
                                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input 
                                        type="date" 
                                        v-model="form.transaction_date" 
                                        class="form-input pl-11 !rounded-xl" 
                                        :class="{ 'py-2 text-xs': isCompact }"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-1.5" :class="{ 'text-xs': isCompact }">Category</label>
                                <div class="relative">
                                    <Tag class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)]" />
                                    <input 
                                        type="text" 
                                        v-model="form.category" 
                                        class="form-input pl-11 !rounded-xl" 
                                        :class="{ 'py-2 text-xs': isCompact }"
                                        placeholder="e.g. Sale, Maintenance, Rent"
                                    />
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-bold text-[var(--text-primary)] mb-1.5" :class="{ 'text-xs': isCompact }">Description</label>
                                <div class="relative">
                                    <FileText class="absolute left-4 top-4 w-4 h-4 text-[var(--text-tertiary)]" :class="{ 'top-2.5': isCompact }" />
                                    <textarea 
                                        v-model="form.description" 
                                        class="form-input pl-11 !rounded-2xl resize-none" 
                                        :class="[
                                            isCompact ? 'pt-2 h-16 text-xs' : 'pt-3 h-24'
                                        ]"
                                        placeholder="Optional notes..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 mt-8" :class="{ 'mt-4': isCompact }">
                            <button 
                                type="button" 
                                @click="showModal = false"
                                class="flex-1 rounded-xl border border-[var(--border-color)] text-[var(--text-primary)] font-bold hover:bg-[var(--bg-base)] transition-all"
                                :class="isCompact ? 'py-2.5 text-xs' : 'py-3.5'"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="flex-1 rounded-xl bg-[#6366f1] text-white font-bold hover:bg-[#4f46e5] shadow-lg shadow-[#6366f1]/20 transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                                :class="isCompact ? 'py-2.5 text-xs' : 'py-3.5'"
                            >
                                <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                                Save Transaction
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}

@keyframes popIn {
    from { transform: scale(0.95) translateY(10px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}
.anim-pop-in {
    animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.is-compact th {
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
    font-size: 0.65rem !important;
}

.is-compact td {
    padding-top: 0.625rem !important;
    padding-bottom: 0.625rem !important;
}

.compact-row td {
    font-size: 0.8rem !important;
}
</style>

