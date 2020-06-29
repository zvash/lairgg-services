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
            $map = array_merge($map, [
                'image' => $this->store(
                    'maps/images', $this->getSeederPath($map['image'])
                ),
            ]);

            Map::create($map);
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
                'name' => 'bind',
                'image' => 'maps/images/bind.jpeg',
                'game_id' => 1,
            ],
            [
                'name' => 'Haven',
                'image' => 'maps/images/haven.jpeg',
                'game_id' => 1,
            ],
            [
                'name' => 'Split',
                'image' => 'maps/images/split.jpeg',
                'game_id' => 1,
            ],
            [
                'name' => 'Ascent',
                'image' => 'maps/images/ascent.jpeg',
                'game_id' => 1,
            ],
        ];
    }
}
