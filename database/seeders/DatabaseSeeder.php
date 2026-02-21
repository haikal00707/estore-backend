<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Categories
        $electronics = \App\Models\Category::create(['name' => 'Electronics']);
        $accessories = \App\Models\Category::create(['name' => 'Accessories']);
        $apparel = \App\Models\Category::create(['name' => 'Apparel']);

        // 2. Admin User
        User::create([
            'name' => 'Admin Store',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
        ]);

        // 3. Regular User
        User::create([
            'name' => 'Buyer Store',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // 4. Initial Products
        \App\Models\Product::create([
            'name' => 'RTX 5050 8GB VENTUS',
            'price' => 8000000,
            'description' => 'High performance graphics card for gaming.',
            'stock' => 10,
            'category_id' => $electronics->id,
            'image' => 'https://asset.msi.com/resize/image/global/product/product_17507572669b07fa60658ab594055e169d6d6fa74f.png62405b38c58fe0f07fcef2367d8a9ba1/1024.png'
        ]);

        \App\Models\Product::create([
            'name' => 'Logitech G Pro X',
            'price' => 1500000,
            'description' => 'Mechanical gaming keyboard.',
            'stock' => 15,
            'category_id' => $accessories->id,
            'image' => 'https://resource.logitechg.com/w_692,c_limit,q_auto,f_auto,dpr_1.0/d_transparent.gif/content/dam/gaming/en/products/pro-x-keyboard/pro-x-keyboard-gallery-1.png?v=1'
        ]);
    }
}
