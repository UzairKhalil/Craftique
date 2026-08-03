<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Production-safe seeders (roles, settings, categories, tax zones) get
     * split out from development fixtures as those land — see T-M2-022.
     */
    public function run(): void
    {
        $this->call([
            DevUserSeeder::class,
        ]);
    }
}
