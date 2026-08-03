<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Known-credential accounts for local development and manual testing.
 *
 * Deliberately refuses to run in production: these passwords are public in the
 * repository. Roles are not assigned yet — Spatie Permission arrives in
 * T-M2-005, and the richer AdminUserSeeder / DemoUsersSeeder in T-M2-022.
 *
 * Idempotent, so `db:seed` can be re-run without unique-constraint failures.
 */
final class DevUserSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /**
     * @var list<array{name: string, email: string}>
     */
    private const ACCOUNTS = [
        ['name' => 'Test Customer', 'email' => 'test@craftique.test'],
        ['name' => 'Second Customer', 'email' => 'second@craftique.test'],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'DevUserSeeder must never run in production: its passwords are public in the repository.',
            );
        }

        foreach (self::ACCOUNTS as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make(self::PASSWORD),
                    // User implements MustVerifyEmail, so an unverified account
                    // is bounced to /verify-email and cannot reach the dashboard.
                    'email_verified_at' => now(),
                ],
            );

            $this->command?->info("  seeded {$account['email']}");
        }
    }
}
