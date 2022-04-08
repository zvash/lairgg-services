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
            $user = array_merge($user, [
                'gender_id' => 1,
                'password' => bcrypt('passwords'),
                'email_verified_at' => now(),
                'avatar' => $this->store(
                    'users/avatars', $this->getSeederPath($user['avatar'])
                ),
                'cover' => $this->store(
                    'users/covers', $this->getSeederPath($user['cover'])
                ),
            ]);

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
                'timezone' => 'Europe/Lisbon',
                'avatar' => 'users/avatars/hossein.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Ali',
                'last_name' => 'Shafiee',
                'email' => 'ali.shafiee@edoramedia.com',
                'username' => 'alshf89',
                'timezone' => 'Asia/Tehran',
                'dob' => '1989-02-18',
                'avatar' => 'users/avatars/ali.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'iLyad',
                'last_name' => 'Esfandiari',
                'email' => 'ilyad@edoramedia.com',
                'username' => 'slimkd',
                'timezone' => 'Asia/Tehran',
                'avatar' => 'users/avatars/ilyad.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Farbod',
                'last_name' => 'Ghasemi',
                'email' => 'farbod@edoramedia.com',
                'username' => 'psycho',
                'timezone' => 'Asia/Tehran',
                'avatar' => 'users/avatars/farbod.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Ehsan',
                'last_name' => 'Jalali',
                'email' => 'ace@lair.gg',
                'username' => 'ace',
                'timezone' => 'Europe/Lisbon',
                'avatar' => 'users/avatars/ahson.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
            [
                'first_name' => 'Siavash',
                'last_name' => 'Hekmatnia',
                'email' => 'siavash@lair.gg',
                'username' => 'siavash',
                'timezone' => 'Asia/Tehran',
                'avatar' => 'users/avatars/siavash.jpeg',
                'cover' => 'users/covers/valorant.jpeg',
            ],
        ];
    }
}
