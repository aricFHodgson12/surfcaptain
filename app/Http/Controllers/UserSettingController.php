<?php

namespace App\Http\Controllers;

use App\Subscription;
use App\User;
use App\UserFavorite;
use App\UserSetting;
use App\UserSettingsUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserSettingController extends Controller
{

    protected $userId;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->userId = Auth::id();
            return $next($request);
        });
    }

    public function units()
    {
        $userSettingsUnits = UserSettingsUnit::all();
        //reorganize Settings by field
        foreach ($userSettingsUnits as $userSettingsUnit) {
            $showToolTip = ($userSettingsUnit->field != 'wind') ? true : false;
            $units[$userSettingsUnit->field][] = array(
                'id' => $userSettingsUnit->id,
                'name' => (($showToolTip)?'<span data-tooltip tabindex="1" title="'.$userSettingsUnit->name.'">':'').$userSettingsUnit->form_name.(($showToolTip)?'</span>':''),
                'code' => $userSettingsUnit->code
            );
        }

        //add user subscription
        $subscription = Subscription::where('user_id',$this->userId)->orderBy('id','desc')->first();
        $userSubscription = ($subscription and $subscription->name == 'pro') ? 'Pro' : 'Basic';

        return array(
            'units' => $units,
            'userSettings' => UserSetting::where('user_id',$this->userId)->first(),
            'userSubscription' => $userSubscription
        );

    }

    public function update(Request $request)
    {
        $return = array(
            'success' => false,
            'errorMsg' => 'There was a problem updating the setting'
        );

        if ($UserSetting = UserSetting::where('user_id',$this->userId)->first()) {
            $UserSetting->{$request->setting} = $request->value;
            $UserSetting->save();
            $return['success'] = true;
        }

        return $return;
    }

    public function confirmPassword(Request $request)
    {
        $return = array(
            'errorMsg' => false
        );

        $user = $request->user('api');

        $hasher = app('hash');
        if (! $hasher->check($request->password, $user->password))
            $return['errorMsg'] = 'The password is not correct';

        return $return;
    }

    public function deleteUser(Request $request)
    {
        $return  = array(
            'errorMsg' => false
        );

        $user = $request->user('api');

        //delete favorites
        UserFavorite::where('user_id',$user->id)->delete();

        //delete subscription
        Subscription::where('user_id',$user->id)->delete();

        //user settings
        UserSetting::where('user_id',$user->id)->delete();

        //delete user
        if (!$user->delete())
            $return['errorMsg'] = 'There was a problem deleting your account';

        return $return;


    }
}
