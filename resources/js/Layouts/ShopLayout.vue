<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ShoppingBagIcon, UserIcon, HeartIcon,
    Bars3Icon, XMarkIcon, HomeIcon, Squares2X2Icon, ChevronDownIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';
import SearchBar from '@/Components/SearchBar.vue';

const page = usePage();
const toast = useToast();

const user = computed(() => page.props.auth?.user);
const cartCount = computed(() => page.props.cart?.count ?? 0);
const mobileMenuOpen = ref(false);

watch(() => page.props.flash?.success, (msg) => { if (msg) toast.success(msg); });
watch(() => page.props.flash?.error, (msg) => { if (msg) toast.error(msg); });
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-sm">
            <!-- Top bar -->
            <div class="hidden md:block bg-sezy-dark text-white text-xs py-1.5">
                <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                    <div class="flex items-center gap-4 text-white/70">
                        <span>✈️ Paris → Cotonou : <strong class="text-sezy-orange font-bold">20€/kg</strong></span>
                        <span class="text-white/30">|</span>
                        <span>Cotonou → Paris : <strong class="text-sezy-orange font-bold">10 000 FR/kg</strong></span>
                        <span class="text-white/30">|</span>
                        <a href="https://wa.me/22901420873" class="hover:text-white transition">+229 01 42 08 73 61</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="mailto:agencesezy@gmail.com" class="text-white/70 hover:text-white transition">agencesezy@gmail.com</a>
                        <div class="flex items-center gap-3">
                            <a href="https://instagram.com/sezy" target="_blank" rel="noopener" class="text-white/70 hover:text-white transition" title="Instagram">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="https://facebook.com/sezy" target="_blank" rel="noopener" class="text-white/70 hover:text-white transition" title="Facebook">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="https://wa.me/22900000000" target="_blank" rel="noopener" class="text-white/70 hover:text-white transition" title="WhatsApp">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a href="https://tiktok.com/@sezy" target="_blank" rel="noopener" class="text-white/70 hover:text-white transition" title="TikTok">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center justify-between h-16 gap-4">
                    <button class="md:hidden" @click="mobileMenuOpen = true" aria-label="Menu">
                        <Bars3Icon class="w-6 h-6 text-sezy-900" />
                    </button>

                    <Link :href="route('home')" class="flex items-center gap-2 shrink-0">
                        <img src="/images/logo-sezy.png" alt="SEZY" class="h-8 w-auto" onerror="this.style.display='none';this.nextElementSibling.style.display='block'" />
                        <span class="text-2xl font-extrabold tracking-tight text-sezy-dark hidden">SEZY</span>
                    </Link>

                    <div class="hidden md:flex flex-1 max-w-xl">
                        <SearchBar />
                    </div>

                    <nav class="flex items-center gap-4 sm:gap-5">
                        <Link :href="user ? route('wishlist.index') : route('login')" class="hidden sm:flex items-center text-gray-500 hover:text-sezy-dark transition relative group">
                            <HeartIcon class="w-6 h-6" />
                            <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] bg-sezy-dark text-white rounded px-1.5 py-0.5 opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">Favoris</span>
                        </Link>
                        <Link :href="route('cart.index')" class="relative text-gray-500 hover:text-sezy-dark transition group">
                            <ShoppingBagIcon class="w-6 h-6" />
                            <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-sezy-dark text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse-once">
                                {{ cartCount > 99 ? '99+' : cartCount }}
                            </span>
                        </Link>
                        <a href="/services/logistique"
                           class="hidden sm:flex items-center gap-1.5 bg-sezy-orange hover:bg-sezy-orange-dark text-white rounded-full px-4 py-1.5 text-sm font-bold transition shadow-md">
                            ✈️ Envoi colis
                        </a>
                        <Link
                            :href="user ? (user.role !== 'client' ? route('admin.dashboard') : route('profile.edit')) : route('login')"
                            class="hidden sm:flex items-center gap-1.5 bg-sezy-dark text-white rounded-full px-4 py-1.5 text-sm font-medium hover:bg-sezy-900 transition"
                        >
                            <UserIcon class="w-4 h-4" />
                            <span v-if="user" class="max-w-[100px] truncate">{{ user.first_name }}</span>
                            <span v-else>Connexion</span>
                        </Link>
                    </nav>
                </div>

                <div class="md:hidden pb-3">
                    <SearchBar />
                </div>
            </div>

            <!-- Mega-menu desktop -->
            <div class="hidden md:block border-t border-gray-100 bg-white">
                <div class="max-w-7xl mx-auto px-6 flex gap-1 text-sm font-medium text-gray-700 overflow-x-auto">
                    <Link :href="route('products.index')"
                          class="py-3 px-3 hover:text-sezy-dark whitespace-nowrap rounded-b-lg transition hover:bg-sezy-50 border-b-2"
                          :class="route().current('products.index') && !$page.url.includes('category') ? 'border-sezy-dark text-sezy-dark' : 'border-transparent'">
                        Tous les produits
                    </Link>
                    <Link v-for="cat in $page.props.headerCategories ?? []" :key="cat.id"
                          :href="route('products.index', { category: cat.slug })"
                          class="py-3 px-3 hover:text-sezy-dark whitespace-nowrap rounded-b-lg transition hover:bg-sezy-50 border-b-2 border-transparent">
                        {{ cat.name }}
                    </Link>
                    <div class="flex-1"></div>
                    <a href="/services/logistique" class="py-3 px-3 hover:text-sezy-orange whitespace-nowrap rounded-b-lg transition hover:bg-sezy-orange-50 border-b-2 border-transparent flex items-center gap-1.5 font-semibold text-sezy-orange-dark">
                        ✈️ Logistique
                    </a>
                    <a href="/services/conciergerie" class="py-3 px-3 hover:text-sezy-orange whitespace-nowrap rounded-b-lg transition hover:bg-sezy-orange-50 border-b-2 border-transparent flex items-center gap-1.5 text-gray-600">
                        🛍️ Conciergerie
                    </a>
                    <a href="/services/etudes" class="py-3 px-3 hover:text-sezy-orange whitespace-nowrap rounded-b-lg transition hover:bg-sezy-orange-50 border-b-2 border-transparent flex items-center gap-1.5 text-gray-600">
                        🎓 Études & Carrière
                    </a>
                </div>
            </div>
        </header>

        <!-- Mobile slide-in menu -->
        <Transition name="slide-fade">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="mobileMenuOpen = false" />
                <div class="absolute left-0 top-0 h-full w-72 bg-white shadow-2xl overflow-y-auto">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <img src="/images/logo-sezy.png" alt="SEZY" class="h-7 w-auto" onerror="this.outerHTML='<span class=\'text-xl font-bold text-sezy-dark\'>SEZY</span>'" />
                        <button @click="mobileMenuOpen = false" class="p-1 rounded-lg hover:bg-gray-100">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="p-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Catégories</p>
                        <nav class="flex flex-col gap-0.5">
                            <Link :href="route('products.index')"
                                  class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 font-medium text-gray-800 transition"
                                  @click="mobileMenuOpen = false">
                                🛍️ Tous les produits
                            </Link>
                            <Link v-for="cat in $page.props.headerCategories ?? []" :key="cat.id"
                                  :href="route('products.index', { category: cat.slug })"
                                  class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 text-gray-700 transition"
                                  @click="mobileMenuOpen = false">
                                {{ cat.name }}
                            </Link>
                        </nav>

                        <div class="border-t border-gray-100 my-4" />

                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Nos Services</p>
                        <nav class="flex flex-col gap-0.5 mb-4">
                            <a href="/services/logistique" class="py-2.5 px-3 rounded-xl hover:bg-sezy-orange-50 font-semibold text-sezy-orange-dark transition flex items-center gap-2" @click="mobileMenuOpen = false">
                                ✈️ Logistique & Colis
                            </a>
                            <a href="/services/conciergerie" class="py-2.5 px-3 rounded-xl hover:bg-sezy-orange-50 text-gray-700 transition flex items-center gap-2" @click="mobileMenuOpen = false">
                                🛍️ Conciergerie Shopping
                            </a>
                            <a href="/services/etudes" class="py-2.5 px-3 rounded-xl hover:bg-sezy-orange-50 text-gray-700 transition flex items-center gap-2" @click="mobileMenuOpen = false">
                                🎓 Études & Carrière
                            </a>
                        </nav>

                        <div class="border-t border-gray-100 my-4" />

                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Mon compte</p>
                        <nav class="flex flex-col gap-0.5">
                            <template v-if="!user">
                                <Link :href="route('login')" class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 font-semibold text-sezy-dark transition" @click="mobileMenuOpen = false">Se connecter</Link>
                                <Link :href="route('register')" class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 text-gray-700 transition" @click="mobileMenuOpen = false">Créer un compte</Link>
                            </template>
                            <template v-else>
                                <div class="px-3 py-2 bg-sezy-50 rounded-xl mb-2">
                                    <p class="font-semibold text-sezy-dark text-sm">{{ user.full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                                </div>
                                <Link :href="route('profile.edit')" class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 text-gray-700 transition" @click="mobileMenuOpen = false">Mon profil</Link>
                                <Link :href="route('orders.index')" class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 text-gray-700 transition" @click="mobileMenuOpen = false">Mes commandes</Link>
                                <Link :href="route('wishlist.index')" class="py-2.5 px-3 rounded-xl hover:bg-sezy-50 text-gray-700 transition" @click="mobileMenuOpen = false">Mes favoris</Link>
                                <Link :href="route('logout')" method="post" as="button" class="py-2.5 px-3 rounded-xl hover:bg-red-50 text-left text-red-600 transition w-full" @click="mobileMenuOpen = false">Déconnexion</Link>
                            </template>
                        </nav>

                        <div class="border-t border-gray-100 my-4" />
                        <div class="flex items-center justify-center gap-4 py-2">
                            <a href="https://instagram.com/sezy" target="_blank" class="text-gray-500 hover:text-pink-600 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="https://facebook.com/sezy" target="_blank" class="text-gray-500 hover:text-blue-600 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a href="https://wa.me/22900000000" target="_blank" class="text-gray-500 hover:text-green-600 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a href="https://tiktok.com/@sezy" target="_blank" class="text-gray-500 hover:text-gray-900 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Page content -->
        <main class="flex-1 pb-20 md:pb-0">
            <slot />
        </main>

        <!-- Bottom navigation mobile -->
        <nav class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-100 flex md:hidden shadow-lg">
            <Link :href="route('home')" class="flex-1 flex flex-col items-center py-2 text-gray-500 hover:text-sezy-dark transition">
                <HomeIcon class="w-5 h-5" /><span class="text-[10px] mt-0.5 font-medium">Accueil</span>
            </Link>
            <Link :href="route('products.index')" class="flex-1 flex flex-col items-center py-2 text-gray-500 hover:text-sezy-dark transition">
                <Squares2X2Icon class="w-5 h-5" /><span class="text-[10px] mt-0.5 font-medium">Catalogue</span>
            </Link>
            <Link :href="route('cart.index')" class="flex-1 flex flex-col items-center py-2 text-gray-500 hover:text-sezy-dark transition relative">
                <div class="relative">
                    <ShoppingBagIcon class="w-5 h-5" />
                    <span v-if="cartCount > 0" class="absolute -top-1.5 -right-1.5 bg-sezy-dark text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">{{ cartCount }}</span>
                </div>
                <span class="text-[10px] mt-0.5 font-medium">Panier</span>
            </Link>
            <Link :href="user ? route('wishlist.index') : route('login')" class="flex-1 flex flex-col items-center py-2 text-gray-500 hover:text-sezy-dark transition">
                <HeartIcon class="w-5 h-5" /><span class="text-[10px] mt-0.5 font-medium">Favoris</span>
            </Link>
            <Link :href="user ? route('profile.edit') : route('login')" class="flex-1 flex flex-col items-center py-2 text-gray-500 hover:text-sezy-dark transition">
                <UserIcon class="w-5 h-5" /><span class="text-[10px] mt-0.5 font-medium">Compte</span>
            </Link>
        </nav>

        <!-- Footer desktop -->
        <footer class="hidden md:block bg-sezy-dark text-white mt-16">
            <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-8 text-sm">
                <div>
                    <img src="/images/logo-sezy.png" alt="SEZY" class="h-9 w-auto mb-3 brightness-0 invert" onerror="this.outerHTML='<span class=\'text-2xl font-extrabold\'>SEZY</span>'" />
                    <p class="text-white/60 text-sm leading-relaxed">Votre passerelle de confiance entre l'Europe et l'Afrique. Boutique, logistique, conciergerie et accompagnement.</p>
                    <div class="mt-3 flex items-center gap-2 text-xs text-sezy-orange font-semibold">
                        <span>ifu: 3202323198217</span>
                    </div>
                    <div class="flex items-center gap-3 mt-4">
                        <a href="https://instagram.com/sezy" target="_blank" class="text-white/50 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://facebook.com/sezy" target="_blank" class="text-white/50 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://wa.me/22900000000" target="_blank" class="text-white/50 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <a href="https://tiktok.com/@sezy" target="_blank" class="text-white/50 hover:text-white transition p-2 rounded-lg hover:bg-white/10">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-white">Boutique</h4>
                    <ul class="space-y-2.5 text-white/60">
                        <li><Link :href="route('products.index')" class="hover:text-white transition">Catalogue</Link></li>
                        <li><Link :href="route('products.index', { sort: 'newest' })" class="hover:text-white transition">Nouveautés</Link></li>
                        <li><Link :href="route('cart.index')" class="hover:text-white transition">Mon panier</Link></li>
                        <li><Link :href="route('wishlist.index')" class="hover:text-white transition">Mes favoris</Link></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-white">Services</h4>
                    <ul class="space-y-2.5 text-white/60">
                        <li><a href="/services/logistique" class="hover:text-sezy-orange transition flex items-center gap-1.5">✈️ Logistique & Colis</a></li>
                        <li><a href="/services/conciergerie" class="hover:text-white transition flex items-center gap-1.5">🛍️ Conciergerie Shopping</a></li>
                        <li><a href="/services/etudes" class="hover:text-white transition flex items-center gap-1.5">🎓 Études & Carrière</a></li>
                        <li class="pt-1 text-sezy-orange font-semibold text-xs">Paris → Cotonou · 20€/kg</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-white">Paiement</h4>
                    <div class="space-y-2 text-white/60">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>MTN Mobile Money
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-400 rounded-full"></span>Moov Money
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-400 rounded-full"></span>Wave
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-white rounded-full"></span>Carte bancaire
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-white">Contact</h4>
                    <ul class="space-y-2.5 text-white/60">
                        <li>
                            <a href="mailto:agencesezy@gmail.com" class="hover:text-white transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                agencesezy@gmail.com
                            </a>
                        </li>
                        <li><a href="https://wa.me/22901420873" target="_blank" class="hover:text-white transition">+229 01 42 08 73 61</a></li>
                        <li><a href="https://wa.me/22901663864" target="_blank" class="hover:text-white transition">+229 01 66 38 64 17</a></li>
                        <li class="text-xs text-white/40">WhatsApp uniquement</li>
                        <li class="flex items-center gap-2 mt-3 pt-3 border-t border-white/10">
                            <span class="text-green-400">🔒</span> Paiement sécurisé SSL
                        </li>
                        <li class="flex items-center gap-2">
                            <span>⭐</span> Avis acheteurs vérifiés
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 py-4">
                <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-white/40">
                    <span>© {{ new Date().getFullYear() }} SEZY. Tous droits réservés.</span>
                    <span>Conçu avec ❤️ pour le Bénin et sa diaspora</span>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.slide-fade-enter-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-fade-leave-active { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-fade-enter-from { opacity: 0; }
.slide-fade-leave-to { opacity: 0; }
</style>
