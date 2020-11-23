<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderAccount extends Model
{
    protected $fillable = ['user_id', 'provider_id', 'provider'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
