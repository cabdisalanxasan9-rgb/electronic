<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'id' => 'iphone-16-pro',
                'name' => 'iPhone 16 Pro',
                'category' => 'Phones',
                'price' => 1249,
                'rating' => 4.90,
                'featured' => true,
                'badge' => 'Hot',
                'description' => 'A17 chip, 48MP camera, titanium build iyo battery maalmo qaadata.',
                'image' => '/images/products/iphone-16-pro.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'galaxy-s25-ultra',
                'name' => 'Galaxy S25 Ultra',
                'category' => 'Phones',
                'price' => 1199,
                'rating' => 4.80,
                'featured' => true,
                'badge' => 'AI',
                'description' => 'Zoom camera awood leh, AMOLED 120Hz, iyo AI features wax-soo-saar leh.',
                'image' => '/images/products/galaxy-s25-ultra.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'macbook-pro-m4',
                'name' => 'MacBook Pro M4',
                'category' => 'Laptops',
                'price' => 2299,
                'rating' => 4.95,
                'featured' => true,
                'badge' => 'Pro',
                'description' => 'Performance sare, display aad u nadiif ah, kuna habboon coding iyo editing.',
                'image' => '/images/products/macbook-pro-m4.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'asus-rog-zephyrus',
                'name' => 'ASUS ROG Zephyrus',
                'category' => 'Laptops',
                'price' => 1899,
                'rating' => 4.70,
                'featured' => false,
                'badge' => 'Gaming',
                'description' => 'RTX graphics, cooling fiican, iyo keyboard RGB oo qurux badan.',
                'image' => '/images/products/asus-rog-zephyrus.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'sony-wh1000xm6',
                'name' => 'Sony WH-1000XM6',
                'category' => 'Audio',
                'price' => 449,
                'rating' => 4.90,
                'featured' => true,
                'badge' => 'Noise Cancel',
                'description' => 'Sound premium ah, noise canceling heer sare ah, battery dheer.',
                'image' => '/images/products/sony-wh1000xm6.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'airpods-pro-3',
                'name' => 'AirPods Pro 3',
                'category' => 'Audio',
                'price' => 299,
                'rating' => 4.80,
                'featured' => false,
                'badge' => 'Spatial',
                'description' => 'Spatial audio, transparency mode, iyo fit aad u raaxo badan.',
                'image' => '/images/products/airpods-pro-3.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'lg-oled-evo-77',
                'name' => 'LG OLED Evo 77"',
                'category' => 'TV',
                'price' => 2899,
                'rating' => 4.85,
                'featured' => true,
                'badge' => 'Cinema',
                'description' => '4K OLED contrast qoto dheer leh, Dolby Vision, iyo gaming mode degdeg ah.',
                'image' => '/images/products/lg-oled-evo-77.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'anker-gan-prime',
                'name' => 'Anker GaN Prime 150W',
                'category' => 'Accessories',
                'price' => 129,
                'rating' => 4.65,
                'featured' => false,
                'badge' => 'Fast Charge',
                'description' => 'Charger awood badan oo isku mar ku dallaca laptop, phone, iyo tablet.',
                'image' => '/images/products/anker-gan-prime.jpg',
                'image_path' => null,
            ],
            [
                'id' => 'apple-watch-ultra-3',
                'name' => 'Apple Watch Ultra 3',
                'category' => 'Wearables',
                'price' => 899,
                'rating' => 4.75,
                'featured' => false,
                'badge' => 'New',
                'description' => 'GPS sax ah, battery dheer, iyo health tracking aad u qoto dheer.',
                'image' => '/images/products/apple-watch-ultra-3.jpg',
                'image_path' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(['id' => $product['id']], $product);
        }
    }
}
