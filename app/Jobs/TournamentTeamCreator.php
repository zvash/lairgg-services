<?php

namespace App\Jobs;

use App\Enums\ParticipantAcceptanceState;
use App\Participant;
use App\Team;
use App\Tournament;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TournamentTeamCreator
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
     */
    public function handle()
    {
        if (! $this->tournament) {
            return;
        }
        $this->tournament->engine()->createBracket();
        $teamMemberCount = $this->tournament->players;
        $teamsCount = $this->tournament->max_teams;
        $gameId = $this->tournament->game_id;
        for ($i = 1; $i <= $teamsCount; $i++) {
//            $team = $this->createTeam($gameId);
//            for ($j = 1; $j <= $teamMemberCount; $j++) {
//                $user = $this->createUser();
//                $isCaptain = $j == 1;
//                $team->players()->attach($user->id, ['captain' => $isCaptain]);
//            }
            $team = $this->getTeamWithOffset($i - 1);
            $participant = new Participant([
                'participantable_type' => Team::class,
                'participantable_id' => $team->id,
                'status' => ParticipantAcceptanceState::ACCEPTED,
            ]);
            $participant = $this->tournament->participants()->save($participant);
            $this->tournament->engine()->assignParticipantToFirstEmptyMatch($participant);
        }
    }

    private function getTeamWithOffset(int $offset)
    {
        return Team::query()->orderBy('id', 'desc')->limit(1)->offset($offset)->first();
    }

    private function createTeam(int $gameId)
    {
        $title = ucfirst($this->faker->word()) . ucfirst($this->faker->word());
        $bio = $this->faker->paragraph();
        $logo = $this->saveImage('teams/logos', 400, 400);
        $cover = $this->saveImage('teams/covers', 640, 400);
        $joinRequest = false;
        $joinUrl = null;
        $team = new Team([
            'title' => $title,
            'bio' => $bio,
            'logo' => $logo,
            'cover' => $cover,
            'game_id' => $gameId,
            'join_request' => $joinRequest,
            'join_url' => $joinUrl
        ]);
        $team->save();
        return $team;
    }

    private function createUser()
    {
        $attributes = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'username' => str_replace('.', '_', $this->faker->userName()),
            'timezone' => 'Europe/Lisbon',
            'gender_id' => 1,
            'password' => bcrypt('passwords'),
        ];
        $user = User::query()->create($attributes);
        $user->email_verified_at = now();
        $user->avatar = $this->saveImage('users/avatars', 400, 400);
        $user->cover = $this->saveImage('users/covers', 640, 480);
        $user->save();
        return $user;
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
