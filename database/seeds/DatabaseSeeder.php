<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->purge();

        // Mass assignment protection is automatically
        // Disabled during database seeding.
        $this->call([
            LinkTypeSeeder::class,
            GenderSeeder::class,
            StudioSeeder::class,
            GameTypeSeeder::class,
            GameSeeder::class,
            MapSeeder::class,
            UserSeeder::class,
        ]);
    }

    /**
     * Purge all directories.
     *
     * @return void
     */
    private function purge()
    {
        Storage::disk('s3')->flushCache();

        foreach (Storage::disk('s3')->directories() as $directory) {
            Storage::disk('s3')->deleteDirectory($directory);
        }
    }
}
