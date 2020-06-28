<?php

use App\Traits\Seeders\Storage;
use App\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use Storage;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->users() as $user) {
            $user['avatar'] = $this->store(
                'users/avatars', $this->getSeederPath($user['avatar'])
            );

            $user['cover'] = $this->store(
                'users/covers', $this->getSeederPath($user['cover'])
            );

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
                'email' => 'hossein@edoramedia.com',
                'username' => 'hossj',
                'password' => bcrypt('passwords'),
                'timezone' => 'Europe/Lisbon',
                'email_verified_at' => now(),
                'avatar' => 'users/avatars/hossein.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Ali',
                'last_name' => 'Shafiee',
                'email' => 'ali.shafiee@edoramedia.com',
                'username' => 'alshf89',
                'password' => bcrypt('passwords'),
                'timezone' => 'Asia/Tehran',
                'dob' => '1989-02-18',
                'email_verified_at' => now(),
                'avatar' => 'users/avatars/ali.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'iLyad',
                'last_name' => 'Esfandiari',
                'email' => 'ilyad@edoramedia.com',
                'username' => 'slimkd',
                'password' => bcrypt('passwords'),
                'timezone' => 'Asia/Tehran',
                'email_verified_at' => now(),
                'avatar' => 'users/avatars/ilyad.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Farbod',
                'last_name' => 'Ghasemi',
                'email' => 'farbod@edoramedia.com',
                'username' => 'psycho',
                'password' => bcrypt('passwords'),
                'timezone' => 'Asia/Tehran',
                'email_verified_at' => now(),
                'avatar' => 'users/avatars/farbod.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
        ];
    }
}
