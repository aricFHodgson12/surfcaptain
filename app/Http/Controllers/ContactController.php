<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Notifications\ApiError;
use App\Notifications\ContactForm;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function contact()
    {

        $contact = Contact::create(request()->validate([
            'email' => 'bail|required|email',
            'message' => 'bail|required',
            'name' => 'bail|required'
        ]));

        $return = array(
            'valid' => true,
            'errorMsg' => false
        );

        try {

            $users = User::where('role_id',1)->get();
            $notification = array(
                'name' => $contact->name,
                'message' => $contact->message,
                'email' => $contact->email
            );
            Notification::send($users, new ContactForm($notification));

        } catch(\Exception $e){

            $return['valid'] = false;
            $return['errorMsg'] = 'There was a problem sending the email.';
        }

        echo json_encode($return);
    }
}
