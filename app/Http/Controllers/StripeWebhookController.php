<?php

namespace App\Http\Controllers;

use App\Notifications\PaymentFailure;
use App\Notifications\SubscriptionDeleted;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierController
{

    public function __construct()
    {
        //Log::info('stripe webhook controller hit');
    }

    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        // Call parent handler method
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        // Send email, notification etc.
        if (! $user = $this->getUserByStripeId($payload['data']['object']['customer']) && config('app.env') == 'dev')
            $user = User::find(1);

        $user->notify(new SubscriptionDeleted);

        return $response;
    }

    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        // Only send on the first failed payment
        if ($payload['data']['object']['attempt_count'] === 1) {
            if (! $user = $this->getUserByStripeId($payload['data']['object']['customer']) && config('app.env') == 'dev')
                $user = User::find(1);

            $user->notify(new PaymentFailure);

        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle Customer Updated
     * @param array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        //update renews_at field
        DB::table('subscriptions')
            ->where('stripe_id', $payload['data']['object']['id'])
            ->update([
                'renews_at' => Carbon::createFromTimestamp($payload['data']['object']['current_period_end']),
            ]);

        return $response;
    }

    /**
     * Handle Coupon Updated
     * @param array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function handleCouponUpdated(array $payload)
    {
        Log::info('stripe coupons: '.print_r($payload,true));

        /*
        //update coupon table and modify promotion codes for this coupon
        DB::table('subscriptions')
            ->where('stripe_id', $payload['data']['object']['id'])
            ->update([
                'renews_at' => Carbon::createFromTimestamp($payload['data']['object']['current_period_end']),
            ]);
        */

        return new Response('Webhook Handled', 200);
    }

}
