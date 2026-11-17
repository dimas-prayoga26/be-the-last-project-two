<?php

namespace Database\Seeders;

use App\Enums\Discipline;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The sub-menus from the client's header brief.
     */
    public function run(): void
    {
        $categories = [
            Discipline::Architecture->value => [
                ['commercial', 'Commercial', 'Komersial'],
                ['residential', 'Residential', 'Residensial'],
                ['hospitality', 'Hospitality', 'Hospitality'],
                ['landscape', 'Landscape', 'Lanskap'],
                ['urban-planning', 'Urban Planning', 'Perencanaan Kota'],
            ],
            Discipline::Interior->value => [
                ['commercial', 'Commercial', 'Komersial'],
                ['residential', 'Residential', 'Residensial'],
                ['hospitality', 'Hospitality', 'Hospitality'],
            ],
            Discipline::Design->value => [
                ['furniture', 'Furniture', 'Furnitur'],
                ['product', 'Product', 'Produk'],
                ['visual-merch', 'Visual Merch', 'Visual Merch'],
            ],
        ];

        foreach ($categories as $discipline => $items) {
            foreach ($items as $position => [$slug, $english, $indonesian]) {
                Category::updateOrCreate(
                    ['discipline' => $discipline, 'slug' => $slug],
                    ['name' => ['en' => $english, 'id' => $indonesian], 'sort_order' => $position],
                );
            }
        }
    }
}
