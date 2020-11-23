<?php

namespace App\Http\Controllers;

use App\Location;
use App\LocationBeach;
use App\LocationForecast;
use App\Timezone;
use App\UserSetting;
use App\WeatherCaptainApi;
use App\WxStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SurfController extends Controller
{
    public $weatherCaptainApi;

    public function __construct()
    {
        $this->weatherCaptainApi = new WeatherCaptainApi();
    }

    public function show($slug, Request $request)
    {
        $fcstData = array();

        $beach = LocationBeach::where('slug', $slug)->first();
        $location = $beach->location;
        $state = $location->state;

        Cookie::queue('beach', $slug, 60 * 24 * 365, '', '', false, false);
        Cookie::queue('sc_location', $location->name, 60 * 24 * 365, '', '', false, false);

        $fcstData['beachId'] = $beach->id;
        if ($beach->wind_block != -1) {
            $offshore1Dir = $beach->beach_face_dir - 180;
            if ($offshore1Dir < 0)
                $offshore1Dir = 360 + $offshore1Dir;
            else if ($offshore1Dir > 360)
                $offshore1Dir = $offshore1Dir - 360;
            $offshore1DirText = $this->weatherCaptainApi->dirText($offshore1Dir,true);

            $offshore2Dir = $beach->wind_block - 180;
            if ($offshore2Dir < 0)
                $offshore2Dir = 360 + $offshore2Dir;
            else if ($offshore2Dir > 360)
                $offshore2Dir = $offshore2Dir - 360;
            $offshore2DirText = $this->weatherCaptainApi->dirText($offshore2Dir,true);

            if ($offshore1DirText == $offshore2DirText)
                $fcstData['beachFaceDir'] = $this->weatherCaptainApi->dirText($beach->beach_face_dir,true).' facing beaches';
            else
                $fcstData['beachFaceDir'] = 'beaches with direct offshore winds from ' . $offshore1DirText . ' to ' . $offshore2DirText;

        } else
            $fcstData['beachFaceDir'] = $this->weatherCaptainApi->dirText($beach->beach_face_dir,true).' facing beaches';

        $fcstData['name'] = $location->name;
        $fcstData['slug'] = $slug;
        $title = ($location->title) ? $location->title : $location->name.', '.$state->state_name;
        $fcstData['title'] = ($location->title) ? $location->title : $location->name.', '.$state->state_code;

        $user = ($request->is('api/*')) ? $request->user('api') : $request->user('web');
        $fcstData['loggedIn'] = ($user) ? true : false;
        $forecastDays = ($user) ? (($user->subscribed('pro')) ? 16 : 3) : 3;

        //forecast
        $locationForecast = $beach->forecast;
        $units = $locationForecast->getUnits($user);

        if ($forecastDays == 3)
            $data = json_decode($locationForecast->forecast_3day, true);
        else
            $data = json_decode($locationForecast->forecast, true);

        //Log::info('forecast: '.print_r($locationForecast->forecast_3day,true));

        $data = $locationForecast->convertUnits($data, $units);

        $fcstData['timeline'] = $data['timeline'];
        $fcstData['timeline']['compassMap'] = Storage::disk('s3')->url('compass-map/map'.$location->id.'.png');
        $fcstData['days'] = $data['days'];

        $timezone = Timezone::where('zone_id', $location->zone_id)->currentTimezone()->first();
        $gmtOffset = $timezone->gmt_offset;

        $fcstData['updated'] = date('M j, Y @ g:ia', strtotime($locationForecast->forecast_updated_at) + $gmtOffset);;
        $fcstData['fcst_expires'] = strtotime($locationForecast->forecast_updated_at) + (3600 * 6);

        //weather
        $weather = $this->getWeather($location, $locationForecast, $units);
        $fcstData['nowHour'] = $weather['nowHour'];

        $weather['low_tide'] = $locationForecast->closestLowTide($data['days'][0]['low_am'], $data['days'][0]['low_pm'],$gmtOffset);
        $weather['high_tide'] = $locationForecast->closestHighTide($data['days'][0]['high_am'], $data['days'][0]['high_pm'],$gmtOffset);
        $weather['wetsuit'] = $data['wetsuit'];

        if ($location->sst_station_id)
            $weather['sst_station'] = $location->sst_station_id;

        $weather['sst'] = $this->weatherCaptainApi->convertTemp($data['sst'], $units->temp_unit);

        $fcstData['weather'] = $weather;
        $fcstData['fcst_days'] = $forecastDays;

        if ($request->wantsJson())
            return $fcstData;
        else if (isset($request->isAjax))
            return json_encode($fcstData);
        else
            return view('forecast', ['title' => $title, 'fcstData' => json_encode($fcstData)]);
    }

    public function weather($slug, Request $request)
    {

        //get units
        $user = ($request->is('api/*')) ? $request->user('api') : $request->user('web');
        $units = (new \App\LocationForecast)->getUnits($user);


        //forecast
        $beach = LocationBeach::where('slug',$slug)->first();
        $location = $beach->location;
        $locationForecast = $beach->forecast;

        $weather = $this->getWeather($location,$locationForecast,$units);

        if ($request->wantsJson())
            return $weather;
        else
            return json_encode($weather);
    }

    public function getWeather($location,$locationForecast,$units) {

        $return = array(
            'wvht' => false,
            'wvper' => false,
            'buoy' => false,
            'sky' => false,
            'sky_icon' => false,
            'wind_station' => false,
            'wind_spd' => false,
            'wind_dir' => false,
            'atmp' => false,
            'expires' => false
        );

        $data = json_decode($locationForecast->forecast_3day,true);
        $weather = json_decode($locationForecast->weather,true);

        if (!$weather or time() > $weather['expires'])
            $weather = $locationForecast->getWeather($location);

        $timezone = Timezone::where('zone_id',$location->zone_id)->currentTimezone()->first();
        $gmtOffset = $timezone->gmt_offset;
        $dayStartOffset = (strtotime($data['days'][0]['date']) - $data['timeline']['timestamp']) / 3600;

        $sunriseTime = strToTime($data['days'][0]['sunrise']) - $gmtOffset;
        $sunsetTime = strToTime($data['days'][0]['sunset']) - $gmtOffset;

        //now hour
        $nowHour = strtotime(date('Y-m-d H:00:00')) + $gmtOffset; //timestamp of the now hour
        if (date('i') > 30)
            $nowHour = $nowHour + 3600;

        $nowHour = (in_array(config('app.env'),array('local','dev'))) ? 12 : ($nowHour - $data['timeline']['timestamp']) / 3600;

        $weather['wind_station'] = $location->wind_station_id;
        if (! $weather['wind_dir']) {
            if (preg_match('^([A-Z]{1,3}) <u>([0-9]{1,3})</u>^',$data['timeline']['wind'][$nowHour],$matches)) {
                $weather['wind_dir'] = $matches[1];
                $weather['wind_spd'] = $matches[2];
                $weather['wind_gst'] = false;
                $weather['wind_station'] = false;
            }
        }

        $nowDayIndex = $nowHour - $dayStartOffset;
        $nowDay = 0;
        if ($nowDayIndex > 23) {
            $nowDay = floor($nowDayIndex / 24);
            $nowDayIndex  = $nowDayIndex % 24;
        }

        if (! $weather['atmp'] and preg_match('^<u>(.*)</u>^',$data['days'][$nowDay]['hourly'][$nowDayIndex]['temp'], $matches))
            $weather['atmp'] = $matches[1];

        $weather['buoy'] = $location->buoy_station_id;
        if (! $weather['wvht'] or ! $weather['wvper']) {
            $weather['buoy'] = 'FORECAST';
            if (preg_match('^\) <u>(.*)</u> @ ([0-9]{1,2}) sec^',$data['timeline']['swell1'][$nowHour],$matches)) {

                if (! $weather['wvht'])
                    $weather['wvht'] = $matches[1];
                if (! $weather['wvper'])
                    $weather['wvper'] = $matches[2];
            }
        }

        $weather = $locationForecast->convertWeatherUnits($weather,$units);

        if ($weather['sky'])
            $weather['sky_icon'] = $this->weatherCaptainApi->wxStationSkyToIcon($weather['sky'], $weather['wind_spd'], $sunriseTime, $sunsetTime);
        else if (isset($data['days'][$nowDay]['hourly'][$nowDayIndex]))
            $weather['sky_icon'] = $data['days'][$nowDay]['hourly'][$nowDayIndex]['wxicon'];
        else
            $weather['sky_icon'] = $this->weatherCaptainApi->wxStationSkyToIcon('fair', $weather['wind_spd'], $sunriseTime, $sunsetTime);

        foreach ($return as $k => $v) {
            if ($weather[$k])
                $return[$k] = $weather[$k];
        }

        //now conditions
        $return['nowHour'] = $nowHour;
        $return['nowSurf'] = $this->weatherCaptainApi->surfIntegerToText($data['timeline']['wvhtText'][$nowHour]);
        $return['nowCond'] = $data['timeline']['condText'][$nowHour];
        $return['nowCondDesc'] = $data['timeline']['condDesc'][$nowHour];

        return $return;
    }

    public function nearbyBeach($lat, $lon)
    {

        $return = array(
            'errorMsg' => false,
            'beach' => false
        );

        if (! $return['beach'] = (new \App\Location)->closestBeach($lat, $lon))
            $return['errorMsg'] = 'There are no beaches nearby';

        return $return;
    }

    public function getTimeline($slug, Request $request, $days = 3) {

        $return = array(
            'errorMsg' => false,
            'graphSvg' => false,
            'baseGraph' => false,
            'locName' => false
        );

        $user = $request->user('api');
        $locationForecast = new \App\LocationForecast;
        $units = $locationForecast->getUnits($user);

        $forecastField = ($days == 3) ? 'forecast_3day' : 'forecast';

        $forecast = DB::table('location_beaches as beach')
            ->join('location_forecasts as forecast','forecast.location_beach_id','=','beach.id')
            ->join('locations as loc','loc.id','=','beach.location_id')
            ->where('beach.slug','=',$slug)
            ->select('forecast.'.$forecastField,'loc.name')
            ->first();

        if ($forecast) {
            $return['locName'] = $forecast->name;
            $return['slug'] = $slug;

            $data = json_decode($forecast->forecast_3day, true);
            $data = $locationForecast->convertUnits($data, $units);

            $timeline = $data['timeline'];
            $return['baseGraph'] = $timeline['baseGraph'];
            $return['graphSvg'] = $timeline['surfSvg'];

        } else
            $return['errorMsg'] = 'There was a problem fetching Forecast Timeline';

        return $return;

    }

    public function nearbyTimeline($lat, $lon, Request $request)
    {

        $nearbyBeach = $this->nearbyBeach($lat,$lon);

        if ($nearbyBeach and $slug = $nearbyBeach['beach']) {
            return $this->getTimeline($slug, $request);
        } else
            return array('errorMsg' => 'Could not find a nearby Beach Location');
    }
}
