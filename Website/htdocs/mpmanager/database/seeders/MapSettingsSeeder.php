<?php

namespace Database\Seeders;

use App\Models\MapSettings;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::connection('shard')->table('map_settings')->insert([
            'zoom' => 7,
            'latitude' => -2.500380,
            'longitude' => 32.889060,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

    }
}
