<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Models\Option;
use Illuminate\Support\Facades\Storage;
//use Illuminate\Container\Attributes\Storage;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::deleteDirectory('products');
        Storage::makeDirectory('products');

        // User::factory(10)->create();

        User::firstOrCreate([
            'email' => 'germany0408@gmail.com'
        ], [
            'name' => 'German',
            'email' => 'germany0408@gmail.com',
            'password' => bcrypt('German123456'),
            'role' => \App\Models\User::ROLE_ADMIN,
        ]);

        $this->call([
            FamilySeeder::class,
            OptionSeeder::class,
            AdminUserSeeder::class,
        ]);

        Product::factory(10)->create();

        // Assign random options to products
        // $products = Product::all();
        // $options = \App\Models\Option::all();

        // foreach ($products as $product) {
        //     $randomOptions = $options->random(rand(1, 3)); // Assign 1-3 random options per product
        //     $optionIds = $randomOptions->pluck('id')->toArray();
        //     $product->options()->syncWithoutDetaching(array_fill_keys($optionIds, ['value' => 'default']));
        // }

        // Create variants for products
        // foreach ($products as $product) {
        //     $variantCount = rand(1, 3); // 1-3 variants per product
        //     for ($i = 0; $i < $variantCount; $i++) {
        //         $variant = Variant::factory()->create(['product_id' => $product->id]);

        //         // Assign random features from the product's options
        //         foreach ($product->options as $option) {
        //             $randomFeature = $option->features->random();
        //             $variant->features()->attach($randomFeature->id);
        //         }
        //     }
        // }
    }
}
