<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Sample catalog for manual testing, including one inactive and one out-of-stock product.
     */
    public function run(): void
    {
        $products = [
            ['code' => '7891000100103', 'name' => 'Leite Integral 1L', 'price_cents' => 549, 'stock' => 120],
            ['code' => '7896005800010', 'name' => 'Arroz Branco Tipo 1 5kg', 'price_cents' => 2899, 'stock' => 40],
            ['code' => '7896006700012', 'name' => 'Arroz Integral 1kg', 'price_cents' => 899, 'stock' => 35],
            ['code' => '7896102500017', 'name' => 'Feijão Carioca 1kg', 'price_cents' => 789, 'stock' => 60],
            ['code' => '7896102500024', 'name' => 'Feijão Preto 1kg', 'price_cents' => 849, 'stock' => 45],
            ['code' => '7891910000197', 'name' => 'Açúcar Refinado 1kg', 'price_cents' => 479, 'stock' => 80],
            ['code' => '7896089011983', 'name' => 'Café Torrado e Moído 500g', 'price_cents' => 1890, 'stock' => 50],
            ['code' => '7891107101621', 'name' => 'Óleo de Soja 900ml', 'price_cents' => 699, 'stock' => 70],
            ['code' => '7896036090244', 'name' => 'Macarrão Espaguete 500g', 'price_cents' => 429, 'stock' => 90],
            ['code' => '7891150013506', 'name' => 'Molho de Tomate 340g', 'price_cents' => 329, 'stock' => 100],
            ['code' => '7891000053508', 'name' => 'Biscoito Recheado Chocolate 140g', 'price_cents' => 299, 'stock' => 150],
            ['code' => '7894900011517', 'name' => 'Refrigerante Cola 2L', 'price_cents' => 1099, 'stock' => 60],
            ['code' => '7891149101900', 'name' => 'Cerveja Lata 350ml', 'price_cents' => 449, 'stock' => 200],
            ['code' => '7896004000015', 'name' => 'Água Mineral sem Gás 500ml', 'price_cents' => 249, 'stock' => 300],
            ['code' => '7891024134702', 'name' => 'Sabonete em Barra 85g', 'price_cents' => 279, 'stock' => 110],
            ['code' => '7891035800207', 'name' => 'Detergente Líquido 500ml', 'price_cents' => 289, 'stock' => 95],
            ['code' => '7896098900123', 'name' => 'Papel Higiênico Folha Dupla 12un', 'price_cents' => 2290, 'stock' => 30],
            ['code' => '7891000315507', 'name' => 'Chocolate ao Leite 90g', 'price_cents' => 699, 'stock' => 75],
            ['code' => '7896045505012', 'name' => 'Pão de Forma Tradicional 500g', 'price_cents' => 899, 'stock' => 25],
            ['code' => '7891515901011', 'name' => 'Manteiga com Sal 200g', 'price_cents' => 1249, 'stock' => 40],
            ['code' => '7896000000016', 'name' => 'Iogurte Natural 170g (fora de linha)', 'price_cents' => 359, 'stock' => 20, 'is_active' => false],
            ['code' => '7896000000023', 'name' => 'Queijo Mussarela Fatiado 150g', 'price_cents' => 1199, 'stock' => 0],
        ];

        foreach ($products as $product) {
            Product::factory()->create($product);
        }
    }
}
