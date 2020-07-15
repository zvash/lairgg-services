<?php

use App\PrizeType;
use Illuminate\Database\Seeder;

class PrizeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->prizeTypes() as $title) {
            PrizeType::create(compact('title'));
        }
    }

    /**
     * System default prize types.
     *
     * @return array
     */
    private function prizeTypes()
    {
        return [
            'Gift',
            'Point',
            'Cash',
        ];
    }
}
