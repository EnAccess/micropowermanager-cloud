<?php

namespace App\Services;

use App\Models\Plugins;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PluginsService implements IBaseService
{
    public function __construct(private Plugins $plugin)
    {
    }



    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function create($pluginData)
    {
       return $this->plugin->newQuery()->create($pluginData);
    }

    public function update($model, $data)
    {
        // TODO: Implement update() method.
    }

    public function delete($model)
    {
        // TODO: Implement delete() method.
    }

    public function getAll($limit = null)
    {
        // TODO: Implement getAll() method.
    }
}
