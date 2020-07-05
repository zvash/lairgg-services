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
        foreach ($this->studios() as [$studio, $link]) {
            $studio = array_merge($studio, [
                'logo' => $this->store(
                    'studios/logos', $this->getSeederPath($studio['logo'])
                ),
            ]);

            Studio::create($studio)->links()->create($link);
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
                [
                    'title' => 'Riot Games',
                    'logo' => 'studios/logos/riot.jpeg',
                ],
                [
                    'url' => 'https://www.riotgames.com/',
                    'link_type_id' => 1,
                ],
            ],
        ];
    }
}
