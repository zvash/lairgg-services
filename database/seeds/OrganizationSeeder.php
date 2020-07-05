<?php

use App\Organization;
use App\Traits\Seeders\Storage;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    use Storage;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->organizations() as [$organization, $link]) {
            $organization = array_merge($organization, [
                'logo' => $this->store(
                    'organizations/logos', $this->getSeederPath($organization['logo'])
                ),
            ]);

            Organization::create($organization)->links()->create($link);
        }
    }

    /**
     * System default organizations.
     *
     * @return array
     */
    private function organizations()
    {
        return [
            [
                [
                    'title' => 'LAIR.GG',
                    'slug' => 'lair-gg',
                    'bio' => 'We are expecting to attract a large pool of teams all from different skill sets, help understand where they stand and place them in the right category of tournaments. Individuals can use this area to find the right team and also increase their knowledge of the game.',
                    'logo' => 'organizations/logos/lair-gg.jpeg',

                ],
                [
                    'link_type_id' => 1,
                    'url' => 'https://lair.gg/',
                ],
            ],

        ];
    }
}
