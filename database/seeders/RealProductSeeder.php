<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create relevant categories
        $nutrition = Category::firstOrCreate(
            ['slug' => 'nutrition-sante'],
            ['name' => 'Nutrition & Santé', 'position' => 5, 'is_active' => true]
        );

        $cosmetiques = Category::where('slug', 'cosmetiques')->first()
            ?? Category::firstOrCreate(
                ['slug' => 'cosmetiques'],
                ['name' => 'Cosmétiques', 'position' => 2, 'is_active' => true]
            );

        $parfums = Category::firstOrCreate(
            ['slug' => 'parfums'],
            ['name' => 'Parfums', 'position' => 6, 'is_active' => true, 'parent_id' => $cosmetiques->id]
        );

        $soinVisage = Category::where('slug', 'soin-visage')->first()
            ?? Category::firstOrCreate(
                ['slug' => 'soin-visage'],
                ['name' => 'Soin du visage', 'position' => 1, 'is_active' => true, 'parent_id' => $cosmetiques->id]
            );

        $sezyBrand = Brand::where('slug', 'sezy-originals')->first()
            ?? Brand::create(['name' => 'SEZY Originals', 'slug' => 'sezy-originals']);

        $chogan = Brand::firstOrCreate(
            ['slug' => 'chogan'],
            ['name' => 'Chogan']
        );

        $theOrdinary = Brand::firstOrCreate(
            ['slug' => 'the-ordinary'],
            ['name' => 'The Ordinary']
        );

        $products = [
            [
                'title'             => 'Collagène Marin Premium',
                'slug'              => 'collagene-marin-premium',
                'sku'               => 'SEZY-COLLMAR-001',
                'category_id'       => $nutrition->id,
                'brand_id'          => $sezyBrand->id,
                'price'             => 30000,
                'stock'             => 25,
                'featured'          => true,
                'description'       => '<p>Le <strong>Collagène Marin Premium SEZY</strong> est issu de poissons marins soigneusement sélectionnés. Riche en peptides de collagène de type I et III, il favorise l\'élasticité de la peau, la santé des articulations et des cheveux.</p><ul><li>Haute biodisponibilité</li><li>Sans arôme artificiel</li><li>Produit importé depuis la France</li></ul>',
                'short_description' => 'Collagène marin hydrolysé · Peau, cheveux, articulations · Importé de France',
                'image'             => '/images/products/collagene-marin.svg',
            ],
            [
                'title'             => 'The Ordinary Niacinamide 10% + Zinc 1%',
                'slug'              => 'the-ordinary-niacinamide-10-zinc',
                'sku'               => 'SEZY-NIAC-001',
                'category_id'       => $soinVisage->id,
                'brand_id'          => $theOrdinary->id,
                'price'             => 15000,
                'stock'             => 40,
                'featured'          => true,
                'description'       => '<p>Le sérum <strong>Niacinamide 10% + Zinc 1%</strong> de The Ordinary est un soin incontournable pour réduire les pores dilatés, réguler le sébum et uniformiser le teint. Formule vegan, sans silicone.</p>',
                'short_description' => 'Réduit les pores · Régule le sébum · Éclat unifié · 30ml',
                'image'             => '/images/products/niacinamide.svg',
            ],
            [
                'title'             => 'Sérum Vitamine C Éclat 20%',
                'slug'              => 'serum-vitamine-c-eclat-20',
                'sku'               => 'SEZY-VITC-001',
                'category_id'       => $soinVisage->id,
                'brand_id'          => $sezyBrand->id,
                'price'             => 15000,
                'stock'             => 35,
                'featured'          => true,
                'description'       => '<p>Notre <strong>Sérum Vitamine C 20%</strong> est formulé avec de l\'acide L-ascorbique pur pour illuminer le teint, atténuer les taches brunes et stimuler la production de collagène. Résultats visibles en 4 semaines.</p>',
                'short_description' => 'Illumine le teint · Anti-taches · Stimule le collagène · 30ml',
                'image'             => '/images/products/vitamine-c.svg',
            ],
            [
                'title'             => 'Zinc 25mg · 60 Gélules',
                'slug'              => 'zinc-25mg-60-gelules',
                'sku'               => 'SEZY-ZINC-001',
                'category_id'       => $nutrition->id,
                'brand_id'          => $sezyBrand->id,
                'price'             => 15000,
                'stock'             => 50,
                'featured'          => false,
                'description'       => '<p>Le <strong>Zinc 25mg</strong> est un oligoélément essentiel qui soutient le système immunitaire, favorise la santé de la peau et des ongles, et contribue à la synthèse des protéines. Importé de France.</p>',
                'short_description' => 'Système immunitaire · Peau & ongles · 60 gélules · Importé',
                'image'             => '/images/products/zinc.svg',
            ],
            [
                'title'             => 'Baies de Goji Séchées Bio 200g',
                'slug'              => 'baies-de-goji-sechees-bio-200g',
                'sku'               => 'SEZY-GOJI-001',
                'category_id'       => $nutrition->id,
                'brand_id'          => $sezyBrand->id,
                'price'             => 15000,
                'stock'             => 45,
                'featured'          => false,
                'description'       => '<p>Les <strong>Baies de Goji Séchées</strong> SEZY sont riches en antioxydants, vitamines C et A, et en zinc naturel. Superfood idéal pour booster l\'énergie et renforcer l\'immunité.</p><ul><li>Certifiées biologiques</li><li>Sans sucre ajouté</li><li>Importées depuis la France</li></ul>',
                'short_description' => 'Superfood · Antioxydants · Bio · 200g · Importé de France',
                'image'             => '/images/products/baies-de-goji.svg',
            ],
            [
                'title'             => 'Parfum Chogan 30ml',
                'slug'              => 'parfum-chogan-30ml',
                'sku'               => 'SEZY-CHO30-001',
                'category_id'       => $parfums->id,
                'brand_id'          => $chogan->id,
                'price'             => 15000,
                'stock'             => 20,
                'featured'          => true,
                'description'       => '<p>Les <strong>Parfums Chogan</strong> sont des fragrances de prestige inspirées des plus grandes maisons de parfumerie. La version <strong>30ml</strong> est idéale pour voyager et découvrir la gamme. Concentration EDP (Eau de Parfum).</p>',
                'short_description' => 'Fragrance de prestige · EDP · 30ml · Inspiré grandes maisons',
                'image'             => '/images/products/chogan-30ml.svg',
            ],
            [
                'title'             => 'Parfum Chogan 70ml',
                'slug'              => 'parfum-chogan-70ml',
                'sku'               => 'SEZY-CHO70-001',
                'category_id'       => $parfums->id,
                'brand_id'          => $chogan->id,
                'price'             => 30000,
                'stock'             => 15,
                'featured'          => true,
                'description'       => '<p>Les <strong>Parfums Chogan</strong> sont des fragrances de prestige inspirées des plus grandes maisons de parfumerie. Le format <strong>70ml</strong> offre une tenue prolongée et une projection envoûtante. Concentration EDP (Eau de Parfum).</p>',
                'short_description' => 'Fragrance de prestige · EDP · 70ml · Tenue longue durée',
                'image'             => '/images/products/chogan-70ml.svg',
            ],
        ];

        foreach ($products as $data) {
            // Skip if already exists
            if (Product::where('slug', $data['slug'])->exists()) {
                continue;
            }

            $imagePath = $data['image'];
            unset($data['image'], $data['featured']);

            $product = Product::create([
                ...$data,
                'status'          => 'published',
                'is_featured'     => true,
                'published_at'    => now(),
                'low_stock_threshold' => 5,
                'average_rating'  => round(rand(40, 50) / 10, 1),
                'reviews_count'   => rand(3, 28),
                'views_count'     => rand(50, 400),
                'condition'       => 'neuf',
            ]);

            // Create product image record pointing to SVG
            ProductImage::create([
                'product_id' => $product->id,
                'path'       => ltrim($imagePath, '/'),
                'position'   => 0,
                'is_primary'  => true,
            ]);
        }
    }
}
