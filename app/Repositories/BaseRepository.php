<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

abstract class BaseRepository
{
    /**
     * @var string $modelClass
     */
    protected $modelClass = null;
    /**
     * @var Model $model
     */
    protected $model = null;

    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Collection
     */
    public function all()
    {
        if ($this->modelClass) {
            return $this->modelClass::all();
        }
        return collect([]);
    }

    /**
     * @param Request $request
     * @param string $key
     * @param string $imagePath
     * @param bool $public
     * @return false|null|string
     */
    protected function saveImageFromRequest(Request $request, string $key, string $imagePath, bool $public = true)
    {
        $path = null;
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $path = Storage::putFile($imagePath, $file, $public);
        }
        return $path;
    }

}