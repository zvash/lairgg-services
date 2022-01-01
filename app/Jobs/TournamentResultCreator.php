<?php

namespace App\Jobs;

use App\Play;
use App\Repositories\PlayRepository;
use App\Tournament;
use Faker\Factory as Faker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TournamentResultCreator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tournament $tournament
     */
    protected $tournament;

    /**
     * @var \Faker\Generator $faker
     */
    private $faker;

    /**
     * Create a new job instance.
     *
     * @param int $tournamentId
     */
    public function __construct(int $tournamentId)
    {
        $this->tournament = Tournament::find($tournamentId);
        $this->faker = Faker::create();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $repository = new PlayRepository();
        $matches = $this->tournament->matches()->orderBy('started_at')->get();
        foreach ($matches as $match) {
            if ($match->winner_team_id) {
                continue;
            }
            $plays = $match->plays;
            foreach ($plays as $play) {
                $this->updatePlay($play);
                $scores = $this->scoreCreator($play);
                $repository->setPlayScores($play, $scores);
            }
        }
    }

    private function updatePlay(Play $play)
    {
        if ($play->match->isRestMatch()) {
            return;
        }
        $play->map_id = mt_rand(1, 4);
        $play->edited_by = 2;
        //$play->screenshot = $this->saveImage('plays/screenshots', 100, 100);
        $play->save();
    }

    private function scoreCreator(Play $play)
    {
        $scores = [];
        $parties = $play->parties;
        $isWinner = mt_rand(0, 1) == 1;
        foreach ($parties as $party) {
            if (! $party->team_id) {
                $isWinner = false;
            }
            $score = [];
            $score['party_id'] = $party->id;
            $score['is_forfeit'] = false;
            $score['is_winner'] = $isWinner;
            if ($isWinner) {
                $score['score'] = mt_rand(5, 10);
            } else {
                if (! $party->team_id) {
                    $score['score'] = 0;
                } else {
                    $score['score'] = mt_rand(0, 4);
                }
            }
            $isWinner = !$isWinner;
            $scores[] = $score;
        }
        return $scores;
    }

    private function saveImage(string $path, int $width, int $height)
    {
        $local = 'public/storage/tests';
        $file = $this->faker->image($local, $width, $height);
        if ($file) {
            return Storage::putFile($path, $file);
        }
        return null;
    }
}
