<?php

namespace Database\Seeders;

use App\Enums\SocialPlatform;
use App\Models\StudioProfile;
use Illuminate\Database\Seeder;

class StudioProfileSeeder extends Seeder
{
    /**
     * The About text is quoted from the brand guidelines (p4). Contact details are placeholders.
     */
    public function run(): void
    {
        $studio = StudioProfile::current();

        $studio->update([
            'intro' => [
                'en' => 'A collective community of architectural, interior, and product designers.',
                'id' => 'Komunitas kolektif para desainer arsitektur, interior, dan produk.',
            ],
            'about_heading' => [
                'en' => 'A space for designers to build connections, share perspectives and create new work.',
                'id' => 'Ruang bagi desainer untuk membangun koneksi, berbagi perspektif, dan menciptakan karya baru.',
            ],
            'about_body' => [
                'en' => "The Latest Project is a collective community of architectural, interior, and product designer. The Latest Project exists as a space for designer to build connections, share perspectives, and simultaneously encourage the enhancement of skills and competencies in producing new works and ideas in the fields of architecture, interior, and product design.\n\nAs part of its activities, The Latest Project regularly publishes the design works of its members through The Latest Project's official website and social media. This provides an opportunity for designers to showcase their work, explore ideas, and receive appreciation and feedback from fellow design practitioners as well as from the public who have an interest in architectural, interior, and product design.",
                'id' => "The Latest Project adalah komunitas kolektif para desainer arsitektur, interior, dan produk. The Latest Project hadir sebagai ruang bagi para desainer untuk membangun koneksi, berbagi perspektif, sekaligus mendorong peningkatan keterampilan dan kompetensi dalam menghasilkan karya dan gagasan baru di bidang arsitektur, interior, dan desain produk.\n\nSebagai bagian dari kegiatannya, The Latest Project secara rutin memublikasikan karya desain para anggotanya melalui situs web resmi dan media sosial The Latest Project. Hal ini memberi kesempatan bagi para desainer untuk menampilkan karya, mengeksplorasi gagasan, serta menerima apresiasi dan masukan dari sesama praktisi desain maupun dari publik yang memiliki minat pada desain arsitektur, interior, dan produk.",
            ],
            'email' => 'hello@thelatestproject.id',
            'phone' => '+62 21 0000 0000',
            'whatsapp' => '+62 812 0000 0000',
            'address' => "Jakarta\nIndonesia",
            'map_url' => null,
            'office_hours' => [
                'en' => 'Monday–Friday, 09.00–17.00 WIB',
                'id' => 'Senin–Jumat, 09.00–17.00 WIB',
            ],
            'socials' => [
                ['platform' => SocialPlatform::Instagram->value, 'url' => 'https://www.instagram.com/thelatestproject.id'],
                ['platform' => SocialPlatform::LinkedIn->value, 'url' => 'https://www.linkedin.com/company/thelatestproject'],
                ['platform' => SocialPlatform::Pinterest->value, 'url' => 'https://www.pinterest.com/thelatestproject'],
            ],
        ]);

        $aboutImage = SeedImages::in('interior')[0] ?? null;

        if ($aboutImage !== null && ! $studio->hasMedia('about_image')) {
            $studio->addMedia($aboutImage)->preservingOriginal()->toMediaCollection('about_image');
        }
    }
}
