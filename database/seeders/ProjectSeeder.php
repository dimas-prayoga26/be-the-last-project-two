<?php

namespace Database\Seeders;

use App\Enums\Discipline;
use App\Models\Category;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Placeholder works spread over every category, using the local seed images.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy(fn (Category $category): string => "{$category->discipline->value}/{$category->slug}");
        $designers = Designer::all()->keyBy('slug');
        $positions = [];

        foreach ($this->projects() as $index => $data) {
            [$discipline, $categorySlug, $titleEn, $titleId, $location, $year, $area, $client, $designerSlugs, $featured] = $data;

            $variant = $index % 2;
            $texts = $this->texts(Discipline::from($discipline), $variant);
            $replace = fn (string $text, string $title): string => strtr($text, ['{title}' => $title, '{location}' => Str::before($location, ',')]);

            $project = Project::updateOrCreate(
                ['slug' => Str::slug($titleEn)],
                [
                    'category_id' => $categories["{$discipline}/{$categorySlug}"]->id,
                    'title' => ['en' => $titleEn, 'id' => $titleId],
                    'summary' => ['en' => $replace($texts['summary']['en'], $titleEn), 'id' => $replace($texts['summary']['id'], $titleId)],
                    'description' => ['en' => $replace($texts['description']['en'], $titleEn), 'id' => $replace($texts['description']['id'], $titleId)],
                    'location' => $location,
                    'year' => $year,
                    'area' => $area,
                    'client' => $client,
                    'is_featured' => $featured,
                    // Interleave publish dates so the newest works mix all disciplines.
                    'published_at' => now()->subDays((($index * 7) % 31) * 4 + 1)->startOfHour(),
                    'sort_order' => 0,
                ],
            );

            $project->designers()->sync(collect($designerSlugs)->map(fn (string $slug): int => $designers[$slug]->id)->all());

            $this->attachImages($project, $discipline, $positions[$discipline] = ($positions[$discipline] ?? -1) + 1);
        }
    }

    private function attachImages(Project $project, string $discipline, int $position): void
    {
        if ($project->hasMedia('cover')) {
            return;
        }

        $pool = SeedImages::in($discipline);

        if ($pool === []) {
            return;
        }

        $galleryCount = $discipline === Discipline::Design->value ? 4 : 6;
        $start = $position * 2;

        $project->addMedia($pool[$start % count($pool)])->preservingOriginal()->toMediaCollection('cover');

        foreach (SeedImages::pick($pool, $start + 1, $galleryCount) as $image) {
            $project->addMedia($image)->preservingOriginal()->toMediaCollection('gallery');
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: int, 6: ?string, 7: ?string, 8: list<string>, 9: bool}>
     */
    private function projects(): array
    {
        return [
            ['architecture', 'commercial', 'Kanopi Office Tower', 'Menara Kantor Kanopi', 'Jakarta, Indonesia', 2024, '12,500 m²', 'PT Kanopi Group', ['adhitya-pranata', 'fajar-nugroho'], true],
            ['architecture', 'commercial', 'Lumen Retail Pavilion', 'Paviliun Ritel Lumen', 'Surabaya, Indonesia', 2022, '2,400 m²', null, ['fajar-nugroho'], false],
            ['architecture', 'commercial', 'Arcade Workspace', 'Ruang Kerja Arcade', 'Bandung, Indonesia', 2021, '3,100 m²', null, ['adhitya-pranata'], false],
            ['architecture', 'residential', 'Rumah Teduh', 'Rumah Teduh', 'Canggu, Bali', 2023, '420 m²', 'Private client', ['raka-wibisono'], true],
            ['architecture', 'residential', 'Courtyard House', 'Rumah Berhalaman', 'Yogyakarta, Indonesia', 2022, '310 m²', 'Private client', ['raka-wibisono', 'laras-setyaningrum'], false],
            ['architecture', 'residential', 'Slatted House', 'Rumah Kisi', 'South Jakarta, Indonesia', 2025, '360 m²', 'Private client', ['adhitya-pranata'], false],
            ['architecture', 'residential', 'Dusk Residence', 'Hunian Senja', 'Semarang, Indonesia', 2020, '280 m²', 'Private client', ['fajar-nugroho'], false],
            ['architecture', 'hospitality', 'Sawah Retreat', 'Sawah Retreat', 'Ubud, Bali', 2024, '1,800 m²', null, ['raka-wibisono', 'laras-setyaningrum'], true],
            ['architecture', 'hospitality', 'Tepi Laut Hotel', 'Hotel Tepi Laut', 'Lombok, Indonesia', 2023, '6,200 m²', null, ['adhitya-pranata', 'ayu-ratnasari'], false],
            ['architecture', 'landscape', 'Garden of Terraces', 'Taman Berundak', 'Bandung, Indonesia', 2022, '5,000 m²', null, ['laras-setyaningrum'], false],
            ['architecture', 'landscape', 'Riverside Park Pavilion', 'Paviliun Taman Tepi Sungai', 'Surabaya, Indonesia', 2021, '3,600 m²', null, ['laras-setyaningrum', 'dimas-arya-putra'], false],
            ['architecture', 'urban-planning', 'Old Town Revitalisation', 'Revitalisasi Kota Lama', 'Semarang, Indonesia', 2025, '18 ha', null, ['dimas-arya-putra'], false],
            ['architecture', 'urban-planning', 'Green Corridor Masterplan', 'Rencana Induk Koridor Hijau', 'Jakarta, Indonesia', 2023, '42 ha', null, ['dimas-arya-putra', 'adhitya-pranata'], false],
            ['interior', 'commercial', 'Studio Senopati', 'Studio Senopati', 'Jakarta, Indonesia', 2024, '650 m²', null, ['nadia-kusumawardani'], false],
            ['interior', 'commercial', 'Glasshouse Office', 'Kantor Glasshouse', 'Jakarta, Indonesia', 2022, '1,200 m²', null, ['nadia-kusumawardani', 'sekar-ayuningtyas'], false],
            ['interior', 'commercial', 'Atelier Concept Store', 'Toko Konsep Atelier', 'Bandung, Indonesia', 2023, '280 m²', null, ['sekar-ayuningtyas', 'gilang-ramadhan'], false],
            ['interior', 'residential', 'Japandi Apartment', 'Apartemen Japandi', 'Jakarta, Indonesia', 2025, '145 m²', 'Private client', ['sekar-ayuningtyas'], true],
            ['interior', 'residential', 'Timber Stair House', 'Rumah Tangga Kayu', 'Bandung, Indonesia', 2023, '320 m²', 'Private client', ['sekar-ayuningtyas'], false],
            ['interior', 'residential', 'Hillside Living', 'Hunian Lereng Bukit', 'Bali, Indonesia', 2022, '260 m²', 'Private client', ['ayu-ratnasari'], false],
            ['interior', 'residential', 'Kemang Loft', 'Loft Kemang', 'Jakarta, Indonesia', 2021, '180 m²', 'Private client', ['nadia-kusumawardani'], false],
            ['interior', 'hospitality', 'Kala Coffee House', 'Kedai Kopi Kala', 'Yogyakarta, Indonesia', 2024, '220 m²', null, ['ayu-ratnasari', 'kirana-maheswari'], false],
            ['interior', 'hospitality', 'Lantern Dining Room', 'Ruang Makan Lentera', 'Bali, Indonesia', 2023, '340 m²', null, ['ayu-ratnasari'], true],
            ['interior', 'hospitality', 'Harbour Lounge', 'Lounge Pelabuhan', 'Surabaya, Indonesia', 2022, '480 m²', null, ['nadia-kusumawardani'], false],
            ['design', 'furniture', 'Tenun Sideboard', 'Bufet Tenun', 'Jepara, Indonesia', 2024, null, null, ['kirana-maheswari'], false],
            ['design', 'furniture', 'Rattan Chair Series', 'Seri Kursi Rotan', 'Jepara, Indonesia', 2023, null, null, ['kirana-maheswari', 'bima-harjono'], false],
            ['design', 'furniture', 'Dining Table Collection', 'Koleksi Meja Makan', 'Yogyakarta, Indonesia', 2022, null, null, ['bima-harjono'], false],
            ['design', 'product', 'Ceramic Cup Set', 'Set Cangkir Keramik', 'Bandung, Indonesia', 2024, null, null, ['maya-anindita'], false],
            ['design', 'product', 'Leaf Tag Identity', 'Identitas Label Daun', 'Bandung, Indonesia', 2023, null, null, ['maya-anindita'], false],
            ['design', 'product', 'Thermal Bottle', 'Botol Termal', 'Yogyakarta, Indonesia', 2025, null, null, ['bima-harjono', 'maya-anindita'], false],
            ['design', 'visual-merch', 'Modular Fair Booth', 'Booth Pameran Modular', 'Jakarta, Indonesia', 2024, '36 m²', null, ['gilang-ramadhan'], false],
            ['design', 'visual-merch', 'Window Display Series', 'Seri Etalase', 'Jakarta, Indonesia', 2023, null, null, ['gilang-ramadhan'], false],
        ];
    }

    /**
     * Two text variants per discipline, in English and Indonesian.
     *
     * @return array{summary: array{en: string, id: string}, description: array{en: string, id: string}}
     */
    private function texts(Discipline $discipline, int $variant): array
    {
        $texts = [
            Discipline::Architecture->value => [
                [
                    'summary' => [
                        'en' => 'Organised around shade, air and light in {location}.',
                        'id' => 'Disusun di sekitar bayangan, udara, dan cahaya di {location}.',
                    ],
                    'description' => [
                        'en' => "{title} responds to its site in {location} with a simple, robust plan. Deep overhangs and a layered façade filter the tropical sun, while openings on opposite sides keep air moving through every room.\n\nThe material palette is deliberately limited: exposed concrete, local timber and steel detailed with care. Over time the building is meant to weather gracefully and become part of its surroundings.",
                        'id' => "{title} menanggapi tapaknya di {location} dengan denah yang sederhana dan kokoh. Teritis yang dalam dan fasad berlapis menyaring matahari tropis, sementara bukaan di sisi yang berseberangan menjaga udara terus mengalir ke setiap ruang.\n\nPilihan materialnya sengaja dibatasi: beton ekspos, kayu lokal, dan baja yang dirinci dengan cermat. Seiring waktu, bangunan ini dirancang untuk menua dengan anggun dan menyatu dengan lingkungannya.",
                    ],
                ],
                [
                    'summary' => [
                        'en' => 'Building and landscape designed as one, in {location}.',
                        'id' => 'Bangunan dan lanskap yang dirancang menyatu, di {location}.',
                    ],
                    'description' => [
                        'en' => "The brief for {title} asked for spaces that feel open without losing privacy. The design answers with a sequence of courtyards and thresholds that slow the visitor down and frame views of the surrounding landscape.\n\nStructure, services and finishes were coordinated from the first sketch so that every element could stay visible and do more than one job.",
                        'id' => "Brief untuk {title} meminta ruang yang terasa terbuka tanpa kehilangan privasi. Desainnya menjawab dengan rangkaian halaman dan ambang yang memperlambat langkah pengunjung serta membingkai pemandangan lanskap di sekitarnya.\n\nStruktur, utilitas, dan material akhir dikoordinasikan sejak sketsa pertama sehingga setiap elemen dapat tetap terlihat dan menjalankan lebih dari satu fungsi.",
                    ],
                ],
            ],
            Discipline::Interior->value => [
                [
                    'summary' => [
                        'en' => 'Natural textures and soft, even light in {location}.',
                        'id' => 'Tekstur alami dan cahaya lembut yang merata di {location}.',
                    ],
                    'description' => [
                        'en' => "{title} was conceived as a quiet backdrop for everyday life. Walls, floors and joinery share a restrained palette of oak, lime plaster and stone, letting furniture and people bring the colour.\n\nLighting was designed in layers, daylight first and warm indirect light in the evening, so the space changes character through the day.",
                        'id' => "{title} dirancang sebagai latar yang tenang bagi kehidupan sehari-hari. Dinding, lantai, dan furnitur bawaan memakai palet yang bersahaja dari kayu ek, plester kapur, dan batu, sehingga warna dihadirkan oleh perabot dan penghuninya.\n\nPencahayaan dirancang berlapis, cahaya alami terlebih dahulu lalu cahaya tidak langsung yang hangat di malam hari, sehingga karakter ruang berubah sepanjang hari.",
                    ],
                ],
                [
                    'summary' => [
                        'en' => 'Planned around how people gather, work and rest.',
                        'id' => 'Ditata mengikuti cara orang berkumpul, bekerja, dan beristirahat.',
                    ],
                    'description' => [
                        'en' => "The layout of {title} follows how the space is actually used. Circulation runs along the façade, leaving the centre free for gathering, while smaller rooms tuck in behind sliding screens.\n\nCustom joinery, built with local workshops, hides storage and services and gives the interior a crafted, durable finish.",
                        'id' => "Tata letak {title} mengikuti cara ruang ini benar-benar dipakai. Sirkulasi berjalan di sepanjang fasad sehingga bagian tengah tetap lapang untuk berkumpul, sementara ruang-ruang kecil berada di balik sekat geser.\n\nFurnitur bawaan yang dibuat bersama bengkel lokal menyembunyikan penyimpanan dan utilitas serta memberi interior ini sentuhan kriya yang tahan lama.",
                    ],
                ],
            ],
            Discipline::Design->value => [
                [
                    'summary' => [
                        'en' => 'Prototyped by hand and made with local makers in {location}.',
                        'id' => 'Dibuat prototipenya dengan tangan dan diproduksi bersama perajin lokal di {location}.',
                    ],
                    'description' => [
                        'en' => "{title} began as a series of hand-made prototypes tested in daily use. Each iteration removed something, until only the essential parts and proportions remained.\n\nThe final piece is produced in small batches with local makers, using materials that can be repaired and that age with character.",
                        'id' => "{title} berawal dari serangkaian prototipe buatan tangan yang diuji dalam penggunaan sehari-hari. Setiap iterasi mengurangi sesuatu, hingga hanya bagian dan proporsi yang paling penting yang tersisa.\n\nKarya akhirnya diproduksi dalam jumlah kecil bersama perajin lokal, memakai material yang dapat diperbaiki dan menua dengan karakter.",
                    ],
                ],
                [
                    'summary' => [
                        'en' => 'A modular system that is quick to build and easy to reuse.',
                        'id' => 'Sistem modular yang cepat dirakit dan mudah digunakan kembali.',
                    ],
                    'description' => [
                        'en' => "For {title} the challenge was to create a strong presence with very few elements. A modular system of panels and joints allows the design to be assembled quickly, adapted to different spaces and reused.\n\nMaterial, colour and graphics follow the brand's identity closely, keeping the focus on the products on display.",
                        'id' => "Untuk {title}, tantangannya adalah menciptakan kehadiran yang kuat dengan sedikit elemen. Sistem modular berupa panel dan sambungan memungkinkan desain dirakit dengan cepat, disesuaikan dengan ruang yang berbeda, dan digunakan kembali.\n\nMaterial, warna, dan grafis mengikuti identitas merek dengan saksama, sehingga fokus tetap pada produk yang dipajang.",
                    ],
                ],
            ],
        ];

        return $texts[$discipline->value][$variant];
    }
}
