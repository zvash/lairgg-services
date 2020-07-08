<?php

use App\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->regions() as $title) {
            Region::create(compact('title'));
        }
    }

    /**
     * System default regions.
     *
     * @return array
     */
    private function regions()
    {
        return [
            'Africa',
            'Asia',
            'Europe',
            'North America',
            'South America',
            'Oceania',
        ];
    }
}
