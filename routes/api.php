<?php

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/home/blogpost','HomeController@getBlogposts');

Route::get('/forecast/{slug}','SurfController@show');
Route::get('/weather/{slug}','SurfController@weather');
Route::get('/beach/nearby/lat/{lat}/lon/{lon}','SurfController@nearbyBeach');
Route::get('/timeline/nearby/lat/{lat}/lon/{lon}','SurfController@nearbyTimeline');
Route::get('/timeline/{slug}','SurfController@getTimeline');
Route::get('/nearby/locations','NearbyController@getLocations');
Route::get('/alert', 'HomeController@getAlert')->name('alert');
Route::get('/stats', 'StatsController@show')->middleware('admin')->name('stats');

Route::patch('/email-subscribe', 'EmailSubscribeController@userSubscribe');

Route::get('/favorites/today','FavoriteController@today')->middleware('auth:api');
Route::get('/favorites/is-favorite/{location_beach_id}','FavoriteController@isFavorite')->middleware('auth:api');
Route::delete('/favorites/remove-favorite/{location_beach_id}','FavoriteController@removeFavorite')->middleware('auth:api');
Route::post('/favorites/add-favorite/{location_beach_id}','FavoriteController@addFavorite')->middleware('auth:api');
Route::resource('favorites', 'FavoriteController')->middleware('auth:api');

Route::get('/user-setting/units','UserSettingController@units')->middleware('auth:api');
Route::patch('/user-setting/update','UserSettingController@update')->middleware('auth:api');
Route::get('/user-setting/confirm-password', 'UserSettingController@confirmPassword')->middleware('auth:api');
Route::delete('/user-setting/delete', 'UserSettingController@deleteUser')->middleware('auth:api');

Route::get('/subscription/stripe-intent', 'SubscriptionController@showIntent')->middleware('auth:api');
Route::get('/subscription/details', 'SubscriptionController@getSubscription')->middleware('auth:api');
Route::get('/subscription/promo-code/{promo_code}', 'SubscriptionController@getPromoCode')->middleware('auth:api');
Route::patch('/subscription/update-payment', 'SubscriptionController@updatePayment')->middleware('auth:api');
Route::patch('/subscription/cancel', 'SubscriptionController@cancelSubscription')->middleware('auth:api');
Route::patch('/subscription/renew', 'SubscriptionController@renewSubscription')->middleware('auth:api');

Route::post('campaign-monitor/subscribe', 'EmailSubscribeController@subscribeWebhook');
Route::post('campaign-monitor/update', 'EmailSubscribeController@updateWebhook');
Route::post('campaign-monitor/deactivate', 'EmailSubscribeController@deactivateWebhook');

