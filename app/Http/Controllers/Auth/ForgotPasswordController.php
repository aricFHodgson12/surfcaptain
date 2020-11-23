<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmailAjax(Request $request)
    {
        //validate email and password
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|string',
        ]);

        if (! $validator->passes())
            return response()->json(['errorMsg' => $validator->errors()->first()]);

        //make sure that email exists in user table
        if (! User::where('email',$request->email)->first())
            return response()->json(['errorMsg' => 'No account exists with this email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return ($response == Password::RESET_LINK_SENT)
            ? response(json_encode(array('errorMsg' => '')))
            : response(json_encode(array('errorMsg' => 'There was a problem sending out a reset link')));
    }
}
