<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Package, ShoppingCart, Wrench, 
    ArrowLeftRight, FileBarChart, Layers, ClipboardCheck, 
    History, Settings, Bell, Search, Sun, Moon, X,
    ChevronRight, User, LogOut, Command, Tag, 
    MapPin, Truck, Scan, AlertCircle, Users, Activity,
    CalendarClock, CheckCircle2
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance, isDark } = useAppearance();

// UI State
const notificationsOpen = ref(false);
const notificationsSeen = ref(false);
const profileOpen = ref(false);
const langOpen = ref(false);
const searchQuery = ref('');
const searchFocused = ref(false);

const languages = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'it', name: 'Italiano', flag: '🇮🇹' }
];

const currentLocale = computed(() => usePage().props.locale || 'en');
const currentLanguage = computed(() => languages.find(l => l.code === currentLocale.value) || languages[0]);

const changeLanguage = (code) => {
    router.post(route('locale.update'), { locale: code }, {
        onSuccess: () => langOpen.value = false
    });
};

const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value;
    if (notificationsOpen.value) {
        notificationsSeen.value = true;
    }
};

const alertsData = computed(() => usePage().props.alerts || { count: 0, latest: [] });
const notifications = computed(() => alertsData.value.latest);

const mockSearchResults = [
    { id: 1, title: 'MacBook Pro 14"', category: 'Laptops', type: 'product' },
    { id: 2, title: 'Dell XPS 15', category: 'Laptops', type: 'product' },
    { id: 3, title: 'Logitech MX Master', category: 'Accessories', type: 'asset' },
    { id: 4, title: 'New Order #4521', category: 'Orders', type: 'order' }
];

const filteredSearchResults = computed(() => {
    if (!searchQuery.value) return [];
    return mockSearchResults.filter(item => 
        item.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    ).slice(0, 5);
});

const user = computed(() => usePage().props.auth?.user || { name: 'Demo User', email: 'admin@demo.com' });

const userInitials = computed(() => {
    const name = user.value.name || 'User';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
});

const closeDropdowns = (e) => {
    if (!e.target.closest('.dropdown-trigger') && !e.target.closest('.search-container')) {
        notificationsOpen.value = false;
        profileOpen.value = false;
        searchFocused.value = false;
    }
};

let pollInterval;

onMounted(() => {
    window.addEventListener('click', closeDropdowns);
    
    // Live Polling every 60 seconds to keep alerts fresh
    pollInterval = setInterval(() => {
        router.reload({ only: ['alerts'], preserveScroll: true });
    }, 60000);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdowns);
    if (pollInterval) clearInterval(pollInterval);
});

const logout = () => {
    router.post(route('logout'));
};

// Flash Messages / Toasts
const flash = computed(() => usePage().props.flash || {});
const toast = ref(null);

