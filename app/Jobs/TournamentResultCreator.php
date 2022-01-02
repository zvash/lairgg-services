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

    private $limit;

    /**
     * Create a new job instance.
     *
     * @param int $tournamentId
     * @param int $limit
     */
    public function __construct(int $tournamentId, int $limit = 0)
    {
        $this->tournament = Tournament::find($tournamentId);
        $this->limit = $limit;
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
        if ($this->limit) {
            $matches = $this->tournament->matches()->orderBy('started_at')->limit($this->limit)->get();
        } else {
            $matches = $this->tournament->matches()->orderBy('started_at')->get();
        }
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
        if ($play->match->isRestMatch()) {
            $isWinner = true;
        }
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
            $scores[] = $score;
            $isWinner = !$isWinner;
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
