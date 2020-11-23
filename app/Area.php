<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    /**
     * Get the region that belongs to this Area
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function region() {
        return $this->belongsTo('App\Region');
    }

    /**
     * Get the local areas that belong to this Area
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function locals() {
        return $this->hasMany('App\Local');
    }
}