watch(() => usePage().props.flash, (newFlash) => {
    if (newFlash?.success) {
        toast.value = { type: 'success', message: newFlash.success };
        setTimeout(() => toast.value = null, 5000);
    } else if (newFlash?.error) {
        toast.value = { type: 'error', message: newFlash.error };
        setTimeout(() => toast.value = null, 5000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div class="app-container flex h-screen w-full bg-[var(--bg-base)] text-[var(--text-primary)] transition-colors duration-300" :class="{ 'dark': isDark }">
        <!-- Sidebar -->
        <aside class="w-[var(--sidebar-width)] bg-[var(--bg-surface)] border-r border-[var(--border-color)] flex flex-col z-30 transition-all duration-300">
            <div class="h-[var(--header-height)] flex items-center px-6 border-b border-[var(--border-color)] gap-3 bg-[var(--bg-surface)]">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#10b981] to-[#059669] flex items-center justify-center shadow-lg shadow-[#10b981]/20">
                    <Package class="text-white w-6 h-6" />
                </div>
                <h2 class="text-xl font-black tracking-tighter text-[var(--text-primary)]">Stockly.</h2>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-4 flex flex-col gap-1.5 custom-scrollbar">
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-4">{{ $t('Overview') }}</div>
                <Link :href="route('dashboard')" class="sidebar-nav-link" :class="{ 'active': $page.component === 'Dashboard' }">
                    <LayoutDashboard /> {{ $t('Dashboard') }}
                </Link>

                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-6">{{ $t('Inventory') }}</div>
                <Link :href="route('products.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Products') }">
                    <Package /> {{ $t('Products') }}
                </Link>
                <Link :href="route('categories.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Categories') }">
                    <Tag /> {{ $t('Categories') }}
                </Link>
                <Link :href="route('locations.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Locations') }">
                    <MapPin /> {{ $t('Locations') }}
                </Link>
                <Link :href="route('suppliers.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Suppliers') }">
                    <Truck /> {{ $t('Suppliers') }}
                </Link>

                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-6">{{ $t('Operations') }}</div>
                <Link :href="route('inventory.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Inventory') }">
                    <ArrowLeftRight /> {{ $t('Stock Movements') }}
                </Link>
                <Link :href="route('orders.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Orders') }">
                    <ShoppingCart /> {{ $t('Orders & Docs') }}
                </Link>
                <Link :href="route('inventory.scanner')" class="sidebar-nav-link" :class="{ 'active': route().current('inventory.scanner') }">
                    <Scan /> {{ $t('Barcode Scanner') }}
                </Link>
                <Link :href="route('stock-take.index')" class="sidebar-nav-link" :class="{ 'active': route().current('stock-take.index') }">
                    <ClipboardCheck /> {{ $t('Stocktakes') }}
                </Link>

                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-6">{{ $t('Monitoring') }}</div>
                <Link :href="route('monitoring.expiry')" class="sidebar-nav-link" :class="{ 'active': route().current('monitoring.expiry') }">
                    <CalendarClock /> {{ $t('Expiry Tracking') }}
                </Link>
                <Link :href="route('monitoring.alerts')" class="sidebar-nav-link relative" :class="{ 'active': route().current('monitoring.alerts') }">
                    <AlertCircle /> {{ $t('Alerts') }}
                    <span v-if="alertsData.count > 0" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-red-500 text-[8px] font-black text-white flex items-center justify-center animate-pulse">
                        {{ alertsData.count }}
                    </span>
                </Link>

                <template v-if="user.role === 'Admin' || user.role === 'Manager'">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-6">{{ $t('Insights') }}</div>
                    <Link :href="route('reports.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Reports') }">
                        <FileBarChart /> {{ $t('Reports') }}
                    </Link>
                    <Link href="#" class="sidebar-nav-link">
                        <Activity /> {{ $t('Activity Log') }}
                    </Link>
                </template>

                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-tertiary)] px-4 mb-2 mt-6">{{ $t('System') }}</div>
                <Link v-if="user.role === 'Admin'" :href="route('users.index')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('Users/') }">
                    <Users /> {{ $t('Team') }}
                </Link>
                <Link :href="route('profile.edit')" class="sidebar-nav-link" :class="{ 'active': $page.component.startsWith('settings/') }">
                    <Settings /> {{ $t('Settings') }}
                </Link>
            </nav>

            <!-- Subscription Widget -->
            <div class="p-4 mt-auto">
                <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-lg overflow-hidden relative group">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-80">{{ user.plan === 'pro' ? $t('Pro Plan') : $t('Basic Plan') }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[8px] font-bold border border-emerald-500/20">{{ $t('Active') }}</span>
                        </div>
                        <div class="flex items-end gap-1 mb-3">
                            <span class="text-2xl font-black leading-none">{{ user.days_left || 0 }}</span>
                            <span class="text-[10px] font-bold opacity-80 mb-1">{{ $t('days') }} {{ $t('left') }}</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-1 mb-4">
                            <div class="bg-white rounded-full h-full" :style="{ width: Math.min(100, ((user.days_left || 0) / 30) * 100) + '%' }"></div>
                        </div>
                        <button class="w-full py-2 rounded-xl bg-white text-indigo-600 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all">
                            {{ $t('Renew Now') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-[var(--border-color)] bg-[var(--bg-surface-hover)]/30">
                <Link :href="route('profile.edit')" class="sidebar-nav-link group" :class="{ 'active': $page.component.startsWith('settings/') }">
                    <Settings class="group-hover:rotate-90 transition-transform duration-500" /> Settings
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Header -->
            <header class="h-[var(--header-height)] flex items-center justify-between px-8 border-b border-[var(--border-color)] bg-[var(--bg-surface)] mt-0 sticky top-0 z-40 transition-all duration-300">
                <!-- Search -->
                <div class="relative w-[400px] search-container">
                    <div class="relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--text-tertiary)] transition-colors group-focus-within:text-[var(--primary)]" />
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            @focus="searchFocused = true"
                            placeholder="Type to search (Ctrl + K)" 
                            class="w-full bg-[var(--bg-base)] border border-[var(--border-color)] rounded-2xl py-2.5 pl-11 pr-12 text-sm focus:outline-none focus:border-[var(--primary)] focus:ring-4 focus:ring-[var(--primary-light)] text-[var(--text-primary)] transition-all placeholder:text-[var(--text-tertiary)]" 
                        />
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 px-2 py-1 rounded bg-[var(--bg-surface)] border border-[var(--border-color)] text-[10px] font-black text-[var(--text-tertiary)]">
                            <Command class="w-3 h-3" /> K
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <Transition name="fade-down">
                        <div v-if="searchFocused && searchQuery" class="absolute top-full left-0 w-full mt-2 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl p-2 z-50 overflow-hidden">
                            <div v-if="filteredSearchResults.length > 0">
                                <div v-for="res in filteredSearchResults" :key="res.id" class="flex items-center justify-between p-3 hover:bg-[var(--bg-surface-hover)] rounded-xl cursor-pointer group transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-[var(--primary-light)] flex items-center justify-center text-[var(--primary)]">
                                            <Package v-if="res.type === 'product'" class="w-5 h-5" />
                                            <ShoppingCart v-else-if="res.type === 'order'" class="w-5 h-5" />
                                            <Wrench v-else class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold">{{ res.title }}</p>
                                            <p class="text-[10px] uppercase tracking-widest text-[var(--text-tertiary)] font-bold">{{ res.category }}</p>
                                        </div>
                                    </div>
                                    <ChevronRight class="w-4 h-4 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all text-[var(--primary)]" />
                                </div>
                            </div>
                            <div v-else class="p-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-[var(--bg-base)] flex items-center justify-center mx-auto mb-3">
                                    <Search class="w-6 h-6 text-[var(--text-tertiary)]" />
                                </div>
                                <p class="text-sm font-bold text-[var(--text-secondary)]">No results for "{{ searchQuery }}"</p>
                            </div>
                        </div>
                    </Transition>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Language Switcher -->
                    <div class="relative dropdown-trigger">
                        <button @click="langOpen = !langOpen" class="w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-lg hover:bg-[var(--bg-surface-hover)] transition-all shadow-sm">
                            {{ currentLanguage.flag }}
                        </button>
                        <Transition name="fade-down">
                            <div v-if="langOpen" class="absolute top-full right-0 mt-3 w-40 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl p-2 z-50">
                                <button v-for="lang in languages" :key="lang.code" 
                                    @click="changeLanguage(lang.code)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--bg-surface-hover)] transition-all"
                                    :class="{ 'bg-[var(--primary-light)] text-[var(--primary)]': currentLocale === lang.code }"
                                >
                                    <span class="text-base">{{ lang.flag }}</span>
                                    <span class="text-xs font-bold">{{ lang.name }}</span>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Theme Toggle -->
                    <button @click="updateAppearance(isDark ? 'light' : 'dark')" class="w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-secondary)] hover:text-[var(--primary)] transition-all">
                        <Moon v-if="isDark" class="w-5 h-5" />
                        <Sun v-else class="w-5 h-5" />
                    </button>

                    <!-- Notifications -->
                    <div class="relative dropdown-trigger">
                        <button @click="toggleNotifications" class="w-10 h-10 rounded-xl bg-[var(--bg-base)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-secondary)] hover:text-[var(--primary)] transition-all relative">
                            <Bell class="w-5 h-5" />
                            <span v-if="alertsData.count > 0 && !notificationsSeen" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-lg border-2 border-[var(--bg-surface)] text-[9px] font-black text-white flex items-center justify-center shadow-lg">
                                {{ alertsData.count }}
                            </span>
                        </button>
                        
                        <Transition name="fade-down">
                            <div v-if="notificationsOpen" class="absolute top-full right-0 mt-3 w-80 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl overflow-hidden z-50">
                                <div class="p-4 border-b border-[var(--border-color)] flex items-center justify-between bg-[var(--bg-surface-hover)]/30">
                                    <h3 class="font-black text-xs uppercase tracking-[0.1em]">System Alerts</h3>
                                    <span v-if="alertsData.count > 0" class="px-2 py-0.5 rounded-full bg-rose-500 text-[9px] font-black text-white">{{ alertsData.count }} NEW</span>
                                </div>
                                <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                    <div v-for="note in notifications" :key="note.id" class="p-4 border-b border-[var(--border-color)] hover:bg-[var(--bg-surface-hover)] transition-all cursor-pointer group">
                                        <div class="flex gap-3">
                                            <div :class="{
                                                'w-2 h-2 rounded-full mt-1.5 shrink-0': true,
                                                'bg-amber-500': note.type === 'warning',
                                                'bg-emerald-500': note.type === 'success',
                                                'bg-blue-500': note.type === 'info'
                                            }"></div>
                                            <div>
                                                <p class="text-sm font-bold group-hover:text-[var(--primary)] transition-colors">{{ note.title }}</p>
                                                <p class="text-xs text-[var(--text-secondary)] mt-0.5">{{ note.message }}</p>
                                                <p class="text-[9px] font-bold text-[var(--text-tertiary)] uppercase mt-2 tracking-wider">{{ note.time }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button class="w-full py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-tertiary)] hover:bg-[var(--bg-surface-hover)] hover:text-[var(--primary)] transition-all">View All Notifications</button>
                            </div>
                        </Transition>
                    </div>
                    
                    <!-- Profile -->
                    <div class="relative dropdown-trigger pl-4 border-l border-[var(--border-color)]">
                        <button @click="profileOpen = !profileOpen" class="flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#10b981] to-[#059669] flex items-center justify-center font-black text-white shadow-lg ring-2 ring-transparent group-hover:ring-[#10b981]/30 transition-all font-mono">
                                {{ userInitials }}
                            </div>
                            <div class="hidden md:flex flex-col items-start">
                                <span class="text-xs font-black text-[var(--text-primary)] leading-none mb-1">{{ user.name }}</span>
                                <span class="text-[9px] font-black text-[var(--text-tertiary)] uppercase tracking-widest">{{ user.role || 'Staff' }}</span>
                            </div>
                        </button>

                        <Transition name="fade-down">
                            <div v-if="profileOpen" class="absolute top-full right-0 mt-3 w-64 bg-[var(--bg-surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl p-2 z-50 overflow-hidden">
                                <div class="p-4 border-b border-[var(--border-color)] mb-2">
                                    <p class="text-xs font-black text-[var(--text-tertiary)] uppercase tracking-widest mb-1">Signed in as</p>
                                    <p class="text-sm font-bold truncate text-[var(--text-primary)]">{{ user.email }}</p>
                                </div>
                                <Link :href="route('profile.edit')" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--bg-surface-hover)] text-sm font-bold transition-all group">
                                    <div class="w-8 h-8 rounded-lg bg-[var(--bg-base)] flex items-center justify-center group-hover:bg-[var(--primary-light)] group-hover:text-[var(--primary)] transition-all">
                                        <User class="w-4 h-4" />
                                    </div>
                                    Account Settings
                                </Link>
                                <button @click="logout" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-red-500/10 text-red-500 text-sm font-bold transition-all group mt-1">
                                    <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center group-hover:bg-red-500 group-hover:text-white transition-all">
                                        <LogOut class="w-4 h-4" />
                                    </div>
                                    Sign Out
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Global Toast -->
            <Transition name="fade-down">
                <div v-if="toast" class="fixed top-24 right-8 z-[100] max-w-sm w-full animate-in slide-in-from-right-8 fade-in">
                    <div :class="{
                        'p-4 rounded-2xl border shadow-2xl flex items-center gap-3 transition-all backdrop-blur-md': true,
                        'bg-emerald-500/10 border-emerald-500/20 text-emerald-500': toast.type === 'success',
                        'bg-rose-500/10 border-rose-500/20 text-rose-500': toast.type === 'error'
                    }">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-current/10">
                            <CheckCircle2 v-if="toast.type === 'success'" class="w-5 h-5" />
                            <AlertCircle v-else class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-black uppercase tracking-widest leading-none mb-1">{{ toast.type }}</p>
                            <p class="text-xs font-bold opacity-80">{{ toast.message }}</p>
                        </div>
                        <button @click="toast = null" class="p-2 hover:bg-current/10 rounded-lg transition-colors">
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Page Content -->
            <div class="flex-1 overflow-y-auto p-8 relative custom-scrollbar bg-[var(--bg-base)]/50">
                <!-- Background Decoration -->
                <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-[var(--primary-light)] to-transparent pointer-events-none -z-10 opacity-50"></div>
                
                <slot />
            </div>
        </main>
    </div>
</template>

<style>
.fade-down-enter-active, .fade-down-leave-active {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-down-enter-from, .fade-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--text-tertiary);
}
</style>
