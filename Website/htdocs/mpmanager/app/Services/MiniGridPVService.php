<?php

namespace App\Services;

use App\Http\Resources\ApiResource;
use App\Models\PV;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MiniGridPVService
{
    public function __construct(private PV $pv)
    {
    }

    public function getById($miniGridId, $startDate, $endDate): Collection|array
    {
        $pvReadings = $this->pv->newQuery()
            ->where('mini_grid_id', $miniGridId);

        if ($startDate) {
            $pvReadings->where(
                'reading_date',
                '>=',
                Carbon::createFromTimestamp($startDate)->format('Y-m-d H:i:s')
            );
        }

        if ($endDate) {
            $pvReadings->where(
                'reading_date',
                '<=',
                Carbon::createFromTimestamp($endDate)->format('Y-m-d H:i:s')
            );
        }

        return $pvReadings->get();
    }

    public function create($pvData): ApiResource
    {
        return $this->pv->newQuery()->create($pvData);
    }
}
