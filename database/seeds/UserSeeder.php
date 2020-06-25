<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->users() as $user) {
            User::create($user);
        }
    }

    /**
     * System default users.
     *
     * @return array
     */
    private function users()
    {
        return [
            [
                'first_name' => 'Hossein',
                'last_name' => 'Jalali',
                'email' => 'info@lair.gg',
                'username' => 'hossj',
                'password' => bcrypt('passwords'),
                'timezone' => 'Europe/Lisbon',
                'email_verified_at' => now(),
            ],
        ];
    }
}
