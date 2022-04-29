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

    protected $count;
    /**
     * @var \Faker\Generator $faker
     */
    private $faker;

    /**
     * Create a new job instance.
     *
     * @param int $count
     */
    public function __construct(int $count)
    {
        $this->faker = Faker::create();
        $this->count = $count;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        for ($i = 1; $i <= 8; $i++) {
//            $team = $this->createTeam(1);
//            for ($j = 1; $j <= 5; $j++) {
//                $user = $this->createUser();
//                $isCaptain = $j == 1;
//                $team->players()->attach($user->id, ['captain' => $isCaptain]);
//            }
//        }
        for ($i = 1; $i <= $this->count; $i++) {
            $this->createUser();
        }
        $users = User::whereNull('avatar')->get();
        foreach ($users as $user) {
            $user->avatar = $this->saveImage('users/avatars', 400, 400);
            $user->cover = $this->saveImage('users/covers', 640, 480);
            $user->save();
        }
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
