<?php

namespace App\Console\Commands;

use App\Location;
use App\LocationForecast;
use App\Notifications\ApiError;
use App\User;
use App\WeatherCaptainApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class UpdateForecast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:forecast {zone_id} {location_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update forecast from the Weather Captain API';

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
        $zoneId = $this->argument('zone_id');
        $locationId = $this->argument('location_id');

        //get all location data for locations in this zone
        //$locations = Location::where('zone_id',$zoneId)->where('active',1)->where('id',1)->get();
        $locations = Location::where('zone_id',$zoneId)->where('active',1);
        if ($locationId)
            $locations = $locations->where('id',$locationId);

        $locations = $locations->get();

        foreach ($locations as $k => $location) {

            $params = $location->params->toArray(); //can be more than 1 for multiple beach facing directions

            foreach ($params as $j => $param) {

                $locData[] = array(
                    'id' => $param['id'],
                    'zone_id' => $location['zone_id'],
                    'nws_id' => $location['nws_id'],
                    'sst_ids' => $location['sst_ids'],
                    'nam_hires_ids' => $location['nam_hires_ids'],
                    'nam_conus_ids' => $location['nam_conus_ids'],
                    'gfs_ids' => $location['gfs_ids'],
                    'tide_loc_id' => $location['tide_loc_id'],
                    'lat' => $location['lat'],
                    'lon' => $location['lon'],
                    'bullName' => $location['ww3_bull_name'],
                    'bathymetry' => $location['bathymetry'],
                    'buoy_id' => ($location['buoy_station_id']) ? $location['buoy_station_id'] : '',
                    'sst_station_id' => ($location['sst_station_id']) ? $location['sst_station_id'] : '',
                    'beach_face_dir' => $param['beach_face_dir'],
                    'wind_block' => $param['wind_block'],
                    'minDir' => $param['swell_window_left'],
                    'maxDir' => $param['swell_window_right']
                );
            }
        }

        if (! $locData)
            return false;

        $weatherCaptainApi = new WeatherCaptainApi();
        $return = $weatherCaptainApi->getForecast($locData);

        if ($return['error']) {
            $this->sendNotification($return['error']);

        } else if ($return['data']) {

            //update each location
            foreach ($return['data'] as $k => $loc) {

                if (!$locationForecast = LocationForecast::where('location_beach_id', $loc['id'])->first()) {
                    $locationForecast = new LocationForecast;
                    $locationForecast->location_beach_id = $loc['id'];
                }

                try {
                    list($forecast,$forecast3) = $locationForecast->fcstData($loc);
                    $locationForecast->forecast = $forecast;
                    $locationForecast->forecast_3day = $forecast3;
                    $locationForecast->forecast_updated_at = date('Y-m-d H:i:s');
                    $locationForecast->save();
                } catch(\Exception $e) {
                    $this->sendNotification('loc beach id: '.$loc['id'].' '.$e->getMessage());
                }
            }
        }
        return true;
    }

    public function sendNotification($error) {
        log::channel('wc_api')->error($error);
        $users = User::where('role_id',1)->get();
        $notification = array(
            'subject' => 'Update Forecast API error',
            'error' => $error
        );
        Notification::send($users, new ApiError($notification));
    }
}
