<?php

namespace App\Console\Commands;

use App\StripeCoupon;
use App\StripePromoCode;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class StripeSyncPromoCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:stripe-promo-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync promo codes with Stripe';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stripe = new StripeClient(config('services.stripe.stripe_secret'));

        $coupons = $stripe->coupons->all();

        foreach ($coupons as $coupon) {

            if (! $stripeCoupon = StripeCoupon::where('coupon_id',$coupon->id)->first())
                $stripeCoupon = new StripeCoupon();

            $stripeCoupon->coupon_id = $coupon->id;
            $stripeCoupon->amount_off = $coupon->amount_off;
            $stripeCoupon->stripe_created = date('Y-m-d H:i:s',$coupon->created);
            $stripeCoupon->currency = $coupon->currency;
            $stripeCoupon->duration = $coupon->duration;
            $stripeCoupon->duration_in_months = $coupon->duration_in_months;
            $stripeCoupon->live_mode = ($coupon->livemode) ? 1 : 0;
            $stripeCoupon->max_redemptions = $coupon->max_redemptions;
            $stripeCoupon->name = $coupon->name;
            $stripeCoupon->percent_off = $coupon->percent_off;
            $stripeCoupon->redeem_by = ($coupon->redeem_by) ? date('Y-m-d H:i:s',$coupon->redeem_by) : null;
            $stripeCoupon->times_redeemed = $coupon->times_redeemed;
            $stripeCoupon->valid = ($coupon->valid) ? 1 : 0;
            $stripeCoupon->save();
        }

        $promoCodes = $stripe->promotionCodes->all();

        foreach($promoCodes as $promoCode) {

            if (! $stripePromoCode = StripePromoCode::where('promo_id',$promoCode->id)->first())
                $stripePromoCode = new StripePromoCode();

            $stripePromoCode->promo_id = $promoCode->id;
            $stripePromoCode->coupon_id = $promoCode->coupon->id;
            $stripePromoCode->active = ($promoCode->active) ? 1 : 0;
            $stripePromoCode->code = $promoCode->code;
            $stripePromoCode->stripe_created = date('Y-m-d H:i:s', $promoCode->created);
            $stripePromoCode->customer = $promoCode->customer;
            $stripePromoCode->expires_at = ($promoCode->expires_at) ? date('Y-m-d H:i:s',$promoCode->expires_at) : null;
            $stripePromoCode->live_mode = ($promoCode->livemode) ? 1 : 0;
            $stripePromoCode->max_redemptions = $promoCode->max_redemptions;
            $stripePromoCode->times_redeemed = $promoCode->times_redeemed;
            $stripePromoCode->save();
        }

        return true;

    }
}
