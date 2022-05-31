<?php


namespace App\Jobs;


use App\Team;
use App\Tournament;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AddUsersToTeam
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Tournament $tournament
     */
    protected $team;

    protected $playerCount;

    /**
     * @var \Faker\Generator $faker
     */
    private $faker;


    /**
     * Create a new job instance.
     *
     * @param int $temId
     * @param int $playerCount
     */
    public function __construct(int $temId, int $playerCount)
    {
        $this->team = Team::find($temId);
        $this->playerCount = $playerCount;
        $this->faker = Faker::create();
    }

    public function handle()
    {
        if (!$this->team) {
            return;
        }
        for ($j = 1; $j <= $this->playerCount; $j++) {
            $user = $this->createUser();
            //$isCaptain = $j == 1;
            $this->team->players()->attach($user->id, ['captain' => false]);
        }
    }

    private function createUser()
    {
        $attributes = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'username' => str_replace('.', '_', $this->faker->userName()),
            'timezone' => 'Europe/London',
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
