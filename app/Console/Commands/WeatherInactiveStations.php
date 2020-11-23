<?php

namespace App\Console\Commands;

use App\Location;
use App\LocationStation;
use App\Notifications\ApiError;
use App\User;
use App\WeatherCaptainApi;
use App\WxStation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class WeatherInactiveStations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:weather-stations-inactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab only inactive stations to update database active status.';

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

        $stations = $WCAPI->getInactiveWeatherStations();

        if ($stations['error']) {

            log::channel('wc_api')->error(print_r($stations['error'],true));

            $users = User::where('role_id',1)->get();
            $notification = array(
                'subject' => 'Weather Inactive Stations API error',
                'error' => $stations['error']
            );
            Notification::send($users, new ApiError($notification));

        } else if ($stations['data']) {

            $inactiveStations = array();
            foreach ($stations['data'] as $station)
                $inactiveStations[] = $station['station_id'];

            //grab all stations and change status if need be
            $WxStations = WxStation::all();

            $updatedStations = array();
            foreach ($WxStations as $station) {
                if ($station->active and in_array($station->station_id,$inactiveStations)) {
                    $station->active = 0;
                    $station->save();
                    $updatedStations[] = $station->station_id;
                } else if ($station->active == 0 and !in_array($station->station_id,$inactiveStations)) {
                    $station->active = 1;
                    $station->save();
                    $updatedStations[] = $station->station_id;
                }
            }
            if ($updatedStations) {
                //find locations associated with updated stations, and run update to find ids
                $locationStations = LocationStation::distinct()->whereIn('station_id',$updatedStations)->get('location_id');
                foreach ($locationStations as $locationStation)
                    Location::find($locationStation->location_id)->updateLocationStations();
            }
        }

        return true;
    }
}
