<?php

namespace App\Console\Commands;

use App\Location;
use App\Notifications\ApiError;
use App\User;
use App\WeatherCaptainApi;
use App\WxStation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WeatherStations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:weather-stations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Weather Stations from Weather Captain API and add to DB';

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
        $WCAPI = new WeatherCaptainApi();

        $allStations = WxStation::all();
        $stationIds = array();
        foreach ($allStations as $station)
            $stationIds[] = $station->station_id;

        $stations = $WCAPI->getWeatherStations();

        if ($stations['error']) {

            log::channel('wc_api')->error(print_r($stations['error'],true));

            $users = User::where('role_id',1)->get();
            $notification = array(
                'subject' => 'Weather Stations API error',
                'error' => $stations['error']
            );
            Notification::send($users, new ApiError($notification));

        } else if ($stations['data']) {

            foreach ($stations['data'] as $station) {
                $stationId = $station['station_id'];
                $active = $station['active'];

                if (in_array($stationId, $stationIds)) {
                    $WxStation = WxStation::where('station_id', $stationId)->first();
                    $WxStation->active = $active;
                } else {
                    $WxStation = new WxStation();
                    $WxStation->station_id = $stationId;
                    $WxStation->active = $active;
                }

                $WxStation->save();
            }

            //(new \App\Location)->updateLocationStations();
        }
    }
}
