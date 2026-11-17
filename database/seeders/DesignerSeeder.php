<?php

namespace Database\Seeders;

use App\Enums\SocialPlatform;
use App\Models\Designer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DesignerSeeder extends Seeder
{
    /**
     * Fictional placeholder members of the collective. Replace them through the admin panel.
     */
    public function run(): void
    {
        $profileTexts = $this->profileTexts();

        foreach ($this->designers() as $position => $data) {
            $designer = Designer::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'title' => $data['title'],
                    'headline' => $profileTexts[$data['name']]['headline'] ?? null,
                    'email' => Str::lower(Str::before($data['name'], ' ')).'@thelatestproject.id',
                    'location' => $data['location'],
                    'bio' => $data['bio'],
                    'statement' => $profileTexts[$data['name']]['statement'] ?? null,
                    'socials' => [
                        ['platform' => SocialPlatform::Instagram->value, 'url' => 'https://www.instagram.com/'.$data['handle']],
                        ['platform' => $data['second_platform']->value, 'url' => $data['second_url']],
                    ],
                    'is_published' => true,
                    'sort_order' => $position,
                ],
            );

            $photo = storage_path("app/seed-images/people/{$data['photo']}");

            if (is_file($photo) && ! $designer->hasMedia('photo')) {
                $designer->addMedia($photo)->preservingOriginal()->toMediaCollection('photo');
            }
        }
    }

    /**
     * The headline under each name and the quote on each profile page, keyed by designer name.
     *
     * @return array<string, array{headline: array<string, string>, statement: array<string, string>}>
     */
    private function profileTexts(): array
    {
        return [
            'Adhitya Pranata' => [
                'headline' => ['en' => 'Buildings that work with the tropical sun', 'id' => 'Bangunan yang bekerja bersama matahari tropis'],
                'statement' => ['en' => 'Shade, air and light come first. The form follows once the climate has been answered.', 'id' => 'Bayangan, udara, dan cahaya didahulukan. Bentuk mengikuti setelah iklim terjawab.'],
            ],
            'Sekar Ayuningtyas' => [
                'headline' => ['en' => 'Calm rooms, edited with care', 'id' => 'Ruang yang tenang, disunting dengan cermat'],
                'statement' => ['en' => 'A good interior is one you stop noticing and start living in.', 'id' => 'Interior yang baik adalah interior yang tak lagi Anda perhatikan, tetapi Anda tinggali.'],
            ],
            'Raka Wibisono' => [
                'headline' => ['en' => 'Houses that open to the landscape', 'id' => 'Rumah yang terbuka ke lanskap'],
                'statement' => ['en' => 'Local craft is not a style to borrow. It is knowledge to build on.', 'id' => 'Kriya lokal bukan gaya untuk dipinjam. Ia pengetahuan untuk dikembangkan.'],
            ],
            'Nadia Kusumawardani' => [
                'headline' => ['en' => 'Hospitality spaces with a sense of place', 'id' => 'Ruang hospitality yang punya rasa tempat'],
                'statement' => ['en' => 'Every guest should be able to tell which city they woke up in.', 'id' => 'Setiap tamu semestinya tahu di kota mana ia terbangun.'],
            ],
            'Bima Harjono' => [
                'headline' => ['en' => 'Everyday objects, made to last', 'id' => 'Benda sehari-hari yang dibuat untuk bertahan'],
                'statement' => ['en' => 'I design for the hand first and the photograph last.', 'id' => 'Saya merancang untuk tangan lebih dulu, untuk foto paling akhir.'],
            ],
            'Laras Setyaningrum' => [
                'headline' => ['en' => 'Landscapes that give back to the city', 'id' => 'Lanskap yang memberi kembali kepada kota'],
                'statement' => ['en' => 'A garden is never finished. I design how it will grow, not how it looks on day one.', 'id' => 'Taman tak pernah selesai. Saya merancang cara ia tumbuh, bukan rupanya di hari pertama.'],
            ],
            'Dimas Arya Putra' => [
                'headline' => ['en' => 'Streets designed for people first', 'id' => 'Jalan yang dirancang untuk manusia lebih dulu'],
                'statement' => ['en' => 'A city is measured by how easily a child can walk to school.', 'id' => 'Kota diukur dari seberapa mudah seorang anak berjalan ke sekolah.'],
            ],
            'Kirana Maheswari' => [
                'headline' => ['en' => 'Furniture shaped with Jepara carpenters', 'id' => 'Furnitur yang dibentuk bersama tukang kayu Jepara'],
                'statement' => ['en' => 'The joint is the design. Everything else is there to show it.', 'id' => 'Sambungan adalah desainnya. Selebihnya hadir untuk menampilkannya.'],
            ],
            'Fajar Nugroho' => [
                'headline' => ['en' => 'Clear plans, honest materials', 'id' => 'Denah yang jernih, material yang jujur'],
                'statement' => ['en' => 'Concrete, timber and steel, detailed well, are enough for almost anything.', 'id' => 'Beton, kayu, dan baja yang didetailkan dengan baik sudah cukup untuk hampir segalanya.'],
            ],
            'Ayu Ratnasari' => [
                'headline' => ['en' => 'Interiors with warmth and restraint', 'id' => 'Interior yang hangat dan bersahaja'],
                'statement' => ['en' => 'I keep the palette small so the people in the room can be the colour.', 'id' => 'Saya menjaga palet tetap sedikit agar orang di dalam ruang yang menjadi warnanya.'],
            ],
            'Gilang Ramadhan' => [
                'headline' => ['en' => 'Windows and displays that stop people in the street', 'id' => 'Etalase dan display yang menghentikan langkah orang'],
                'statement' => ['en' => 'A window has three seconds to catch someone. I design for those three seconds.', 'id' => 'Sebuah etalase hanya punya tiga detik untuk menarik perhatian. Saya merancang untuk tiga detik itu.'],
            ],
            'Maya Anindita' => [
                'headline' => ['en' => 'Products that make small rituals better', 'id' => 'Produk yang membuat ritual kecil lebih baik'],
                'statement' => ['en' => 'Good design disappears into the habit it supports.', 'id' => 'Desain yang baik larut ke dalam kebiasaan yang didukungnya.'],
            ],
        ];
    }

    /**
     * @return list<array{name: string, title: array<string, string>, location: string, bio: array<string, string>, handle: string, second_platform: SocialPlatform, second_url: string, photo: string}>
     */
    private function designers(): array
    {
        return [
            [
                'name' => 'Adhitya Pranata',
                'title' => ['en' => 'Architect', 'id' => 'Arsitek'],
                'location' => 'Jakarta, Indonesia',
                'bio' => [
                    'en' => 'Adhitya designs commercial and residential buildings that answer the tropical climate with deep shade, cross ventilation and honest materials. His work moves between office towers and small family homes.',
                    'id' => 'Adhitya merancang bangunan komersial dan hunian yang menjawab iklim tropis lewat bayangan yang dalam, ventilasi silang, dan material yang jujur. Karyanya bergerak di antara menara perkantoran dan rumah keluarga yang kecil.',
                ],
                'handle' => 'adhitya.pranata',
                'second_platform' => SocialPlatform::LinkedIn,
                'second_url' => 'https://www.linkedin.com/in/adhitya-pranata',
                'photo' => 'm01.jpg',
            ],
            [
                'name' => 'Sekar Ayuningtyas',
                'title' => ['en' => 'Interior Designer', 'id' => 'Desainer Interior'],
                'location' => 'Bandung, Indonesia',
                'bio' => [
                    'en' => 'Sekar creates calm, light-filled interiors with natural textures and carefully edited details. She works on homes, studios and small retail spaces.',
                    'id' => 'Sekar menciptakan interior yang tenang dan terang dengan tekstur alami serta detail yang dipilih dengan cermat. Ia mengerjakan rumah, studio, dan ruang ritel kecil.',
                ],
                'handle' => 'sekar.ayu.studio',
                'second_platform' => SocialPlatform::Pinterest,
                'second_url' => 'https://www.pinterest.com/sekarayuningtyas',
                'photo' => 'w01.jpg',
            ],
            [
                'name' => 'Raka Wibisono',
                'title' => ['en' => 'Architect', 'id' => 'Arsitek'],
                'location' => 'Bali, Indonesia',
                'bio' => [
                    'en' => 'Raka builds villas, retreats and courtyard houses that open to the landscape. He is interested in local building craft and how it can meet contemporary living.',
                    'id' => 'Raka membangun vila, tempat peristirahatan, dan rumah berhalaman yang terbuka ke lanskap. Ia tertarik pada keterampilan membangun lokal dan bagaimana hal itu bertemu dengan cara hidup masa kini.',
                ],
                'handle' => 'raka.wibisono',
                'second_platform' => SocialPlatform::Behance,
                'second_url' => 'https://www.behance.net/rakawibisono',
                'photo' => 'm02.jpg',
            ],
            [
                'name' => 'Nadia Kusumawardani',
                'title' => ['en' => 'Interior Designer', 'id' => 'Desainer Interior'],
                'location' => 'Jakarta, Indonesia',
                'bio' => [
                    'en' => 'Nadia designs workplaces and hospitality interiors where planning and atmosphere carry equal weight. Her projects favour glass, steel and warm timber.',
                    'id' => 'Nadia merancang interior kantor dan hospitality yang memberi bobot setara pada tata ruang dan suasana. Proyeknya banyak memakai kaca, baja, dan kayu yang hangat.',
                ],
                'handle' => 'nadia.kusuma.interior',
                'second_platform' => SocialPlatform::LinkedIn,
                'second_url' => 'https://www.linkedin.com/in/nadia-kusumawardani',
                'photo' => 'w02.jpg',
            ],
            [
                'name' => 'Bima Harjono',
                'title' => ['en' => 'Product Designer', 'id' => 'Desainer Produk'],
                'location' => 'Yogyakarta, Indonesia',
                'bio' => [
                    'en' => 'Bima designs furniture and everyday objects with workshops across Java. He prototypes by hand and keeps production close to the makers.',
                    'id' => 'Bima merancang furnitur dan benda sehari-hari bersama bengkel-bengkel di Jawa. Ia membuat prototipe dengan tangan dan menjaga produksi tetap dekat dengan para perajin.',
                ],
                'handle' => 'bima.harjono',
                'second_platform' => SocialPlatform::Behance,
                'second_url' => 'https://www.behance.net/bimaharjono',
                'photo' => 'm03.jpg',
            ],
            [
                'name' => 'Laras Setyaningrum',
                'title' => ['en' => 'Landscape Architect', 'id' => 'Arsitek Lanskap'],
                'location' => 'Bali, Indonesia',
                'bio' => [
                    'en' => 'Laras shapes gardens, terraces and public parks around native planting and water. She often collaborates with architects from the first sketch.',
                    'id' => 'Laras membentuk taman, teras, dan taman publik dengan tanaman lokal dan air sebagai dasarnya. Ia kerap berkolaborasi dengan arsitek sejak sketsa pertama.',
                ],
                'handle' => 'laras.landscape',
                'second_platform' => SocialPlatform::Website,
                'second_url' => 'https://example.com/laras',
                'photo' => 'w03.jpg',
            ],
            [
                'name' => 'Dimas Arya Putra',
                'title' => ['en' => 'Urban Planner', 'id' => 'Perencana Kota'],
                'location' => 'Surabaya, Indonesia',
                'bio' => [
                    'en' => 'Dimas works on masterplans and neighbourhood revitalisation with a focus on walkable streets and shared green space.',
                    'id' => 'Dimas mengerjakan rencana induk dan revitalisasi kawasan dengan fokus pada jalan yang ramah pejalan kaki dan ruang hijau bersama.',
                ],
                'handle' => 'dimas.aryaputra',
                'second_platform' => SocialPlatform::LinkedIn,
                'second_url' => 'https://www.linkedin.com/in/dimas-arya-putra',
                'photo' => 'm04.jpg',
            ],
            [
                'name' => 'Kirana Maheswari',
                'title' => ['en' => 'Furniture Designer', 'id' => 'Desainer Furnitur'],
                'location' => 'Jepara, Indonesia',
                'bio' => [
                    'en' => 'Kirana designs furniture with Jepara carpenters, pairing traditional joinery with quiet contemporary lines.',
                    'id' => 'Kirana merancang furnitur bersama para tukang kayu Jepara, memadukan sambungan tradisional dengan garis kontemporer yang tenang.',
                ],
                'handle' => 'kirana.maheswari',
                'second_platform' => SocialPlatform::Pinterest,
                'second_url' => 'https://www.pinterest.com/kiranamaheswari',
                'photo' => 'w04.jpg',
            ],
            [
                'name' => 'Fajar Nugroho',
                'title' => ['en' => 'Architect', 'id' => 'Arsitek'],
                'location' => 'Semarang, Indonesia',
                'bio' => [
                    'en' => 'Fajar designs retail, office and residential buildings with a precise, restrained vocabulary of brick, concrete and steel.',
                    'id' => 'Fajar merancang bangunan ritel, kantor, dan hunian dengan bahasa yang presisi dan bersahaja dari bata, beton, dan baja.',
                ],
                'handle' => 'fajar.nugroho.arch',
                'second_platform' => SocialPlatform::LinkedIn,
                'second_url' => 'https://www.linkedin.com/in/fajar-nugroho',
                'photo' => 'm05.jpg',
            ],
            [
                'name' => 'Ayu Ratnasari',
                'title' => ['en' => 'Interior Designer', 'id' => 'Desainer Interior'],
                'location' => 'Bali, Indonesia',
                'bio' => [
                    'en' => 'Ayu designs restaurants, cafés and homes where lighting and texture set the mood. Her interiors are warm, tactile and made to be used.',
                    'id' => 'Ayu merancang restoran, kafe, dan rumah yang suasananya dibangun lewat cahaya dan tekstur. Interiornya hangat, kaya rasa, dan dibuat untuk dipakai.',
                ],
                'handle' => 'ayu.ratnasari',
                'second_platform' => SocialPlatform::Behance,
                'second_url' => 'https://www.behance.net/ayuratnasari',
                'photo' => 'w05.jpg',
            ],
            [
                'name' => 'Gilang Ramadhan',
                'title' => ['en' => 'Visual Merchandising Designer', 'id' => 'Desainer Visual Merchandising'],
                'location' => 'Jakarta, Indonesia',
                'bio' => [
                    'en' => 'Gilang designs booths, window displays and retail installations that are built fast and reused often.',
                    'id' => 'Gilang merancang booth, etalase, dan instalasi ritel yang cepat dibangun dan bisa dipakai berulang kali.',
                ],
                'handle' => 'gilang.ramadhan.vm',
                'second_platform' => SocialPlatform::TikTok,
                'second_url' => 'https://www.tiktok.com/@gilangramadhan.vm',
                'photo' => 'm06.jpg',
            ],
            [
                'name' => 'Maya Anindita',
                'title' => ['en' => 'Product Designer', 'id' => 'Desainer Produk'],
                'location' => 'Bandung, Indonesia',
                'bio' => [
                    'en' => 'Maya designs objects and packaging for small brands, from ceramic tableware to everyday carry. She cares about how things feel in the hand.',
                    'id' => 'Maya merancang benda dan kemasan untuk merek-merek kecil, dari peralatan makan keramik hingga barang bawaan sehari-hari. Ia peduli pada rasa sebuah benda di tangan.',
                ],
                'handle' => 'maya.anindita',
                'second_platform' => SocialPlatform::Behance,
                'second_url' => 'https://www.behance.net/mayaanindita',
                'photo' => 'w06.jpg',
            ],
        ];
    }
}
