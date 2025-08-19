<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Electronics
            [
                'kode' => 'ELC001',
                'nama' => 'Laptop ASUS VivoBook',
                'kategori' => 'Electronics',
                'stok' => 15,
                'harga_beli' => 8500000,
                'harga_jual' => 10500000,
            ],
            [
                'kode' => 'ELC002',
                'nama' => 'Smartphone Samsung Galaxy A54',
                'kategori' => 'Electronics',
                'stok' => 25,
                'harga_beli' => 4500000,
                'harga_jual' => 5500000,
            ],
            [
                'kode' => 'ELC003',
                'nama' => 'Headphone Sony WH-1000XM4',
                'kategori' => 'Electronics',
                'stok' => 30,
                'harga_beli' => 3500000,
                'harga_jual' => 4200000,
            ],
            [
                'kode' => 'ELC004',
                'nama' => 'Mouse Logitech MX Master 3',
                'kategori' => 'Electronics',
                'stok' => 50,
                'harga_beli' => 850000,
                'harga_jual' => 1200000,
            ],
            [
                'kode' => 'ELC005',
                'nama' => 'Keyboard Mechanical Keychron K2',
                'kategori' => 'Electronics',
                'stok' => 20,
                'harga_beli' => 1200000,
                'harga_jual' => 1600000,
            ],
            
            // Fashion
            [
                'kode' => 'FSH001',
                'nama' => 'Kemeja Formal Pria',
                'kategori' => 'Fashion',
                'stok' => 40,
                'harga_beli' => 150000,
                'harga_jual' => 250000,
            ],
            [
                'kode' => 'FSH002',
                'nama' => 'Dress Casual Wanita',
                'kategori' => 'Fashion',
                'stok' => 35,
                'harga_beli' => 200000,
                'harga_jual' => 350000,
            ],
            [
                'kode' => 'FSH003',
                'nama' => 'Sepatu Sneakers Nike Air Force 1',
                'kategori' => 'Fashion',
                'stok' => 25,
                'harga_beli' => 1200000,
                'harga_jual' => 1800000,
            ],
            [
                'kode' => 'FSH004',
                'nama' => 'Tas Ransel Eiger',
                'kategori' => 'Fashion',
                'stok' => 30,
                'harga_beli' => 300000,
                'harga_jual' => 450000,
            ],
            [
                'kode' => 'FSH005',
                'nama' => 'Jam Tangan Casio G-Shock',
                'kategori' => 'Fashion',
                'stok' => 15,
                'harga_beli' => 2500000,
                'harga_jual' => 3200000,
            ],
            
            // Food & Beverage
            [
                'kode' => 'FNB001',
                'nama' => 'Kopi Arabica Premium 250g',
                'kategori' => 'Food & Beverage',
                'stok' => 100,
                'harga_beli' => 75000,
                'harga_jual' => 120000,
            ],
            [
                'kode' => 'FNB002',
                'nama' => 'Teh Earl Grey 50 Tea Bags',
                'kategori' => 'Food & Beverage',
                'stok' => 80,
                'harga_beli' => 45000,
                'harga_jual' => 75000,
            ],
            [
                'kode' => 'FNB003',
                'nama' => 'Cokelat Dark 70% Lindt',
                'kategori' => 'Food & Beverage',
                'stok' => 60,
                'harga_beli' => 85000,
                'harga_jual' => 135000,
            ],
            [
                'kode' => 'FNB004',
                'nama' => 'Madu Murni 500ml',
                'kategori' => 'Food & Beverage',
                'stok' => 45,
                'harga_beli' => 125000,
                'harga_jual' => 200000,
            ],
            [
                'kode' => 'FNB005',
                'nama' => 'Biskuit Oat Cookies',
                'kategori' => 'Food & Beverage',
                'stok' => 70,
                'harga_beli' => 25000,
                'harga_jual' => 45000,
            ],
            
            // Home & Living
            [
                'kode' => 'HML001',
                'nama' => 'Lampu LED 12W Philips',
                'kategori' => 'Home & Living',
                'stok' => 100,
                'harga_beli' => 35000,
                'harga_jual' => 55000,
            ],
            [
                'kode' => 'HML002',
                'nama' => 'Vas Bunga Keramik',
                'kategori' => 'Home & Living',
                'stok' => 25,
                'harga_beli' => 150000,
                'harga_jual' => 250000,
            ],
            [
                'kode' => 'HML003',
                'nama' => 'Bantal Sofa 40x40cm',
                'kategori' => 'Home & Living',
                'stok' => 50,
                'harga_beli' => 75000,
                'harga_jual' => 125000,
            ],
            [
                'kode' => 'HML004',
                'nama' => 'Cermin Dinding Bulat',
                'kategori' => 'Home & Living',
                'stok' => 20,
                'harga_beli' => 200000,
                'harga_jual' => 350000,
            ],
            [
                'kode' => 'HML005',
                'nama' => 'Rak Buku Minimalis',
                'kategori' => 'Home & Living',
                'stok' => 15,
                'harga_beli' => 450000,
                'harga_jual' => 650000,
            ],
        ];
        
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
