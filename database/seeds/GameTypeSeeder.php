<?php

use App\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->gameTypes() as $title) {
            GameType::create(compact('title'));
        }
    }

    /**
     * System default game types.
     *
     * @return array
     */
    private function gameTypes()
    {
        return [
            'FPS',
            'MOBA',
            'PvP',
            'Battle Royale',
            'RTS',
        ];
    }
}
