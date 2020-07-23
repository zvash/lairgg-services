<?php

use App\ValueType;
use Illuminate\Database\Seeder;

class ValueTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->valueTypes() as $title) {
            ValueType::create(compact('title'));
        }
    }

    /**
     * System default value types.
     *
     * @return array
     */
    private function valueTypes()
    {
        return [
            'Gift',
            'Point',
            'Cash',
        ];
    }
}
