<?php

namespace App\Http\Controllers;

use App\LocationForecast;
use App\User;
use App\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function today(Request $request) {
        $return = array(
            'locations' => array(),
            'errorMsg' => false
        );

        $user = $request->user('api');

        $favorites = UserFavorite::where('user_id',$user->id)
            ->join('location_beaches','location_beaches.id','=','user_favorites.location_beach_id')
            ->join('locations','locations.id','=','location_beaches.location_id')
            ->leftJoin('states','states.id','=','locations.state_id')
            ->select('user_favorites.id','user_favorites.location_beach_id','location_beaches.slug','locations.name','states.state_code')
            ->get();

        $units = (new \App\LocationForecast())->getUnits($user);
        foreach ($favorites as $favorite) {
            $forecast = LocationForecast::where('location_beach_id',$favorite->location_beach_id)->first();
            $today = $forecast->todaysSummary($units);
            $location = array(
                'id' => $favorite->id,
                'locationName' => $favorite->name.(($favorite->state_code)?', '.$favorite->state_code:''),
                'locationBeachId' => $favorite->location_beach_id,
                'locationBeachSlug' => $favorite->slug
            );
            $return['locations'][] = array_merge($today,$location);
        }

        return response($return);
    }

    public function isFavorite($location_beach_id) {
        $return = array(
            'isFavorite' => false,
            'errorMsg' => false
        );

        $user_id = Auth::id();
        if (UserFavorite::where('user_id',$user_id)->where('location_beach_id',$location_beach_id)->exists())
            $return['isFavorite'] = true;

        return response($return);
    }

    public function removeFavorite($location_beach_id) {
        $return = array(
            'success' => false,
            'errorMsg' => false
        );
        $user_id = Auth::id();

        if (UserFavorite::where('user_id',$user_id)->where('location_beach_id',$location_beach_id)->delete())
            $return['success'] = true;

        return response($return);
    }

    public function addFavorite($location_beach_id) {
        $return = array(
            'success' => false,
            'errorMsg' => false
        );
        $user_id = Auth::id();

        $userFavorite = new UserFavorite();
        $userFavorite->user_id = $user_id;
        $userFavorite->location_beach_id = $location_beach_id;
        if ($userFavorite->save())
            $return['success'] = true;

        return response($return);
    }
}
