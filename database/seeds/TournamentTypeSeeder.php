<?php

use App\Enums\TournamentStage;
use App\TournamentType;
use Illuminate\Database\Seeder;

class TournamentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->tournamentTypes() as [$title, $stage]) {
            TournamentType::create(compact('title', 'stage'));
        }
    }

    /**
     * System default tournament types.
     *
     * @return array
     */
    private function tournamentTypes()
    {
        return [
            [
                'Single Elimination',
                TournamentStage::Dual,
            ],
            [
                'Double Elimination',
                TournamentStage::Dual,
            ],
            [
                'Round Robin',
                TournamentStage::Dual,
            ],
            [
                'League',
                TournamentStage::Dual,
            ],
            [
                'Battle Royale',
                TournamentStage::FFA,
            ],
        ];
    }
}
