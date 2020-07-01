<?php

use App\Game;
use App\Traits\Seeders\Storage;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    use Storage;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->games() as $game) {
            $game = array_merge($game, [
                'image' => $this->store(
                    'games/images', $this->getSeederPath($game['image'])
                ),

                'cover' => $this->store(
                    'games/covers', $this->getSeederPath($game['cover'])
                ),

                'logo' => $this->store(
                    'games/logos', $this->getSeederPath($game['logo'])
                ),
            ]);

            Game::create($game);
        }
    }

    /**
     * System default games.
     *
     * @return array
     */
    private function games()
    {
        return [
            [
                'name' => 'Valorant',
                'bio' => 'Valorant is an upcoming tactical shooter game developed and published by Riot Games. It was announced on October 15, 2019 under the codename Project A.',
                'launched_at' => '02-06-2020',
                'image' => 'games/images/valorant.jpeg',
                'cover' => 'games/covers/valorant.jpeg',
                'logo' => 'games/logos/valorant.jpeg',
                'website' => 'https://playvalorant.com/',
                'game_type_id' => 1,
                'studio_id' => 1,
            ],
        ];
    }
}
