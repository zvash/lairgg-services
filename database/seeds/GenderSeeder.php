<?php

use App\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->genders() as $title) {
            Gender::create(compact('title'));
        }
    }

    /**
     * System default genders.
     *
     * @return array
     */
    private function genders()
    {
        return [
            'Male',
            'Female',
            'Other',
            'Prefer not to say',
        ];
    }
}
