<?php

namespace App\Jobs;

use App\Team;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CreateTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Faker\Generator $faker
     */
    private $faker;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = Faker::create();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $captains = [5, 50, 51, 75];
        //for ($i = 1; $i <= 4; $i++) {
        while (count($captains)) {
            $captainId = array_shift($captains);
            $isCaptain = true;
            $team = $this->createTeam(1);
            for ($j = 1; $j <= 5; $j++) {
                if ($isCaptain) {
                    $user = User::find($captainId);
                    $team->players()->attach($user->id, ['captain' => $isCaptain]);
                    $isCaptain = false;
                } else {
                    $user = $this->createUser();
                    $team->players()->attach($user->id, ['captain' => false]);
                }
            }
        }

        //}
//        $teams = Team::whereNull('logo')->get();
//        foreach ($teams as $team) {
//            $logo = $this->saveImage('teams/logos', 400, 400);
//            $cover = $this->saveImage('teams/covers', 640, 400);
//            $team->logo = $logo;
//            $team->cover = $cover;
//            $team->save();
//        }
//        $users = User::whereNull('avatar')->get();
//        foreach ($users as $user) {
//            $user->avatar = $this->saveImage('users/avatars', 400, 400);
//            $user->cover = $this->saveImage('users/covers', 640, 480);
//            $user->save();
//        }
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
