<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Örnek kullanıcıları oluşturur.
     *
     * Çalıştırmak için:
     *   php artisan db:seed --class=UserSeeder
     *
     * Veya tüm seeder'ları çalıştırmak için:
     *   php artisan db:seed
     */
    public function run(): void
    {
        // ── Yönetici ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@beyoglu.bel.tr'],
            [
                'name'     => 'Sistem Yöneticisi',
                'password' => Hash::make('Admin@12345'),
                'role'     => 'admin',
            ]
        );

        // ── Personel ──────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'personel@beyoglu.bel.tr'],
            [
                'name'     => 'Ayşe Kaya',
                'password' => Hash::make('Personel@12345'),
                'role'     => 'personel',
            ]
        );

        // ── Okuyucu ───────────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'okuyucu@beyoglu.bel.tr'],
            [
                'name'     => 'Mehmet Yılmaz',
                'password' => Hash::make('Okuyucu@12345'),
                'role'     => 'okuyucu',
            ]
        );

        $this->command->info('✓ Örnek kullanıcılar oluşturuldu:');
        $this->command->table(
            ['E-posta', 'Şifre', 'Rol'],
            [
                ['admin@beyoglu.bel.tr',     'Admin@12345',    'Yönetici'],
                ['personel@beyoglu.bel.tr',  'Personel@12345', 'Personel'],
                ['okuyucu@beyoglu.bel.tr',   'Okuyucu@12345',  'Okuyucu'],
            ]
        );
    }
}
