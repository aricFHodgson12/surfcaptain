<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserSetting extends Model
{
    //pass the following variables: 'wvht','wind','tide',temp'
    public function units($var)
    {
        $units = UserSettingsUnit::where('id',$this->{$var})->first();
        return $units->unit;
    }

    public function allUnits()
    {

        $units = DB::table('user_settings_units')
            ->whereIn('id',[$this->wvht,$this->wind,$this->tide,$this->temp])
            ->get();

        $allUnits = array();
        foreach ($units as $unit) {
            if ($unit->field == 'wvht')
                $allUnits['wvht_unit'] = $unit->unit;
            else if ($unit->field == 'wind')
                $allUnits['wind_unit'] = $unit->unit;
            if ($unit->field == 'tide')
                $allUnits['tide_unit'] = $unit->unit;
            if ($unit->field == 'temp')
                $allUnits['temp_unit'] = $unit->unit;
        }
        return (object) $allUnits;

        /*
        return DB::table('user_settings')
            ->join('user_settings_units as wvht_units', 'wvht_units.id', '=', 'user_settings.wvht')
            ->join('user_settings_units as wind_units', 'wind_units.id', '=', 'user_settings.wind')
            ->join('user_settings_units as tide_units', 'tide_units.id', '=', 'user_settings.tide')
            ->join('user_settings_units as temp_units', 'temp_units.id', '=', 'user_settings.temp')
            ->select('wvht_units.unit as wvht_unit', 'wind_units.unit as wind_unit', 'tide_units.unit as tide_unit', 'temp_units.unit as temp_unit')
            ->first();
        */
    }
}
