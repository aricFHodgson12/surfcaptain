<?php

namespace App\Http\Controllers;

use App\CampaignMonitor;
use App\EmailSubscribe;
use App\Notifications\Subscriber;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PharIo\Manifest\Email;

class EmailSubscribeController extends Controller
{
    public function subscribe(Request $request)
    {

        $return = array(
            'valid' => true,
            'errorMsg' => false
        );

        //check to see if email is already in database
        if (! EmailSubscribe::where('email',$request->email)->first()) {

            $subscribe = EmailSubscribe::create($request->validate([
                'email' => 'bail|required|email'
            ]));

            try {
                $users = User::where('role_id', 1)->get();
                $notification = array(
                    'type' => 'email',
                    'email' => $subscribe->email
                );
                Notification::send($users, new Subscriber($notification));

            } catch (\Exception $e) {
                $return['valid'] = false;
                $return['errorMsg'] = 'There was a problem sending the email notification.';
            }

            //add to Campaign Monitor
            $CM = new CampaignMonitor();
            if (!$CM->addRecord($data = array('email' => $subscribe->email))) {
                $return['valid'] = false;
                $return['errorMsg'] = 'There was a problem saving the email.';
            }

            //if this is a registered user, make sure, is_subscribed is set to true
            if ($user = User::where('email',$request->email)->first()) {
                if (! $user->is_subscribed) {
                    $user->is_subscribed = true;
                    $user->save();
                }
            }

        }

        echo json_encode($return);
    }

    /** This is for subscribing a registered user.
     * @param Request $request
     * @return array
     */
    public function userSubscribe(request $request)
    {
        $return = array(
            'errorMsg' => false
        );

        $user = $request->user('api');

        //subscribe to email list
        $CM = new CampaignMonitor();

        if ($request->subscribe == true) {

            if (!$CM->addRecord($data = array('email' => $user->email))) {
                $return['errorMsg'] = 'There was a problem saving to email list.';
            } else {
                $user->is_subscribed = true;
                $user->save();
            }

        } else {

            if (!$CM->unsubscribe($user->email)) {
                $return['errorMsg'] = 'There was a problem un-subscribing from email list';
            } else {
                $user->is_subscribed = false;
                $user->save();
            }

            if ($emailSubscribe = EmailSubscribe::where('email',$user->email)
                ->whereNull('unsubscribe_date')
                ->first()
            ) {
                $emailSubscribe->unsubscribe_date = Carbon::now();
                $emailSubscribe->save();
            }
        }

        return $return;
    }

    /* example of Campaign Monitor json webhook response

    "Events": [
        {
            "CustomFields": [],
            "Date": "2010-12-14 11:32:00",
            "EmailAddress": "testone@example.org",
            "Name": "Test One Subscriber",
            "SignupIPAddress": "TypeManual",
            "Type": "Subscribe"
        },
        {
            "CustomFields": [],
            "Date": "2010-12-14 11:32:00",
            "EmailAddress": "testtwo@example.org",
            "Name": "Test Two Subscriber",
            "SignupIPAddress": "TypeManual",
            "Type": "Subscribe"
          }
        ],
    "ListID": "96c0bbdaa54760c8d9e62a2b7ffa2e13"
    */

    public function subscribeWebhook(Request $request) {
        $json = json_decode($request,true);

        if (isset($json['Events'])) {
            foreach ($json['Events'] as $event) {
                if (!User::where('email', $event['EmailAddress'])->first() and
                    !EmailSubscribe::where('email', $event['EmailAddress'])->first()
                ) {
                    $emailSubscribe = new EmailSubscribe();
                    $emailSubscribe->email = $event['EmailAddress'];
                    $emailSubscribe->save();
                }
            }
        }
        return response();
    }
    /*
    "Events": [
        {
            "CustomFields": [
                {
                    "Key": "website",
                    "Value": "http://example.org"
                }
            ],
            "Date": "2010-12-14 11:32:00",
            "OldEmailAddress": "test@example.org",
            "EmailAddress": "test@example.org",
            "Name": "Test Subscriber Renamed",
            "Type": "Update",
            "State": "Active"
        }
    ],
    "ListID": "96c0bbdaa54760c8d9e62a2b7ffa2e13"
    */

    public function updateWebhook(Request $request) {
        $json = json_decode($request,true);

        if (isset($json['Events'])) {
            foreach ($json['Events'] as $event) {
                if ($emailSubscribe = EmailSubscribe::where('email', $event['OldEmailAddress'])->first()) {
                    if ($event['OldEmailAddress'] != $event['EmailAddress'])
                        $emailSubscribe->email = $event['EmailAddress'];
                    if ($emailSubscribe->unsubscribe_date)
                        $emailSubscribe->unsubscribe_date = null;
                    $emailSubscribe->save();
                }

                if ($user = User::where('email', $event['OldEmailAddress'])->first()) {
                    if ($event['OldEmailAddress'] != $event['EmailAddress'])
                        $user->email = $event['EmailAddress'];
                    if (!$user->is_subscribed)
                        $user->is_subscribed = true;
                    $user->save();
                }
            }
        }

        return response();
    }

    public function deactivateWebhook(Request $request) {
        $json = json_decode($request,true);

        if (isset($json['Events'])) {
            foreach ($json['Events'] as $event) {
                if ($emailSubscribe = EmailSubscribe::where('email', $event['EmailAddress'])
                    ->whereNull('unsubscribe_date')
                    ->first()
                ) {
                    $emailSubscribe->unsubscribe_date = Carbon::now();
                    $emailSubscribe->save();
                }

                if ($user = User::where('email', $event['EmailAddress'])
                    ->where('is_subscribed', 1)
                    ->first()
                ) {
                    $user->is_subscribed = false;
                    $user->save();
                }
            }
        }
        return response();
    }
}
