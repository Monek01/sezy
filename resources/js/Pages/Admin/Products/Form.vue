<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    product: { type: Object, default: null },
    categories: Array,
    brands: Array,
});

const isEdit = computed(() => !!props.product);

const form = useForm({
    category_id: props.product?.category_id ?? '',
    brand_id: props.product?.brand_id ?? '',
    title: props.product?.title ?? '',
    sku: props.product?.sku ?? '',
    description: props.product?.description ?? '',
    short_description: props.product?.short_description ?? '',
    price: props.product?.price ?? '',
    compare_at_price: props.product?.compare_at_price ?? '',
    cost_price: props.product?.cost_price ?? '',
    stock: props.product?.stock ?? 0,
    low_stock_threshold: props.product?.low_stock_threshold ?? 5,
    track_stock: props.product?.track_stock ?? true,
    weight_kg: props.product?.weight_kg ?? '',
    condition: props.product?.condition ?? '',
    status: props.product?.status ?? 'draft',
    is_featured: props.product?.is_featured ?? false,
    video_url: props.product?.video_url ?? '',
    meta_title: props.product?.meta_title ?? '',
    meta_description: props.product?.meta_description ?? '',
    images: [],
});

function handleFiles(e) {
    form.images = Array.from(e.target.files);
}

function submit() {
    if (isEdit.value) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.products.update', props.product.id));
    } else {
        form.post(route('admin.products.store'));
    }
}
</script>

<template>
    <AdminLayout>
        <Link :href="route('admin.products.index')" class="text-sm text-sezy-dark hover:underline mb-4 inline-block">← Retour aux produits</Link>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}</h1>

        <form @submit.prevent="submit" class="space-y-6 max-w-3xl">
            <div class="card p-5 space-y-4">
                <h2 class="font-bold">Informations générales</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input v-model="form.title" type="text" class="input-field" required />
                    <p v-if="form.errors.title" class="text-xs text-red-600 mt-1">{{ form.errors.title }}</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                        <input v-model="form.sku" type="text" class="input-field" required />
                        <p v-if="form.errors.sku" class="text-xs text-red-600 mt-1">{{ form.errors.sku }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie *</label>
                        <select v-model="form.category_id" class="input-field" required>
                            <option value="" disabled>Choisir...</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                    <select v-model="form.brand_id" class="input-field">
                        <option :value="null">Aucune</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description courte</label>
                    <input v-model="form.short_description" type="text" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description complète</label>
                    <textarea v-model="form.description" class="input-field" rows="6"></textarea>
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <h2 class="font-bold">Prix & Stock</h2>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (FCFA) *</label>
                        <input v-model="form.price" type="number" step="1" class="input-field" required />
                        <p v-if="form.errors.price" class="text-xs text-red-600 mt-1">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix barré</label>
                        <input v-model="form.compare_at_price" type="number" step="1" class="input-field" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat</label>
                        <input v-model="form.cost_price" type="number" step="1" class="input-field" />
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                        <input v-model="form.stock" type="number" class="input-field" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seuil stock critique</label>
                        <input v-model="form.low_stock_threshold" type="number" class="input-field" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.track_stock" class="rounded text-sezy" /> Suivre le stock
                </label>
            </div>

            <div class="card p-5 space-y-4">
                <h2 class="font-bold">État / Condition (vêtements)</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condition de l'article</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition text-sm font-semibold"
                               :class="form.condition === '' ? 'border-gray-200 text-gray-500' : ''">
                            <input type="radio" value="" v-model="form.condition" class="sr-only" /> Non applicable
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition text-sm font-semibold"
                               :class="form.condition === 'neuf' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-blue-200'">
                            <input type="radio" value="neuf" v-model="form.condition" class="sr-only" /> 🆕 Neuf
                        </label>
                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition text-sm font-semibold"
                               :class="form.condition === 'fripe' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-600 hover:border-emerald-200'">
                            <input type="radio" value="fripe" v-model="form.condition" class="sr-only" /> ♻️ Fripe / Seconde main
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Pour les vêtements : précisez si l'article est neuf ou de seconde main (fripe).</p>
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <h2 class="font-bold">Images</h2>
                <input type="file" multiple accept="image/*" @change="handleFiles" class="block text-sm" />
                <div v-if="product?.images?.length" class="flex gap-2 flex-wrap mt-3">
                    <img v-for="img in product.images" :key="img.id" :src="`/storage/${img.path}`"
                         class="w-20 h-20 rounded-lg object-cover"
                         @error="$event.target.src='/images/placeholder-product.svg'" />
                </div>
            </div>

            <div class="card p-5 space-y-4">
                <h2 class="font-bold">Publication & SEO</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select v-model="form.status" class="input-field">
                            <option value="draft">Brouillon</option>
                            <option value="published">Publié</option>
                            <option value="archived">Archivé</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm mt-7">
                        <input type="checkbox" v-model="form.is_featured" class="rounded text-sezy" /> Produit mis en avant
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta titre (SEO)</label>
                    <input v-model="form.meta_title" type="text" class="input-field" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta description (SEO)</label>
                    <textarea v-model="form.meta_description" class="input-field" rows="2"></textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary" :disabled="form.processing">
                    {{ isEdit ? 'Mettre à jour' : 'Créer le produit' }}
                </button>
                <Link :href="route('admin.products.index')" class="btn-secondary">Annuler</Link>
            </div>
        </form>
    </AdminLayout>
</template>
