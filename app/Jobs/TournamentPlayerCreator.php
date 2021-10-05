<?php

namespace App\Jobs;

use App\Enums\ParticipantAcceptanceState;
use App\Participant;
use App\Tournament;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TournamentPlayerCreator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tournament $tournament
     */
    protected $tournament;

    /**
     * Create a new job instance.
     *
     * @param int $tournamentId
     */
    public function __construct(int $tournamentId)
    {
        $this->tournament = Tournament::find($tournamentId);
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
        $playersCount = $this->tournament->max_teams;
        $users = User::query()->orderBy('id', 'desc')->limit($playersCount)->get();
        foreach ($users as $user) {
            $participant = new Participant([
                'participantable_type' => User::class,
                'participantable_id' => $user->id,
                'status' => ParticipantAcceptanceState::ACCEPTED,
            ]);
            $participant = $this->tournament->participants()->save($participant);
            $this->tournament->engine()->assignParticipantToFirstEmptyMatch($participant);
        }
    }
}
