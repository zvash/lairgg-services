<?php

use App\Map;
use App\Traits\Seeders\Storage;
use Illuminate\Database\Seeder;

class MapSeeder extends Seeder
{
    use Storage;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->maps() as $map) {
            Map::create(array_merge($map, [
                'image' => $this->store(
                    'maps/images', $this->getSeederPath($map['image'])
                ),
            ]));
        }
    }

    /**
     * System default maps.
     *
     * @return array
     */
    private function maps()
    {
        return [
            [
                'title' => 'bind',
                'image' => 'maps/images/bind.jpeg',
                'game_id' => 1,
            ],
            [
                'title' => 'Haven',
                'image' => 'maps/images/haven.jpeg',
                'game_id' => 1,
            ],
            [
                'title' => 'Split',
                'image' => 'maps/images/split.jpeg',
                'game_id' => 1,
            ],
            [
                'title' => 'Ascent',
                'image' => 'maps/images/ascent.jpeg',
                'game_id' => 1,
            ],
        ];
    }
}
