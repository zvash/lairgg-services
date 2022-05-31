<?php

namespace App\Nova\Actions;

use App\Repositories\MatchRepository;
use App\Repositories\TournamentRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class AutoWinRestMatches extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @return string
     */
    public function name()
    {
        return 'Submit Auto-Wins';
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $tournamentRepository = new TournamentRepository();
        $matchRepository = new MatchRepository();
        $tournament = $models->all()[0];
        $autoWinMatchIds = $tournamentRepository->submitAutoWins($tournament, $matchRepository);
        if ($autoWinMatchIds) {
            $autoWinCount = count($autoWinMatchIds);
            if ($autoWinCount == 1) {
                return Action::message("In one match, the winner was determined by auto-win.");
            }
            return Action::message("In {$autoWinCount} matches, the winners were determined by auto-wins.");
        }
        return Action::danger('Auto-winning was not available in any of the matches.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
