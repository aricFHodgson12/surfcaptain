<?php

namespace App\Console\Commands;

use App\LocationStation;
use App\WeatherCaptainApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WeatherCurrent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:weather {location_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch current weather from Weather Captain API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $weatherCaptainApi = new WeatherCaptainApi();

        $locationId = $this->argument('location_id');

        $joinAtmp = DB::table('location_stations')
            ->join('wx_stations', 'wx_stations.station_id','=','location_stations.station_id')
            ->select('location_id','location_stations.station_id')
            ->where('wx_stations.active',1)
            ->where('location_id',$locationId)
            ->where('station_type','atmp')
            ->orderBy('rank')
            ->limit(1);

        $joinSky = DB::table('location_stations')
            ->join('wx_stations', 'wx_stations.station_id','=','location_stations.station_id')
            ->select('location_id','location_stations.station_id')
            ->where('wx_stations.active',1)
            ->where('location_id',$locationId)
            ->where('station_type','sky')
            ->orderBy('rank')
            ->limit(1);

        $joinWind = DB::table('location_stations')
            ->join('wx_stations', 'wx_stations.station_id','=','location_stations.station_id')
            ->select('location_id','location_stations.station_id')
            ->where('wx_stations.active',1)
            ->where('location_id',$locationId)
            ->where('station_type','wind')
            ->orderBy('rank')
            ->limit(1);

        $locationStations = DB::table('locations')
            ->leftJoinSub($joinAtmp,'atmp', function($join) {
                $join->on('atmp.location_id','=','locations.id');
            })
            ->leftJoinSub($joinSky,'sky', function($join) {
                $join->on('sky.location_id','=','locations.id');
            })
            ->leftJoinSub($joinWind,'wind', function($join) {
                $join->on('wind.location_id','=','locations.id');
            })
            ->select('atmp.station_id as atmpId', 'sky.station_id as skyId', 'wind.station_id as windId')
            ->where('id',$locationId)
            ->toSql();

        die($locationStations);

        $atmpIds = '';
        $windIds = '';
        $skyIds = '';
        foreach ($locationStations as $station) {
            if ($station->station_type == 'atmp')
                $atmpIds = $atmpIds .(($atmpIds) ? ',' : '').$station->station_id;
            else if ($station->station_type == 'wind')
                $windIds = $windIds .(($windIds) ? ',' : '').$station->station_id;
            else if ($station->station_type == 'sky')
                $skyIds = $skyIds .(($skyIds) ? ',' : '').$station->station_id;
        }

        $weather = $weatherCaptainApi->getCurrentWeather($atmpIds,$windIds,$skyIds);
    }
}
