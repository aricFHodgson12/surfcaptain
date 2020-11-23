<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    public function locationBeach()
    {
        return $this->belongsTo('App\LocationBeach','location_beach_id');
    }
}
