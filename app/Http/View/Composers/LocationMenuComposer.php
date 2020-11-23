<?php

namespace App\Http\View\Composers;

use App\Area;
use App\Local;
use App\Region;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LocationMenuComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        //grab data to build navigation menu
        $regions = Region::where('active',1)->orderBy('rank')->get();
        $areas = Area::where('active',1)->orderBy('rank')->get();
        $locals = Local::where('active',1)->orderBy('rank')->get();

        if ($beach = Cookie::get('beach')) {
            $localId = DB::table('locations')
                ->join('location_beaches', 'location_beaches.location_id', '=', 'locations.id')
                ->where('location_beaches.slug', $beach)
                ->select('locations.local_id')
                ->first();
            $view->with('localIdActive', $localId->local_id);
        }

        $locations = DB::table('locations')
            ->join('location_beaches','locations.id','=','location_beaches.location_id')
            ->join('states', 'states.id','=','locations.state_id')
            ->select('locations.id','locations.local_id','locations.name','locations.title','location_beaches.id as beach_id','location_beaches.slug','states.state_code')
            ->where('locations.active',1)
            ->where('location_beaches.active',1)
            ->where('location_beaches.rank',1)
            ->orderBy('locations.rank');

        $locations = $locations->get();

        $menu = array();
        foreach ($regions as $region) {

            $regionAreas = array();
            foreach ($areas as $area) {

                $areaLocals = array();
                foreach ($locals as $local) {
                    if ($local->area_id == $area->id) {

                        $localLocations = array();
                        foreach ($locations as $location) {
                            if ($location->local_id == $local->id) {
                                $localLocations[] = array(
                                    'location_name' => ($location->title) ? $location->title : $location->name.', '.$location->state_code,
                                    'slug' => $location->slug
                                );
                            }
                        }

                        $areaLocals[] = array(
                            'local_id' => $local->id,
                            'local_name' => $local->name,
                            'locations' => $localLocations
                        );
                    }
                }

                if ($area->region_id == $region->id) {
                    $regionAreas[] = array(
                        'area_id' => $area->id,
                        'area_name' => $area->name,
                        'locals' => $areaLocals,
                        'nlocals' => count($areaLocals)
                    );
                }
            }

            $menu[] = array(
                'region_id' => $region->id,
                'region_name' => $region->name,
                'areas' => $regionAreas
            );
        }

        $view->with('locationMenu', $menu);
    }
}
