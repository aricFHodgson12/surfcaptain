<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomPasswordReset;
use App\CaptainSubscriptionBuilder;
use Laravel\Cashier\Billable;

class User extends \TCG\Voyager\Models\User implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_subscribed'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function newSubscription($name, $plans)
    {
        return new CaptainSubscriptionBuilder($this, $name, $plans);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return 'https://hooks.slack.com/services/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';
    }

    public function settings() {
        return $this->hasOne('App\UserSetting');
    }

    public function roleName() {
        return ($role = Role::where('id',$this->role_id)->first()) ? $role->name : false;
    }

    public function addUserSettings($settings = array()) {
        $UserSetting = new UserSetting();
        $UserSetting->user_id = $this->id;
        if ($settings) {
            if (isset($settings['wvht']))
                $UserSetting->wvht = $settings['wvht'];
            if (isset($settings['wind']))
                $UserSetting->wind = $settings['wind'];
            if (isset($settings['tide']))
                $UserSetting->tide = $settings['tide'];
            if (isset($settings['temp']))
                $UserSetting->temp = $settings['temp'];
        }
        $UserSetting->save();
    }

    public function getSubscription()
    {
        return Subscription::where('user_id',$this->id)->latest()->first();
    }

    public function emailSubscribe($isSubscribed)
    {
        $this->is_subscribed = ($isSubscribed) ? true : false;
        $this->save();

        $CM = new CampaignMonitor();
        if ($isSubscribed) {
            if (!$CM->addRecord($data = array('email' => $this->email)))
                return false;
        } else if ($emailSubscriber = EmailSubscribe::where('email',$this->email)->first()) {
            if (! $CM->unsubscribe($this->email))
                return false;
        }
        return true;
    }
}
