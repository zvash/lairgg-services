<?php

use App\LinkType;
use Illuminate\Database\Seeder;

class LinkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->linkTypes() as $name) {
            LinkType::create(compact('name'));
        }
    }

    /**
     * System default link types.
     *
     * @return array
     */
    private function linkTypes()
    {
        return [
            'Website',
            'Discord',
            'Facebook',
            'Twitter',
        ];
    }
}
