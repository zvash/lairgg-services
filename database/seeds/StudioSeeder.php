<?php

use App\Studio;
use App\Traits\Seeders\Storage;
use Illuminate\Database\Seeder;

class StudioSeeder extends Seeder
{
    use Storage;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->studios() as $studio) {
            $studio = array_merge($studio, [
                'logo' => $this->store(
                    'studios/logos', $this->getSeederPath($studio['logo'])
                ),
            ]);

            Studio::create($studio);
        }
    }

    /**
     * System default studios.
     *
     * @return array
     */
    private function studios()
    {
        return [
            [
                'name' => 'Riot Games',
                'website' => 'https://www.riotgames.com/',
                'logo' => 'studios/logos/riot.png',
            ],
        ];
    }
}
