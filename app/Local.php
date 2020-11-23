<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Local extends Model
{

    /**
     * Get the Area that this Local Area belongs to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function areas() {
        return $this->belongsTo('App\Area');
    }

    public function minMaxLatLon($id=null) {

        $id = ($id) ? $id : $this->id;

        return DB::table('locations')
            ->selectRaw('MIN(lat) as minlat, MAX(lat) as maxlat, MIN(lon) as minlon, MAX(lon) as maxlon')
            ->where('local_id',$id)
            ->first();
    }
}
