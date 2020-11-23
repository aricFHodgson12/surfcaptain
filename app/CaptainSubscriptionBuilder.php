<?php


namespace App;


use Laravel\Cashier\Payment;
use Laravel\Cashier\SubscriptionBuilder;

class CaptainSubscriptionBuilder extends SubscriptionBuilder
{

    /**
     * The promotion code being applied to the customer.
     *
     * @var string|null
     */
    protected $promotionCodeId;

    public function withPromotionCodeId($promotionCodeId)
    {
        $this->promotionCodeId = $promotionCodeId;

        return $this;
    }


    public function buildPayload()
    {
        return array_merge(parent::buildPayload(), array_filter([
            'promotion_code' => $this->promotionCodeId,
        ]));
    }

    /**
     * Create a new Stripe subscription.
     *
     * @param  \Stripe\PaymentMethod|string|null  $paymentMethod
     * @param  array  $options
     * @return \Laravel\Cashier\Subscription
     *
     * @throws \Laravel\Cashier\Exceptions\PaymentActionRequired
     * @throws \Laravel\Cashier\Exceptions\PaymentFailure
     */
    public function create($paymentMethod = null, array $options = [])
    {
        $customer = $this->getStripeCustomer($paymentMethod, $options);

        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $customer->subscriptions->create($this->buildPayload());

        if ($this->skipTrial) {
            $trialEndsAt = null;
        } else {
            $trialEndsAt = $this->trialExpires;
        }

        /** @var \Laravel\Cashier\Subscription $subscription */
        $subscription = $this->owner->subscriptions()->create([
            'name' => $this->name,
            'stripe_id' => $stripeSubscription->id,
            'stripe_status' => $stripeSubscription->status,
            'stripe_plan' => $this->plan,
            'quantity' => $this->quantity,
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => null,
            'promo_id' => $this->promotionCodeId,
        ]);

        if ($subscription->incomplete()) {
            (new Payment(
                $stripeSubscription->latest_invoice->payment_intent
            ))->validate();
        }

        return $subscription;
    }
}
