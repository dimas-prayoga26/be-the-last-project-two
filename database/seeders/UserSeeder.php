<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Designer;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Local accounts: one admin and a login for every designer. All use the password "password".
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@thelatestproject.id'],
            ['name' => 'Admin', 'password' => 'password', 'role' => UserRole::Admin, 'designer_id' => null],
        );

        Designer::query()->whereNotNull('email')->each(function (Designer $designer): void {
            User::updateOrCreate(
                ['email' => $designer->email],
                ['name' => $designer->name, 'password' => 'password', 'role' => UserRole::Designer, 'designer_id' => $designer->id],
            );
        });
    }
}
