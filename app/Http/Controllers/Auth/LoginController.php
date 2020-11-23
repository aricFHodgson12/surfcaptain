<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Services\ProviderAccountService;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected $maxAttempts = 8;
    protected $decayMinutes = 2;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function loginAjax(Request $request) {

        $responseData = array();
        if ($request->register_form)
            $responseData['loginFromRegister'] = true;

        //if email does not exist, push over to register form
        if (! $user = User::where('email',$request->email)->first()) {
            $responseData['errorMsg'] = 'An account with this email address does not exist. Please register a new account';
            $responseData['sendToRegister'] = true;
            return response()->json($responseData);
        }

        //if email has not been verified
        if (! $user->hasVerifiedEmail()) {
            $responseData['errorMsg'] = 'Email has not been verified yet.';
            $responseData['needsVerification'] = true;
            return response()->json($responseData);
        }


        //validate email and password
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email|string',
            'password' => 'required|min:8',
        ]);

        if (! $validator->passes()) {
            $responseData['errorMsg'] = $validator->errors()->first();
            return response()->json($responseData);
        }

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $responseData['errorMsg'] = 'Too many log in attempts. Try again in '.$this->decayMinutes.' minutes';
            return response()->json($responseData);
        }


        if ($this->attemptLogin($request)) {

            $request->session()->regenerate();
            $this->clearLoginAttempts($request);

            $subscription = ($user->subscribed('pro')) ? 'pro' : 'basic';

            $responseData['errorMsg'] = '';
            $responseData['subscription'] = $subscription;
            return response()->json($responseData);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        if ($request->register_form)
            $responseData['errorMsg'] = 'This email account already exists, but the password is incorrect. Try signing in with different password.';
        else
            $responseData['errorMsg'] = 'Incorrect email/password. Try again.';
        return response()->json($responseData);
    }

    public function attemptLogin(Request $request)
    {
        $remember = ($request->remember) ? true : false;

        return $this->guard()->attempt(
            $this->credentials($request), $remember
        );
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->scopes(['email'])
            ->redirect();
    }


    /**
     * Obtain the user information from GitHub.
     *
     * @param ProviderAccountService $service
     * @return
     */

    public function handleFacebookCallback(ProviderAccountService $service)
    {
        $facebookUser = Socialite::driver('facebook')->user();

        if ($facebookUser->getEmail()) {
            $user = $service->createOrGetUser($facebookUser);
            auth()->login($user);
            return redirect()->to('/');

        } else
            return redirect()->to('/?login=true&facebookError=true');
    }

    public function verifyEmail(Request $request)
    {
        $responseData['errorMsg'] = '';

        if (! $user = User::where('email',$request->email)->first())
            $responseData['errorMsg'] = 'An account with this email address does not exist.';

        else if (! $user->hasVerifiedEmail())
            $user->sendEmailVerificationNotification();

        else
            $responseData['errorMsg'] = 'This email has already been verified.';

        return response()->json($responseData);
    }

}
