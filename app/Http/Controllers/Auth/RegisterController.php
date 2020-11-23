<?php

namespace App\Http\Controllers\Auth;

use App\CampaignMonitor;
use App\EmailSubscribe;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function registerAjax(Request $request)
    {
        //if the email already exists, send to login
        if (User::where('email',$request->email)->first())
            return redirect()->to(config('app.url').'/login/ajax?email='.urlencode($request->email).'&password='.urlencode($request->password).'&register_form=true');

        $validator = $this->validator($request->all());
        if (! $validator->passes())
            return response()->json(['errorMsg' => $validator->errors()->first()]);

        event(new Registered($user = $this->create($request->all())));

        //setup default settings
        $user->addUserSettings();

        $errorMsg = '';
        //subscribe to email list
        $CM = new CampaignMonitor();
        if ($request->is_subscribed) {
            if (!$CM->addRecord($data = array('email' => $request->email)))
                $errorMsg = 'There was a problem saving to email list.';

        } else if ($emailSubscriber = EmailSubscribe::where('email',$request->email)->first()) {
            if (! $CM->unsubscribe($request->email))
                $errorMsg = 'There was a problem un-subscribing from email list';
        }

        //$this->guard()->login($user);

        return response()->json(['errorMsg' => $errorMsg]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            //'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            //'name' => $data['name'],
            'is_subscribed' => $data['is_subscribed'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
