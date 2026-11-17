<?php

namespace Database\Seeders;

use App\Models\PressItem;
use Illuminate\Database\Seeder;

class PressItemSeeder extends Seeder
{
    /**
     * Placeholder press coverage from fictional publications. Replace through the admin panel.
     */
    public function run(): void
    {
        $images = [...SeedImages::in('architecture'), ...SeedImages::in('interior')];

        $items = [
            [
                'title' => ['en' => 'Twelve designers, one collective', 'id' => 'Dua belas desainer, satu kolektif'],
                'publication' => 'Sample Design Journal',
                'published_on' => '2026-09-18',
                'excerpt' => [
                    'en' => 'How The Latest Project brings architects, interior designers and product designers together under one name.',
                    'id' => 'Bagaimana The Latest Project menyatukan arsitek, desainer interior, dan desainer produk di bawah satu nama.',
                ],
            ],
            [
                'title' => ['en' => 'Inside Rumah Teduh', 'id' => 'Menengok Rumah Teduh'],
                'publication' => 'Example Architecture Review',
                'published_on' => '2026-07-02',
                'excerpt' => [
                    'en' => 'A house in Canggu that turns shade and breeze into architecture.',
                    'id' => 'Sebuah rumah di Canggu yang mengubah bayangan dan angin menjadi arsitektur.',
                ],
            ],
            [
                'title' => ['en' => 'The new Indonesian interior', 'id' => 'Wajah baru interior Indonesia'],
                'publication' => 'Interior Notes (sample)',
                'published_on' => '2026-04-21',
                'excerpt' => [
                    'en' => 'Natural materials, local craft and a calmer palette: notes on a generation of interior designers.',
                    'id' => 'Material alami, kriya lokal, dan palet yang lebih tenang: catatan tentang satu generasi desainer interior.',
                ],
            ],
            [
                'title' => ['en' => 'Furniture made with Jepara carpenters', 'id' => 'Furnitur bersama tukang kayu Jepara'],
                'publication' => 'Sample Living Magazine',
                'published_on' => '2025-12-09',
                'excerpt' => [
                    'en' => 'Traditional joinery meets quiet contemporary lines in a new furniture series.',
                    'id' => 'Sambungan tradisional bertemu garis kontemporer yang tenang dalam seri furnitur terbaru.',
                ],
            ],
            [
                'title' => ['en' => 'Designing a walkable old town', 'id' => 'Merancang kota lama yang ramah pejalan kaki'],
                'publication' => 'Example Urban Quarterly',
                'published_on' => '2025-08-15',
                'excerpt' => [
                    'en' => 'A revitalisation plan that puts streets and shared green space first.',
                    'id' => 'Rencana revitalisasi yang mengutamakan jalan dan ruang hijau bersama.',
                ],
            ],
            [
                'title' => ['en' => 'Booths built to be reused', 'id' => 'Booth yang dibuat untuk dipakai ulang'],
                'publication' => 'Sample Retail Design Weekly',
                'published_on' => '2025-05-30',
                'excerpt' => [
                    'en' => 'A modular approach to fair booths and window displays.',
                    'id' => 'Pendekatan modular untuk booth pameran dan etalase.',
                ],
            ],
        ];

        foreach ($items as $index => $item) {
            $pressItem = PressItem::updateOrCreate(
                ['publication' => $item['publication']],
                [...$item, 'url' => 'https://example.com/press/'.($index + 1), 'is_published' => true],
            );

            $image = $images[($index * 5 + 3) % max(count($images), 1)] ?? null;

            if ($image !== null && ! $pressItem->hasMedia('image')) {
                $pressItem->addMedia($image)->preservingOriginal()->toMediaCollection('image');
            }
        }
    }
}
