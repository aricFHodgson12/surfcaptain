<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StripePromoCode extends Model
{
    public function coupon() {
        return $this->belongsTo('App\StripeCoupon','coupon_id','coupon_id');
    }
}
