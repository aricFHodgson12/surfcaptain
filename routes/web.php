<?php

use App\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::get('/', 'HomeController@index')->name('home');
Route::get('/about', function () { return view('about'); })->name('about');
Route::post('/contact', 'ContactController@contact');
Route::get('/forecast/{slug}','SurfController@show')->name('forecast');
Route::get('/weather/{slug}','SurfController@weather');
Route::get('/faq', 'FaqController')->name('faq');
Route::get('/nearby', 'NearbyController@index')->name('nearby');
Route::get('/maps', function() { return view('maps'); })->name('maps');
Route::get('/privacy', function() { return view('privacy'); })->name('privacy');
Route::get('/stats', function() { return view('stats'); })->middleware('admin')->name('stats');

Route::post('/subscribe', 'EmailSubscribeController@subscribe');

Auth::routes(['verify' => true]);
//Route::get('login-ajax', 'Auth\LoginController@loginAjax');
Route::get('/logout', 'Auth\LoginController@logout');
Route::post('/cookie',function(Request $request) {
    return response('Hello World')->cookie($request->cookie, $request->value, 60*24*365,'','',false,false);
});

Route::post('/register/ajax','Auth\RegisterController@registerAjax')->name('register-jax');
Route::post('/login/ajax','Auth\LoginController@loginAjax')->name('login-ajax');
Route::get('/login/ajax','Auth\LoginController@loginAjax')->name('login-ajax-get'); //get method exists for redirecting from register method
Route::post('/login/verify-email','Auth\LoginController@verifyEmail')->name('verify-email');
Route::post('/password/email-ajax','Auth\ForgotPasswordController@sendResetLinkEmailAjax');
Route::post('/password/reset-ajax','Auth\ResetPasswordController@resetAjax');

Route::get('/login/facebook', 'Auth\LoginController@redirectToFacebook');
Route::get('/login/facebook/callback', 'Auth\LoginController@handleFacebookCallback');

Route::post('/stripe/webhook', 'StripeWebhookController@handleWebhook');
//Route::post('/stripe/webhook', function() {
//    return response('Hello World');
//});


Route::namespace('Studio')->prefix(config('studio.path'))->group(function () {
    Route::prefix('api')->group(function () {
        Route::prefix('posts')->group(function () {
            Route::get('/', 'PostController@index');
            Route::get('{identifier}/{slug}', 'PostController@show')->middleware('Canvas\Http\Middleware\Session');
        });

        Route::prefix('tags')->group(function () {
            Route::get('/', 'TagController@index');
            Route::get('{slug}', 'TagController@show');
        });

        Route::prefix('topics')->group(function () {
            Route::get('/', 'TopicController@index');
            Route::get('{slug}', 'TopicController@show');
        });

        Route::prefix('users')->group(function () {
            Route::get('{identifier}', 'UserController@show');
        });
    });

    Route::get('/{view?}', 'ViewController')->where('view', '(.*)')->name('studio');
});
