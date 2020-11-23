<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timezone extends Model
{
    protected $table = 'timezone';
    protected $primaryKey = 'zone_id';
    public $timestamps = false;


    public function scopeCurrentTimezone($query)
    {
        return $query->where('time_start','<=',time())->orderBy('time_start','desc');
    }
}
