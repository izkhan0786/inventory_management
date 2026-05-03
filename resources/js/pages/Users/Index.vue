<script setup>
import PremiumLayout from '@/Layouts/PremiumLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    Users, UserPlus, Search, MoreVertical, 
    Edit2, Trash2, Shield, Calendar, 
    CreditCard, CheckCircle2, XCircle, AlertTriangle
} from 'lucide-vue-next';

const props = defineProps({
    users: Array
});

const searchQuery = ref('');
const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'Staff',
    plan_type: 'basic',
    subscription_expires_at: '',
    is_suspended: false
});

const filteredUsers = computed(() => {
    return props.users.filter(u => 
        u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    form.plan_type = user.plan_type;
    form.subscription_expires_at = user.subscription_expires_at ? user.subscription_expires_at.split('T')[0] : '';
    form.is_suspended = !!user.is_suspended;
    isModalOpen.value = true;
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (user) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', user.id));
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'Admin': return 'bg-indigo-500/20 text-indigo-400 border-indigo-500/20';
        case 'Manager': return 'bg-amber-500/20 text-amber-400 border-amber-500/20';
        default: return 'bg-slate-500/20 text-slate-400 border-slate-500/20';
    }
};
</script>

<template>
    <Head title="Team Management" />

    <PremiumLayout>
        <div class="p-6 md:p-8 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-2xl bg-indigo-500/10 border border-indigo-500/20">
                            <Users class="w-6 h-6 text-indigo-400" />
                        </div>
                        <h1 class="text-3xl font-black tracking-tight text-white">Team Management</h1>
                    </div>
                    <p class="text-slate-400 text-sm ml-12">Manage your organization users, roles and SaaS subscriptions.</p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-sm transition-all shadow-lg shadow-indigo-500/20 group"
                >
                    <UserPlus class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    Add Team Member
                </button>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-6 rounded-3xl bg-[var(--bg-surface)] border border-[var(--border-color)] space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="p-2 rounded-xl bg-indigo-500/10"><Users class="w-5 h-5 text-indigo-400" /></div>
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Total Users</span>
                    </div>
                    <div class="text-3xl font-black text-white">{{ users.length }}</div>
                </div>
                <div class="p-6 rounded-3xl bg-[var(--bg-surface)] border border-[var(--border-color)] space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="p-2 rounded-xl bg-emerald-500/10"><CheckCircle2 class="w-5 h-5 text-emerald-400" /></div>
                        <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Active Subs</span>
                    </div>
                    <div class="text-3xl font-black text-white">{{ users.filter(u => !u.is_suspended).length }}</div>
                </div>
                <div class="p-6 rounded-3xl bg-[var(--bg-surface)] border border-[var(--border-color)] space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="p-2 rounded-xl bg-amber-500/10"><AlertTriangle class="w-5 h-5 text-amber-400" /></div>
                        <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest">Expiring Soon</span>
                    </div>
                    <div class="text-3xl font-black text-white">2</div>
                </div>
                <div class="p-6 rounded-3xl bg-[var(--bg-surface)] border border-[var(--border-color)] space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="p-2 rounded-xl bg-rose-500/10"><XCircle class="w-5 h-5 text-rose-400" /></div>
                        <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Suspended</span>
                    </div>
                    <div class="text-3xl font-black text-white">{{ users.filter(u => u.is_suspended).length }}</div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2.5rem] overflow-hidden shadow-2xl backdrop-blur-xl">
                <div class="p-6 border-b border-[var(--border-color)] flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[var(--bg-surface-hover)]/30">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search by name or email..." 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-2.5 pl-11 pr-4 text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-white transition-all"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[var(--bg-surface-hover)]/20">
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">User Details</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Role</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">SaaS Plan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Subscription Expiry</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-slate-500 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border-color)]">
                            <tr v-for="user in filteredUsers" :key="user.id" class="group hover:bg-[var(--bg-surface-hover)]/50 transition-all cursor-pointer">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-sm shadow-lg">
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-white">{{ user.name }}</span>
                                            <span class="text-xs text-slate-500">{{ user.email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <span :class="['px-3 py-1 rounded-full text-[10px] font-black border', getRoleBadgeClass(user.role)]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1.5 rounded-lg bg-indigo-500/10">
                                            <CreditCard class="w-3.5 h-3.5 text-indigo-400" />
                                        </div>
                                        <span class="text-xs font-bold text-slate-300 capitalize">{{ user.plan_type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="w-3.5 h-3.5 text-slate-500" />
                                        <span class="text-xs font-bold" :class="user.subscription_expires_at && new Date(user.subscription_expires_at) < new Date() ? 'text-rose-400' : 'text-slate-300'">
                                            {{ formatDate(user.subscription_expires_at) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div v-if="user.is_suspended" class="flex items-center gap-1.5 text-rose-400">
                                        <XCircle class="w-3.5 h-3.5" />
                                        <span class="text-[10px] font-black uppercase">Suspended</span>
                                    </div>
                                    <div v-else class="flex items-center gap-1.5 text-emerald-400">
                                        <CheckCircle2 class="w-3.5 h-3.5" />
                                        <span class="text-[10px] font-black uppercase">Active</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEditModal(user)" class="p-2 hover:bg-white/10 rounded-xl transition-all text-slate-400 hover:text-white">
                                            <Edit2 class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteUser(user)" class="p-2 hover:bg-rose-500/10 rounded-xl transition-all text-slate-400 hover:text-rose-400">
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

        <!-- Create/Edit Modal -->
        <Transition name="modal">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="closeModal"></div>
                <div class="relative w-full max-w-xl bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-[2.5rem] shadow-2xl overflow-hidden">
                    <div class="p-8 border-b border-[var(--border-color)] bg-[var(--bg-surface-hover)]/30">
                        <h2 class="text-2xl font-black text-white">{{ editingUser ? 'Edit Team Member' : 'Add Team Member' }}</h2>
                        <p class="text-slate-400 text-sm">Configure user profile, role and SaaS subscription details.</p>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Full Name</label>
                                <input v-model="form.name" type="text" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-600" placeholder="John Doe" />
                                <div v-if="form.errors.name" class="text-rose-400 text-[10px] font-bold mt-1 uppercase">{{ form.errors.name }}</div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Email Address</label>
                                <input v-model="form.email" type="email" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-600" placeholder="john@example.com" />
                                <div v-if="form.errors.email" class="text-rose-400 text-[10px] font-bold mt-1 uppercase">{{ form.errors.email }}</div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">{{ editingUser ? 'New Password (Optional)' : 'Password' }}</label>
                                <input v-model="form.password" type="password" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-600" />
                                <div v-if="form.errors.password" class="text-rose-400 text-[10px] font-bold mt-1 uppercase">{{ form.errors.password }}</div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">User Role</label>
                                <select v-model="form.role" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="Admin">Admin (Full Access)</option>
                                    <option value="Manager">Manager (Operations)</option>
                                    <option value="Staff">Staff (Scanner/Inventory)</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Subscription Plan</label>
                                <select v-model="form.plan_type" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="basic">Basic Plan</option>
                                    <option value="pro">Pro Plan</option>
                                    <option value="enterprise">Enterprise</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 ml-1">Expiry Date</label>
                                <input v-model="form.subscription_expires_at" type="date" class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-3 px-4 text-sm text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                        </div>

                        <div v-if="editingUser" class="pt-4 border-t border-[var(--border-color)]">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative w-12 h-6 bg-slate-700 rounded-full transition-all group-hover:bg-slate-600" :class="{ 'bg-rose-500': form.is_suspended }">
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all" :class="{ 'translate-x-6': form.is_suspended }"></div>
                                </div>
                                <input v-model="form.is_suspended" type="checkbox" class="hidden" />
                                <span class="text-sm font-bold" :class="form.is_suspended ? 'text-rose-400' : 'text-slate-300'">Suspend Account Access</span>
                            </label>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button type="button" @click="closeModal" class="flex-1 py-4 rounded-2xl bg-[var(--bg-base)] border border-[var(--border-color)] text-white text-[10px] font-black uppercase tracking-widest hover:bg-white/5 transition-all">Cancel</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 py-4 rounded-2xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-500/20 disabled:opacity-50">
                                {{ editingUser ? 'Update Profile' : 'Create User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </PremiumLayout>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }

/* Custom scrollbar for form */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
</style>
