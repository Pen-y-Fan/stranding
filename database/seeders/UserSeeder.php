<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insertOrIgnore(self::getData());
    }

    public static function getData(): array
    {
        return [
            'admin' => [
                'name'              => 'Admin',
                'email'             => 'admin@example.com',
                'email_verified_at' => now(),
                'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            'user' => [
                'name'              => 'User',
                'email'             => 'user@example.com',
                'email_verified_at' => now(),
                'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token'    => Str::random(10),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];
    }
}
