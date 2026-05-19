<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // CUIDADO DEL CABELLO Y BARBA
            ['name' => 'Cera Mate Pomade', 'desc' => 'Fijación fuerte, acabado sin brillo', 'cost' => 8.00, 'price' => 15.00, 'm' => '100', 'u' => 'gr', 'stock' => 20, 'category' => 'cuidado del cabello', 'brand' => 'BarberBrand', 'is_active' => true, 'photo' => 'https://kokoro.mx/cdn/shop/files/817891025254_CERA_UPPERCUT_MATTE_POMADE_100g_PRODUCTO_1024.jpg'],
            ['name' => 'Gel Fijador Extrafuerte', 'desc' => 'Efecto húmedo de larga duración', 'cost' => 4.00, 'price' => 8.50, 'm' => '600', 'u' => 'g', 'stock' => 15, 'category' => 'cuidado del cabello', 'brand' => 'BarberBrand', 'is_active' => true, 'photo' => 'https://http2.mlstatic.com/D_NQ_NP_2X_964641-MLA92605780841_092025-F.webp'],
            ['name' => 'Aceite para Barba', 'desc' => 'Hidrata y suaviza la barba', 'cost' => 5.00, 'price' => 12.00, 'm' => '50', 'u' => 'ml', 'stock' => 25, 'category' => 'cuidado de la barba', 'brand' => 'BarberBrand', 'is_active' => true, 'photo' => 'https://i5-mx.walmartimages.com/gr/images/product-images/img_large/00750043521624L.jpg'],
            ['name' => 'Bálsamo para Barba', 'desc' => 'Controla el frizz y da forma', 'cost' => 6.00, 'price' => 14.00, 'm' => '60', 'u' => 'gr', 'stock' => 10, 'category' => 'cuidado de la barba', 'brand' => 'BarberBrand', 'is_active' => true, 'photo' => 'https://tse3.mm.bing.net/th/id/OIP.S9Hu5z7IitZhW3Wi26x-ewHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Agua Mineral', 'desc' => 'Sin gas, bien helada', 'cost' => 0.40, 'price' => 1.00, 'm' => '500', 'u' => 'ml', 'stock' => 2, 'category' => 'bebidas', 'brand' => 'BarberBrand', 'is_active' => true, 'photo' => 'https://farmaenlace.vtexassets.com/arquivos/ids/159995/16223-1.jpg'],
            ['name' => 'Coca-Cola Clásica', 'desc' => 'Bebida gaseosa', 'cost' => 0.60, 'price' => 1.50, 'm' => '350', 'u' => 'ml', 'stock' => 0, 'category' => 'bebidas', 'brand' => 'Coca Cola', 'is_active' => true, 'photo' => 'https://tse4.mm.bing.net/th/id/OIP.x3pQnfmwudoKW0iIohTAdQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'],
            ['name' => 'Coca-Cola Zero', 'desc' => 'Bebida gaseosa sin azúcar', 'cost' => 0.60, 'price' => 1.50, 'm' => '350', 'u' => 'ml', 'stock' => 10, 'category' => 'bebidas', 'brand' => 'Coca Cola', 'is_active' => false, 'photo' => 'https://beverageuniverse.com/media/catalog/product/cache/4ff36951dc53d670a51daea8303fac58/c/o/coca-cola-oxxi-ckz12-main.jpg'],

        ];

        foreach ($products as $p) {
            Product::create([
                'name'        => $p['name'],
                'description' => $p['desc'],
                'cost'        => $p['cost'],
                'price'       => $p['price'],
                'measure'     => $p['m'],
                'unit'        => $p['u'],
                'photo'       => $p['photo'],
                'stock'       => $p['stock'],
                'category'    => $p['category'],
                'brand'         => $p['brand'],
                'is_active'     => $p['is_active']
            ]);
        }
    }
}