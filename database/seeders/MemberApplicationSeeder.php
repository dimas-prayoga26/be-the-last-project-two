<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\Discipline;
use App\Models\MemberApplication;
use Illuminate\Database\Seeder;

class MemberApplicationSeeder extends Seeder
{
    /**
     * Placeholder "Join the collective" applications for the admin inbox.
     */
    public function run(): void
    {
        $applications = [
            [
                'name' => 'Dimas Prakoso',
                'email' => 'dimas@example.com',
                'phone' => '+62 812 3456 7890',
                'city' => 'Yogyakarta',
                'discipline' => Discipline::Architecture,
                'portfolio_url' => 'https://example.com/dimas-prakoso',
                'instagram' => '@dimas.prakoso.studio',
                'message' => 'Architect working on small houses with exposed brick and timber. I would like to share my work and learn from the other members.',
            ],
            [
                'name' => 'Nadia Halim',
                'email' => 'nadia@example.com',
                'phone' => null,
                'city' => 'Surabaya',
                'discipline' => Discipline::Interior,
                'portfolio_url' => 'https://example.com/nadia-halim',
                'instagram' => '@nadiahalim.interior',
                'message' => 'Interior designer focused on hospitality projects, mostly cafes and boutique hotels in East Java.',
            ],
            [
                'name' => 'Arief Wicaksono',
                'email' => 'arief@example.com',
                'phone' => '+62 813 2222 1111',
                'city' => 'Bandung',
                'discipline' => Discipline::Design,
                'portfolio_url' => 'https://example.com/arief-wicaksono',
                'instagram' => null,
                'message' => 'Product designer making lighting and small furniture with local rattan craftsmen.',
            ],
        ];

        foreach ($applications as $index => $attributes) {
            $application = MemberApplication::updateOrCreate(['email' => $attributes['email']], $attributes);

            if ($index === 2 && $application->status === ApplicationStatus::Pending) {
                $application->review(ApplicationStatus::Accepted);
            }
        }
    }
}
