<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Squares2X2Icon, ShoppingBagIcon, TagIcon, UsersIcon,
    ChartBarIcon, Bars3Icon, XMarkIcon, ArrowLeftOnRectangleIcon,
    TicketIcon, UserGroupIcon, BanknotesIcon, Cog6ToothIcon,
    HomeIcon, StarIcon, BoltIcon, ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);

const navigation = [
    { name: 'Tableau de bord',  href: 'admin.dashboard',        icon: ChartBarIcon },
    { name: 'Catégories',       href: 'admin.categories.index', icon: Squares2X2Icon },
    { name: 'Produits',         href: 'admin.products.index',   icon: TagIcon },
    { name: 'Commandes',        href: 'admin.orders.index',     icon: ShoppingBagIcon },
    { name: 'Codes promo',      href: 'admin.coupons.index',    icon: TicketIcon },
    { name: 'Ventes flash',     href: 'admin.flash-sales.index',icon: BoltIcon },
    { name: 'Avis clients',     href: 'admin.reviews.index',    icon: StarIcon },
    { name: 'Clients',          href: 'admin.customers.index',  icon: UsersIcon },
    { name: 'Équipe',           href: 'admin.staff.index',      icon: UserGroupIcon },
    { name: 'Comptabilité',     href: 'admin.accounting.index', icon: BanknotesIcon },
    { name: 'Journal',          href: 'admin.activity-log.index', icon: ClipboardDocumentListIcon },
    { name: 'Paramètres',       href: 'admin.settings.index',   icon: Cog6ToothIcon },
];

function isActive(href) {
    try { return route().current(href + '*'); } catch { return false; }
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar desktop -->
        <aside class="hidden lg:flex lg:flex-col w-60 bg-sezy-900 text-white shrink-0 shadow-xl">
            <div class="px-5 py-4 border-b border-white/10">
                <Link :href="route('home')" class="flex items-center gap-2 group">
                    <img src="/images/logo-sezy.png" alt="SEZY" class="h-6 w-auto brightness-0 invert" onerror="this.style.display='none'" />
                    <span class="text-xl font-extrabold group-hover:text-sezy-100 transition">SEZY</span>
                </Link>
                <p class="text-xs text-white/40 mt-0.5">Administration</p>
            </div>

            <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
                <Link
                    v-for="item in navigation" :key="item.name"
                    :href="route(item.href)"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition"
                    :class="isActive(item.href) ? 'bg-sezy-dark text-white shadow-sm' : 'text-white/60 hover:bg-white/8 hover:text-white'"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" />
                    {{ item.name }}
                </Link>
            </nav>

            <div class="p-3 border-t border-white/10">
                <div class="flex items-center gap-2.5 mb-2">
                    <div class="w-7 h-7 rounded-full bg-sezy-dark flex items-center justify-center text-white font-bold text-xs shrink-0">
                        {{ (user?.first_name ?? 'A').charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold truncate text-white">{{ user?.full_name }}</p>
                        <p class="text-xs text-white/40 truncate">{{ user?.role }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('home')" class="flex items-center gap-1 text-xs text-white/50 hover:text-white transition">
                        <HomeIcon class="w-3 h-3" /> Boutique
                    </Link>
                    <span class="text-white/20">·</span>
                    <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-1 text-xs text-white/50 hover:text-red-300 transition">
                        <ArrowLeftOnRectangleIcon class="w-3 h-3" /> Déconnexion
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Sidebar mobile overlay -->
        <Transition name="fade">
            <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="sidebarOpen = false" />
                <aside class="absolute left-0 top-0 h-full w-64 bg-sezy-900 text-white shadow-2xl overflow-y-auto">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-white/10">
                        <span class="text-lg font-extrabold">SEZY Admin</span>
                        <button @click="sidebarOpen = false" class="p-1 rounded-lg hover:bg-white/10"><XMarkIcon class="w-5 h-5" /></button>
                    </div>
                    <nav class="p-2 space-y-0.5">
                        <Link v-for="item in navigation" :key="item.name" :href="route(item.href)"
                              class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition"
                              :class="isActive(item.href) ? 'bg-sezy-dark text-white' : 'text-white/60 hover:bg-white/10 hover:text-white'"
                              @click="sidebarOpen = false">
                            <component :is="item.icon" class="w-4 h-4 shrink-0" />
                            {{ item.name }}
                        </Link>
                    </nav>
                </aside>
            </div>
        </Transition>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Mobile topbar -->
            <header class="lg:hidden bg-sezy-900 text-white flex items-center justify-between px-4 py-3 shadow-md">
                <button @click="sidebarOpen = true" class="p-1 rounded-lg hover:bg-white/10">
                    <Bars3Icon class="w-6 h-6" />
                </button>
                <span class="font-bold text-sm">SEZY Admin</span>
                <Link :href="route('home')" class="p-1 rounded-lg hover:bg-white/10">
                    <HomeIcon class="w-5 h-5" />
                </Link>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
