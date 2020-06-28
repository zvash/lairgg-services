<?php

namespace App\Traits\Seeders;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage as FileSystem;

trait Storage
{
    /**
     * Store a file from a specific path to a destination on a disk.
     *
     * @param  string  $to
     * @param  string  $path
     * @param  string  $disk
     * @return string
     */
    protected function store(string $to, string $path, string $disk = 's3')
    {
        return FileSystem::disk($disk)->putFile($to, new File($path));
    }

    /**
     * get seeder resource path.
     *
     * @param  string  $path
     * @return string
     */
    protected function getSeederPath(string $path)
    {
        return resource_path('seeders/'.$path);
    }
}
