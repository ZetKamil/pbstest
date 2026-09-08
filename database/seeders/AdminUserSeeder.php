<?php
namespace Database\Seeders;
use App\Models\User; // user model
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // wachtwoord hashen
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'kamil@test.com', // unieke sleutel om duplicaten te vermijden
],
[
    'name' => 'Kamil Admin', // naam van de admin
    'currency_code' => 'EUR',
    'password' => Hash::make('kamil123'), // wachtwoord gehashed opslaan
'email_verified_at' => now(), // meteen als verified markeren
]
);
}
}
