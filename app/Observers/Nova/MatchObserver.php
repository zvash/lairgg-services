<?php

namespace App\Observers\Nova;

use App\{
    Match,
    Party,
    Play
};

class MatchObserver
{
    /**
     * Handle the match "saved" event.
     *
     * @param  \App\Match  $match
     * @return void
     */
    public function saved(Match $match)
    {
        if (! $difference = $this->difference($match)) {
            return;
        }

        $this->sync($match, $difference);
    }

    /**
     * Find the difference between requested play count and current play count.
     *
     * @param  \App\Match  $match
     * @return int
     */
    private function difference(Match $match)
    {
        return $match->play_count - $match->plays->count();
    }

    /**
     * Sync plays models and create or delete base on play count value.
     *
     * @param  \App\Match  $match
     * @param  int  $difference
     * @return mixed
     */
    private function sync(Match $match, int $difference)
    {
        if ($difference < 0) {
            return $match->plays()->oldest()->take(abs($difference))->delete();
        }

        for ($i = 0; $i < $difference; $i++) {
            $match->plays()->save(new Play)->parties()->saveMany([
                new Party,
                new Party,
            ]);
        }
    }
}
