<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
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

        User::factory()->create([
            'name' => 'German',
            'email' => 'germany0408@gmail.com',
            'password' => bcrypt('German123456')
        ]);

        $this->call([
            FamilySeeder::class,
            OptionSeeder::class,
        ]);

        Product::factory(150)->create();
    }
}
