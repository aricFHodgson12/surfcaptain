<?php

namespace App\Console\Commands;

use App\Location;
use App\LocationBeach;
use App\WeatherCaptainApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ModelIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:model-ids {location_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Weather Captain required model ids for sst, nam, gfs';

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
        $locationId = $this->argument('location_id');

        $location = Location::find($locationId);

        //figure out nam model name
        if (substr($location['ww3_bull_name'],0,3) == 'haw')
            $model = 'haw';
        else if (substr($location['ww3_bull_name'],0,3) == 'pri')
            $model = 'pri';
        else
            $model = 'hires';

        $params = array (
            'nearshore_lat' => $location['nearshore_lat'],
            'nearshore_lon' => $location['nearshore_lon'],
            'lat'   => $location['lat'],
            'lon'   => $location['lon'],
            'model' => $model
        );

        $weatherCaptainApi = new WeatherCaptainApi();
        $modelIds = $weatherCaptainApi->getModelIds($params);

        //Log::channel('wc_api')->info('data: '.print_r($modelIds,true));

        if ($modelIds['error'])
            print_r($modelIds['error']);

        if ($modelIds['data']) {

            if (isset($modelIds['data']['sst_ids']))
                $location->sst_ids = $modelIds['data']['sst_ids'];
            if (isset($modelIds['data']['gfs_ids']))
                $location->gfs_ids = $modelIds['data']['gfs_ids'];
            if (isset($modelIds['data']['nam_hires_ids']))
                $location->nam_hires_ids = $modelIds['data']['nam_hires_ids'];
            if (isset($modelIds['data']['nam_conus_ids']))
                $location->nam_conus_ids = $modelIds['data']['nam_conus_ids'];
            if (isset($modelIds['data']['tide_loc_id']))
                $location->tide_loc_id = $modelIds['data']['tide_loc_id'];
            if (isset($modelIds['data']['nws_id']))
                $location->nws_id = $modelIds['data']['nws_id'];

            $location->save();
        }
    }
}
