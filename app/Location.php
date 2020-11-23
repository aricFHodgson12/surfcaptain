<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Location extends Model
{

    public function params()
    {
        return $this->hasMany('App\LocationBeach');
    }

    public function state()
    {
        return $this->belongsTo('App\State');
    }

    public function defaultBeach()
    {
        return $this->hasMany('App\LocationBeach')->orderBy('rank')->first();
    }

    public function closestBeach($lat,$lon) {

        $subquery = DB::table('location_beaches')
            ->select('slug','location_id')
            ->where('rank',1);

        $result = DB::table('locations')
            ->joinSub($subquery,'beaches', function($join) {
                $join->on('locations.id','=','beaches.location_id');
            })
            ->selectRaw("`slug`, (
                3959 *
                acos(cos(radians($lat)) *
                    cos(radians(lat)) *
                    cos(radians(lon) -
                        radians($lon)) +
                    sin(radians($lat)) *
                    sin(radians(lat)))
                ) AS distance"
            )
            ->where('lat','>',((float)$lat - 5))
            ->where('lat','<', ((float)$lat + 5))
            ->where('lon', '>', ((float)$lon - 5))
            ->where('lon', '<',((float)$lon + 5))
            ->orderBy('distance')
            ->limit(1)
            ->first();

        if ($result and isset($result->slug))
            return $result->slug;
        else
            return false;
    }

    public function updateLocationStations($locationId=null)
    {
        if (! $locationId)
            $locationId = $this->id;

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

        $joinSst = DB::table('location_stations')
            ->join('wx_stations', 'wx_stations.station_id','=','location_stations.station_id')
            ->select('location_id','location_stations.station_id')
            ->where('wx_stations.active',1)
            ->where('location_id',$locationId)
            ->where('station_type','sst')
            ->orderBy('rank')
            ->limit(1);

        $joinWave = DB::table('location_stations')
            ->join('wx_stations', 'wx_stations.station_id','=','location_stations.station_id')
            ->select('location_id','location_stations.station_id')
            ->where('wx_stations.active',1)
            ->where('location_id',$locationId)
            ->where('station_type','wave')
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
            ->leftJoinSub($joinSst,'sst', function($join) {
                $join->on('sst.location_id','=','locations.id');
            })
            ->leftJoinSub($joinWave,'wave', function($join) {
                $join->on('wave.location_id','=','locations.id');
            })
            ->select(
                'atmp.station_id as atmpId',
                        'sky.station_id as skyId',
                        'wind.station_id as windId',
                        'sst.station_id as sstId',
                        'wave.station_id as waveId'
            )->where('id',$locationId)
            ->first();


        if ($locationStations->atmpId)
            $this->atmp_station_id = $locationStations->atmpId;
        if ($locationStations->windId)
            $this->wind_station_id = $locationStations->windId;
        if ($locationStations->skyId)
            $this->sky_station_id = $locationStations->skyId;
        if ($locationStations->sstId)
            $this->sst_station_id = $locationStations->sstId;
        if ($locationStations->waveId)
            $this->buoy_station_id = $locationStations->waveId;

        $this->save();

    }
}
