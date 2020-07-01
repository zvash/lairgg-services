<?php

use App\Organization;
use App\Traits\Seeders\Storage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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
        foreach ($this->organizations() as $organization) {
            $organization = array_merge($organization, [
                'logo' => $this->store(
                    'organizations/logos', $this->getSeederPath($organization['logo'])
                ),
            ]);

            $website = Arr::pull($organization, 'website');

            Organization::create($organization)->links()->create([
                'url' => $website,
                'link_type_id' => 1,
            ]);
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
                'name' => 'LAIR.GG',
                'username' => 'lair-gg',
                'bio' => 'We are expecting to attract a large pool of teams all from different skill sets, help understand where they stand and place them in the right category of tournaments. Individuals can use this area to find the right team and also increase their knowledge of the game.',
                'logo' => 'organizations/logos/lair-gg.jpeg',
                'website' => 'https://lair.gg/',
            ],
        ];
    }
}
