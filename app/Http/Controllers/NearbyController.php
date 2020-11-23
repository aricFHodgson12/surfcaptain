<?php

namespace App\Http\Controllers;

use App\Local;
use App\LocationBeach;
use App\LocationForecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NearbyController extends Controller
{
    /**
     * Show the nearby view
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        //get title of local region, if beach cookie exists
        if ($slug = $request->cookie('beach'))
            $local = $this->getLocalAreaBySlug($slug);

        $localName = (isset($local)) ? $local->name.' ' : '';
        return view('nearby', ['localName' => $localName]);
    }

    /**
     * @param Request $request
     *
     */

    public function getLocations(Request $request)
    {

        $return = array(
            'locations' => array(),
            'map' => array(),
            'errorMsg' => false
        );

        $sort = $request->sort;

        if ($request->minLat) {

            //subtract 10% buffer, so you dont have locations right on the borders
            $latBuff = abs($request->maxLat - $request->minLat) * .1;
            $lonBuff = abs($request->maxLon - $request->minLon) * .1;

            $minLat = $request->minLat + $latBuff;
            $maxLat = $request->maxLat - $latBuff;
            $minLon = $request->minLon + $lonBuff;
            $maxLon = $request->maxLon - $lonBuff;

        } else {

            $localModel = (new \App\Local);

            //fetch min/max lat/lon based on local area of last visited forecast location
            if ($slug = $request->cookie('beach')) {
                $local = $this->getLocalAreaBySlug($slug);

            } else
                //if no cookied location, use Outer Banks as default local area
                $local = Local::find(6);

            $minMaxLatLon = $localModel->minMaxLatLon($local->id);
            $latBuff = ($minMaxLatLon->maxlat - $minMaxLatLon->minlat) * .25;
            $lonBuff = ($minMaxLatLon->maxlon - $minMaxLatLon->minlon) * .25;

            $minLat = $minMaxLatLon->minlat - $latBuff;
            $maxLat = $minMaxLatLon->maxlat + $latBuff;
            $minLon = $minMaxLatLon->minlon - $lonBuff;
            $maxLon = $minMaxLatLon->maxlon + $lonBuff;

            //get lat lon of slug location
            $lastLocation = DB::table('locations')
                ->join('location_beaches as beach','beach.location_id','=','locations.id')
                ->where('beach.slug',$slug)
                ->select('locations.lat','locations.lon')
                ->first();
        }

        $return['map']['minLat'] = $minLat;
        $return['map']['minLon'] = $minLon;
        $return['map']['maxLat'] = $maxLat;
        $return['map']['maxLon'] = $maxLon;

        //get locations and current day forecast for locations inside min/max lat/lon
        $locations = DB::table('locations as loc')
            ->join('location_beaches as beach','beach.location_id','=','loc.id')
            ->leftJoin('states','states.id','=','loc.state_id')
            ->where('loc.active',1)
            ->where('beach.active',1)
            ->where('beach.rank',1)
            ->whereBetween('lat',[$minLat, $maxLat])
            ->whereBetween('lon',[$minLon, $maxLon])
            ->select('beach.id as beach_id','beach.slug','loc.name','states.state_code','loc.lat','loc.lon')
            ->limit(100);

        if (isset($lastLocation))
            $locations->orderByRaw("(POW((lon - $lastLocation->lon),2) + POW((lat-$lastLocation->lat),2))");
        else
            $locations->orderBy('loc.rank');

        //Log::info('location sql: '.$locations->toSql());

        $locations = $locations->get();

        $user = $request->user('api');
        $units = (new LocationForecast())->getUnits($user);

        foreach ($locations as $location) {

            $forecast = LocationForecast::where('location_beach_id',$location->beach_id)->first();
            $today = $forecast->todaysSummary($units);
            $locationArray = array(
                'locationName' => $location->name.(($location->state_code)?', '.$location->state_code:''),
                'locationBeachId' => $location->beach_id,
                'locationBeachSlug' => $location->slug,
                'locationLat' => $location->lat,
                'locationLon' => $location->lon
            );
            $return['locations'][] = array_merge($today,$locationArray);
        }

        if ($sort != 'geog') {
            if ($sort == 'surf') {
                $surf = array_column($return['locations'], 'surfInt');
                array_multisort($surf, SORT_DESC, $return['locations']);
            } else if ($sort == 'cond') {
                $cond = array_column($return['locations'], 'condInt');
                array_multisort($cond, SORT_ASC, $return['locations']);
            }
        }

        return response($return);
    }

    public function getLocalAreaBySlug($slug) {
        $local = DB::table('location_beaches as beach')
            ->join('locations as loc','loc.id','=','beach.location_id')
            ->join('locals','locals.id','=','loc.local_id')
            ->select('locals.*')
            ->where('beach.slug',$slug)
            ->first();

        return $local;
    }
}
