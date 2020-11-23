<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationBeach extends Model
{
    public function location() {
        return $this->belongsTo('App\Location');
    }

    public function forecast()
    {
        return $this->hasOne('App\LocationForecast','location_beach_id');
    }
}
