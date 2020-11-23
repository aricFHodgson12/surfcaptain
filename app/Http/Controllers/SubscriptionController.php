<?php

namespace App\Http\Controllers;

use App\StripeCoupon;
use App\StripePromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class SubscriptionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth('api')->user();
    }

    public function showIntent()
    {
        $return = array();
        $return['client_secret'] = $this->user->createSetupIntent()->client_secret;
        $return['pub_key'] = config('services.stripe.stripe_key');

        return $return;
    }

    public function getSubscription()
    {
        $subscription = array(
            'trial_days' => config('services.subscription.trial_days'),
            'renew_date' => false,
            'cancelled' => false,
            'on_trial' => false,
        );

        //create subscription object
        $userSubscription = false;
        if ($this->user->hasPaymentMethod()) {
            $subscription['last_four'] = $this->user->card_last_four;
            $subscription['card_brand'] = $this->user->card_brand;
            if ($userSubscription = $this->user->getSubscription())
                $subscription['subscription_ends'] = date('M d, Y', strtotime($userSubscription->ends_at));

            if ($this->user->subscription('pro')->cancelled())
                $subscription['cancelled'] = true;
        }

        if ($this->user->subscribed('pro')) {

            $subscription['type'] = 'Pro';
            $subscription['on_trial'] = ($this->user->subscription('pro')->onTrial()) ? true : false;

            if ($userSubscription) {
                $subscription['renew_date'] = date('M d, Y', strtotime($userSubscription->renews_at));
                $subscription['trial_end_date'] = ($subscription['on_trial']) ? date('M d, Y', strtotime($userSubscription->trial_ends_at)) : false;

                if ($userSubscription->promo_id) {
                    $stripePromoCode = StripePromoCode::where('promo_id',$userSubscription->promo_id)->first();
                    $coupon = StripeCoupon::where('coupon_id',$stripePromoCode->coupon_id)->first();

                    //estimate when promo text should continue until
                    //its okay its not exact to show the promo that was applied

                    $subscriptionCreated = strtotime($userSubscription->created_at);
                    $promoExpires = $subscriptionCreated + (365 * 3600 * 24); //default expiration

                    if ($coupon->duration == 'repeating')
                        $promoExpires = $subscriptionCreated + ($coupon->duration_in_months * 30 * 3600 * 24); //rough estimate

                    $subscription['promo_text'] = (time() < $promoExpires) ? 'You received the promotion: '.$coupon->name : '';
                    $subscription['promo_free'] = (strtotime($userSubscription->renews_at) < $promoExpires and strpos(strtolower($coupon->name),'free') !== false) ? true : false;
                }
            }

        } else
            $subscription['type'] = 'Basic';

        return response($subscription);
    }

    public function updatePayment(Request $request)
    {

        $return = array(
            'success' => false,
            'errorMsg' => false
        );

        if (! $this->user->subscribed('pro')) {

            $planId = (config('app.env') == 'prod') ? 'plan_Gtq8xVreAkEDnO' : 'plan_GvyYuRBBDU53uU';

            $newSubscription = $this->user->newSubscription('pro', $planId);

            if ($request->promotionCodeId) {
                $newSubscription->withPromotionCodeId($request->promotionCodeId);

                $stripePromotionCode = StripePromoCode::where('promo_id',$request->promotionCodeId)->first();
                $stripeCoupon = StripeCoupon::where('coupon_id',$stripePromotionCode->coupon_id)->first();
                if (strpos(strtolower($stripeCoupon->name),'free') !== false)
                    $promoFree = true;
            }

            if (! isset($promoFree))
                $newSubscription->trialDays(config('services.subscription.trial_days'));

            $return['success'] = ($newSubscription->create($request->payment_method)) ? true : false;

            if (!$return['success']) {
                $return['errorMsg'] = 'There was a problem adding your subscription';
            } else {
                //get auto renew date
                if ($this->user->stripe_id) {
                    Artisan::call('stripe:sync-renewals', [
                        'customer' => $this->user->stripe_id
                    ]);
                }
                $userSubscription = $this->user->getSubscription();
                if ($userSubscription->renews_at)
                    $return['renew_date'] = date('M d, Y',strtotime($userSubscription->renews_at));

                $return['on_trial'] = (! isset($promoFree)) ? true : false;
                $return['trial_end_date'] = (! isset($promoFree)) ? date('M d, Y', strtotime($userSubscription->trial_ends_at)) : false;
            }

        } else if ($this->user->hasPaymentMethod()) {

            $return['success'] = ($this->user->updateDefaultPaymentMethod($request->payment_method)) ? true : false;
            if (! $return['success'])
                $return['errorMsg'] = 'There was a problem updating your subscription.';
        }

        $return['last_four'] = $this->user->card_last_four;
        $return['card_brand'] = $this->user->card_brand;

        return $return;
    }

    public function cancelSubscription() {
        $return = array(
            'success' => false,
            'errorMsg' => false
        );

        if ($this->user->subscription('pro')->cancel()) {
            $userSubscription = $this->user->getSubscription();
            $return['success'] = true;
            $return['endDate'] = date('M d, Y',strtotime($userSubscription->ends_at));
        } else
            $return['errorMsg'] = 'There was a problem cancelling your subscription';

        return $return;
    }

    public function renewSubscription() {
        $return = array(
            'success' => false,
            'errorMsg' => false
        );

        if ($this->user->subscription('pro')->onGracePeriod()) {
            $return['success'] = $this->user->subscription('pro')->resume() ? true : false;

            if (! $return['success'])
                $return['errorMsg'] = 'There was a problem resuming your subscription.';
        } else {
            $return['errorMsg'] = 'Your subscription has expired. You must create a new subscription.';
        }

        return $return;
    }

    public function getPromoCode($promoCode) {
        $return['error'] = false;
        $return['message'] = '';

        if ($promoCode = StripePromoCode::where('code',trim($promoCode))->first()) {

            if ($promoCode->active) {

                if ($coupon = $promoCode->coupon) {
                    $return['error'] = false;
                    $return['message'] = 'PROMO: '.$coupon->name . ' will be applied to your transaction.';
                    $return['promoId'] = $promoCode->promo_id;

                } else {
                    $return['error'] = true;
                    $return['message'] = 'This promo code has encountered a problem.';
                }
            } else {
                $return['error'] = true;
                $return['message'] = 'This promo code is no longer active.';
            }
        } else {
            $return['error'] = true;
            $return['message'] = 'Invalid Promo Code.';
        }

        return $return;
    }
}
