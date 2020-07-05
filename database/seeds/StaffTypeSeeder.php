<?php

use App\StaffType;
use Illuminate\Database\Seeder;

class StaffTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->staffTypes() as $title) {
            StaffType::create(compact('title'));
        }
    }

    /**
     * System default staff types.
     *
     * @return array
     */
    private function staffTypes()
    {
        return [
            'Admin',
            'Moderator',
        ];
    }
}
