<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ----------------------------------------------------------------
        // Comptes administrateurs (§5.1)
        // ----------------------------------------------------------------
        User::firstOrCreate(['email' => 'admin@sezy.bj'], [
            'first_name' => 'Admin',
            'last_name' => 'SEZY',
            'phone' => '+22900000001',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        User::firstOrCreate(['email' => 'client@sezy.bj'], [
            'first_name' => 'Client',
            'last_name' => 'Démo',
            'phone' => '+22900000002',
            'password' => Hash::make('password'),
            'role' => 'client',
            'email_verified_at' => now(),
            'loyalty_points' => 2500,
            'loyalty_tier' => 'silver',
        ]);

        // ----------------------------------------------------------------
        // Catégories (§1: cosmétiques, mode, alimentation)
        // ----------------------------------------------------------------
        $mode = Category::create(['name' => 'Mode', 'slug' => 'mode', 'position' => 1, 'is_active' => true]);
        Category::create(['name' => 'Hommes', 'slug' => 'mode-hommes', 'parent_id' => $mode->id, 'position' => 1, 'is_active' => true]);
        Category::create(['name' => 'Femmes', 'slug' => 'mode-femmes', 'parent_id' => $mode->id, 'position' => 2, 'is_active' => true]);

        $cosmetiques = Category::create(['name' => 'Cosmétiques', 'slug' => 'cosmetiques', 'position' => 2, 'is_active' => true]);
        Category::create(['name' => 'Soin du visage', 'slug' => 'soin-visage', 'parent_id' => $cosmetiques->id, 'position' => 1, 'is_active' => true]);
        Category::create(['name' => 'Maquillage', 'slug' => 'maquillage', 'parent_id' => $cosmetiques->id, 'position' => 2, 'is_active' => true]);

        $alimentation = Category::create(['name' => 'Alimentation', 'slug' => 'alimentation', 'position' => 3, 'is_active' => true]);
        Category::create(['name' => 'Épicerie', 'slug' => 'epicerie', 'parent_id' => $alimentation->id, 'position' => 1, 'is_active' => true]);

        $electronique = Category::create(['name' => 'Électronique', 'slug' => 'electronique', 'position' => 4, 'is_active' => true]);

        // ----------------------------------------------------------------
        // Marques
        // ----------------------------------------------------------------
        $brandSezy = Brand::create(['name' => 'SEZY Originals', 'slug' => 'sezy-originals']);
        $brandGen = Brand::create(['name' => 'Générique', 'slug' => 'generique']);

        // ----------------------------------------------------------------
        // Attributs (Couleur, Taille) pour les variantes (§4.2)
        // ----------------------------------------------------------------
        $colorAttr = Attribute::create(['name' => 'Couleur', 'slug' => 'couleur']);
        $colorRed = AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => 'Rouge', 'hex_color' => '#dc2626']);
        $colorBlue = AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => 'Bleu', 'hex_color' => '#3180d0']);
        $colorBlack = AttributeValue::create(['attribute_id' => $colorAttr->id, 'value' => 'Noir', 'hex_color' => '#111827']);

        $sizeAttr = Attribute::create(['name' => 'Taille', 'slug' => 'taille']);
        $sizeS = AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'S']);
        $sizeM = AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'M']);
        $sizeL = AttributeValue::create(['attribute_id' => $sizeAttr->id, 'value' => 'L']);

        // ----------------------------------------------------------------
        // Produits de démonstration
        // ----------------------------------------------------------------
        $demoProducts = [
            ['title' => 'T-shirt coton premium', 'category_id' => $mode->id, 'price' => 8500, 'compare_at_price' => 11000, 'featured' => true, 'variants' => true],
            ['title' => 'Robe wax élégance', 'category_id' => $mode->id, 'price' => 25000, 'featured' => true, 'variants' => true],
            ['title' => 'Crème hydratante karité bio', 'category_id' => $cosmetiques->id, 'price' => 6000, 'featured' => true],
            ['title' => 'Palette maquillage 12 couleurs', 'category_id' => $cosmetiques->id, 'price' => 12500],
            ['title' => 'Riz parfumé local 5kg', 'category_id' => $alimentation->id, 'price' => 4500],
            ['title' => 'Huile de palme rouge 1L', 'category_id' => $alimentation->id, 'price' => 2000],
            ['title' => 'Écouteurs sans fil Bluetooth', 'category_id' => $electronique->id, 'price' => 15000, 'compare_at_price' => 19000, 'featured' => true],
            ['title' => 'Powerbank 20000mAh', 'category_id' => $electronique->id, 'price' => 18000],
            ['title' => 'Sneakers urbaines unisexe', 'category_id' => $mode->id, 'price' => 22000, 'variants' => true],
            ['title' => 'Sérum vitamine C', 'category_id' => $cosmetiques->id, 'price' => 9500],
            ['title' => 'Pagne wax 6 yards', 'category_id' => $mode->id, 'price' => 17500],
            ['title' => 'Chocolat artisanal béninois', 'category_id' => $alimentation->id, 'price' => 3500],
        ];

        foreach ($demoProducts as $index => $data) {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'brand_id' => $index % 2 === 0 ? $brandSezy->id : $brandGen->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']).'-'.Str::random(5),
                'sku' => 'SEZY-'.strtoupper(Str::random(8)),
                'description' => "<p>{$data['title']} — produit soigneusement sélectionné pour la clientèle SEZY au Bénin et dans la diaspora.</p>",
                'short_description' => 'Qualité garantie, livraison rapide.',
                'price' => $data['price'],
                'compare_at_price' => $data['compare_at_price'] ?? null,
                'stock' => rand(0, 40),
                'low_stock_threshold' => 5,
                'status' => 'published',
                'is_featured' => $data['featured'] ?? false,
                'published_at' => now(),
                'average_rating' => round(rand(35, 50) / 10, 1),
                'reviews_count' => rand(0, 24),
                'views_count' => rand(10, 500),
            ]);

            if ($data['variants'] ?? false) {
                foreach ([$colorRed, $colorBlue, $colorBlack] as $color) {
                    foreach ([$sizeS, $sizeM, $sizeL] as $size) {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $product->sku.'-'.$color->value.'-'.$size->value,
                            'stock' => rand(0, 15),
                        ]);
                        $variant->attributeValues()->attach([$color->id, $size->id]);
                    }
                }
            }
        }

        // ----------------------------------------------------------------
        // Points de retrait Click & Collect (§3.5)
        // ----------------------------------------------------------------
        PickupPoint::create([
            'name' => 'SEZY Store Cotonou Centre',
            'city' => 'Cotonou',
            'address' => 'Avenue Steinmetz, Cotonou',
            'latitude' => 6.3654,
            'longitude' => 2.4183,
            'phone' => '+22900000010',
            'opening_hours' => 'Lun-Sam 9h-19h',
            'retrieval_delay_days' => 7,
        ]);

        PickupPoint::create([
            'name' => 'SEZY Store Porto-Novo',
            'city' => 'Porto-Novo',
            'address' => 'Quartier Houinmè, Porto-Novo',
            'latitude' => 6.4969,
            'longitude' => 2.6289,
            'opening_hours' => 'Lun-Sam 9h-18h',
            'retrieval_delay_days' => 7,
        ]);

        $this->call(CouponSeeder::class);
        $this->call(RealProductSeeder::class);
    }
}
