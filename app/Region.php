<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /**
     * Get all the areas that belong to this Region
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function areas() {
        return $this->hasMany('App\Area');
    }
}
