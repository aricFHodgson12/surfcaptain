<?php

namespace App\Http\Controllers;

use App\Subscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StatsController extends Controller
{
    public function show(Request $request)
    {
        $return = array(
            'errorMsg' => false,
            'userCount' => false,
            'usersSubscribed' => false,
            'usersToday' => false,
            'subscribersToday' => false
        );

        $return['userCount'] = User::count();
        $return['usersSubscribed'] = Subscription::whereIn('stripe_status',['active','trialing'])->count();
        $return['usersToday'] = User::where('created_at','>=',date('Y-m-d'))->count();
        $return['subscribersToday'] = Subscription::where('created_at','>=',date('Y-m-d 00:00:00'))->count();

        return $return;
    }
}
