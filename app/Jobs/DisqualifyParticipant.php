<?php

namespace App\Jobs;

use App\Enums\ParticipantAcceptanceState;
use App\Events\ParticipantIsDisqualified;
use App\Match;
use App\MatchParticipant;
use App\Participant;
use App\Repositories\LobbyRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DisqualifyParticipant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var MatchParticipant $matchParticipant
     */
    protected $matchParticipant;

    /**
     * @var LobbyRepository $lobbyRepository
     */
    protected $lobbyRepository;

    /**
     * DisqualifyParticipant constructor.
     * @param MatchParticipant $matchParticipant
     * @param LobbyRepository $lobbyRepository
     */
    public function __construct(MatchParticipant $matchParticipant, LobbyRepository $lobbyRepository)
    {
        $this->matchParticipant = $matchParticipant;
        $this->lobbyRepository = $lobbyRepository;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $participant = $this->matchParticipant->participant;
        $match = $this->matchParticipant->match;
        DB::beginTransaction();
        try {
            $participant->status = ParticipantAcceptanceState::DISQUALIFIED;
            $this->matchParticipant->disqualified_at = Carbon::now();
            $participant->save();
            $this->matchParticipant->save();
            MatchParticipant::query()
                ->where('participant_id', $participant->id)
                ->whereNull('ready_at')
                ->update(['disqualified_at' => Carbon::now()]);
            $this->increaseMatchDisqualifiedCount($match);
            DB::commit();
            event(new ParticipantIsDisqualified($match, $participant));
            $this->updateReadyMessageInLobby($match, $participant);
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    /**
     * @param Match|null $match
     */
    private function increaseMatchDisqualifiedCount(?Match $match)
    {
        if (!$match) {
            return;
        }
        if ($match->disqualified_count == 2) {
            return;
        }
        $match->disqualified_count += 1;
        $match->save();
        $readyMatchParticipant = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->whereNotNull('ready_at')
            ->first();
        if ($readyMatchParticipant) {
            $match->winner_team_id = $readyMatchParticipant->participant->id;
            $match->is_forfeit = true;
            $match->save();
            $match->addWinnerToNextMatchForWinners();
        }
        if ($match->disqualified_count == 1) {
            $this->increaseMatchDisqualifiedCount($match->getNextMatchForLoser());
        } else if ($match->disqualified_count == 2) {
            $match->winner_team_id = -1;
            $match->is_forfeit = true;
            $match->save();
            $this->increaseMatchDisqualifiedCount($match->getNextMatchForWinner());
        }
        return;
    }

    private function updateReadyMessageInLobby(Match $match, Participant $participant)
    {
        $otherMatchParticipant = MatchParticipant::query()
            ->where('match_id', $match->id)
            ->where('participant_id', '<>', $participant->id)
            ->whereNotNull('ready_at')
            ->first();
        if (
            $otherMatchParticipant
            && $match->lobby
            && $this->lobbyRepository->getLobbyMessageWithType($match->lobby, 'ready_message')
        ) {
            $disqualifiedCaptain = $participant->getCaptain();
            $this->lobbyRepository->createReadyMessage($match->lobby, $disqualifiedCaptain, true);
        }
    }
}
