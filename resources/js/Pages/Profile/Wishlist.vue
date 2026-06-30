<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps({ wishlist: Array });
const toast = useToast();

const products = computed(() => props.wishlist.map((w) => w.product).filter(Boolean));

const shareLink = computed(() => window.location.origin + route('wishlist.index'));
const copied = ref(false);

async function share() {
    try {
        if (navigator.share) {
            await navigator.share({
                title: 'Ma liste de souhaits SEZY',
                text: 'Découvrez mes favoris sur SEZY !',
                url: shareLink.value,
            });
        } else {
            await navigator.clipboard.writeText(shareLink.value);
            copied.value = true;
            toast.success('Lien copié dans le presse-papier !');
            setTimeout(() => { copied.value = false; }, 2000);
        }
    } catch {}
}
</script>

<template>
    <ShopLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
            <div class="flex items-center justify-between mb-1">
                <h1 class="text-2xl font-bold">Ma liste de souhaits</h1>
                <button v-if="products.length" @click="share"
                        class="flex items-center gap-1.5 text-sm text-sezy-dark border border-sezy-dark rounded-xl px-3 py-1.5 hover:bg-sezy-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    {{ copied ? 'Copié !' : 'Partager' }}
                </button>
            </div>
            <p class="text-sm text-gray-400 mb-6">{{ products.length }} article{{ products.length > 1 ? 's' : '' }} sauvegardé{{ products.length > 1 ? 's' : '' }}</p>

            <!-- Profile nav -->
            <nav class="flex gap-4 mb-6 text-sm font-medium border-b overflow-x-auto">
                <Link :href="route('profile.edit')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Profil</Link>
                <Link :href="route('orders.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Mes commandes</Link>
                <Link :href="route('profile.loyalty')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Points fidélité</Link>
                <span class="pb-3 border-b-2 border-sezy-dark text-sezy-dark whitespace-nowrap">Liste de souhaits</span>
                <Link :href="route('adresses.index')" class="pb-3 whitespace-nowrap text-gray-500 hover:text-sezy-dark">Adresses</Link>
            </nav>

            <div v-if="!products.length" class="text-center py-16">
                <p class="text-4xl mb-3">💝</p>
                <p class="font-semibold text-gray-700">Votre liste de souhaits est vide</p>
                <p class="text-sm text-gray-400 mt-1">Sauvegardez vos articles favoris en cliquant sur le cœur.</p>
                <Link :href="route('products.index')" class="btn-primary mt-4 inline-flex">Découvrir la boutique</Link>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <ProductCard v-for="p in products" :key="p.id" :product="p" />
            </div>
        </div>
    </ShopLayout>
</template>
