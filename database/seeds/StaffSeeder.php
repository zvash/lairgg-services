<?php

use App\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (User::all() as $user) {
            $user->staff()->create([
                'organization_id' => 1,
                'staff_type_id' => $user->id > 1 ? 2 : 1,
                'owner' => $user->id == 1,
            ]);
        }
    }
}
