<template>
    <aside class="header-subscription" :class="{'is-modal': isModal}">
        <div class="header-subscription-content">
            <h1>{{ titleText }}</h1>
            <div class="subscription-text" v-html="subscriptionText"></div>
            <div v-if="subscriptionLoaded">
                <div v-if="subscriptionType === 'Pro'">
                    <div class="subscription-summary" v-if="subscriptionOnTrial || subscriptionCancelled || (subscriptionRenewDate && !promoFree)">
                        <div v-if="subscriptionOnTrial">
                            You are currently on your trial period, which ends on {{ subscriptionTrialEndDate }}.
                            <span v-if="!subscriptionCancelled">You will be charged on {{ subscriptionRenewDate }}.</span>
                            <span v-else>Your subscription was canceled, and you will not be charged.</span>
                        </div>
                        <div v-else-if="subscriptionCancelled">
                            Your subscription was cancelled and will expire on {{ subscriptionEndDate}}.
                        </div>
                        <div v-else-if="subscriptionRenewDate && !promoFree">
                            Your subscription will auto renew on <span class="no-wrap">{{ subscriptionRenewDate }}</span>.
                        </div>
                    </div>
                    <div v-if="!subscriptionCancelled" class="subscription-method">
                        Payment Method: <span class="payment-method">{{ cardBrand }} {{ cardLastFour }}</span>
                        <button type="button" class="update-btn" @click="displayAddPayment">UPDATE</button>
                    </div>
                    <button v-if="!subscriptionCancelled && !showAddPayment" type="button" class="cancel-button" @click="cancelSubscription">
                        <span v-if="subscriptionOnTrial">CANCEL TRIAL</span>
                        <span v-else>CANCEL SUBSCRIPTION</span>
                        <span v-if="cancelResumeProcessing" class="loader"></span>
                    </button>
                    <button v-if="subscriptionCancelled && !showAddPayment" type="button" class="resume-button" @click="resumeSubscription">
                        RESUME SUBSCRIPTION
                        <span v-if="cancelResumeProcessing" class="loader"></span>
                    </button>
                    <div v-if="cancelRenewMessage && !showAddPayment" class="cancel-renew-message" :class="cancelRenewClass">{{ cancelRenewMessage }}</div>
                </div>
                <div v-show="showAddPayment">
                    <div id="card-info" :style="{ minHeight: cardInfoHeight }">
                        <label>Name
                            <input id="card-holder-name" type="text" v-model="cardHolderName">
                        </label>

                        <!-- Stripe Elements Placeholder -->
                        <label>Credit Card
                            <div id="card-element"></div>
                        </label>

                        <div v-if="!addPromotionCode" id="card-info-show-coupon">
                            <a type="button" @click="toggleCoupon()">Add Promo Code</a>
                        </div>
                        <label v-else id="card-coupon">Coupon Code
                            <div id="card-coupon-code"><input type="text" v-model="promotionCode"></div>
                        </label>
                        <div v-show="promoMessage" id="card-promo-message">{{ promoMessage }}</div>
                        <div v-if="promoLoading" id="card-promo-loading">Searching for Promotion...</div>
                    </div>
                    <button id="card-button" type="button" @click="handleCardSetup" :class="{success: stripeSuccess}">
                        {{ addPaymentText }}
                        <span v-if="stripeProcessing" class="loader"></span>
                    </button>
                    <div class="stripe-message" v-if="stripeError">{{ stripeError }}</div>
                </div>
            </div>
            <div v-else>
                Loading Subscription...
            </div>
            <div v-if="isModal === false">
                <div class="right-col hide-for-large">
                    <button class="close-profile" @click="$root.toggleSubscriptionMenu()">
                        <span><img src="/images/icon/chevron_left_white.svg"></span>
                    </button>
                </div>
                <div class="close-subscription show-for-large" @click="$parent.toggleSubscription">
                    <img width="12" src="/images/icon/chevron_left_white.svg" alt="close subscription view">
                </div>
            </div>
        </div>
    </aside>
</template>

<script>
    let stripe;
    export default {
        name: "main-subscription.vue",
        props:['isModal'],
        data: function() {
            return {
                stripeClientSecret:'',
                stripePubKey:'',
                stripeError:false,
                stripeSuccess:false,
                stripeProcessing:false,
                stripePaymentMethod: false,
                showAddPayment: false,
                cardInfoHeight: '180px',
                cardElement:false,
                cardHolderName:'',
                cardLastFour: '',
                cardBrand: '',
                subscriptionType: '',
                subscriptionLoaded:false,
                subscriptionCancelled: false,
                subscriptionEndDate: false,
                subscriptionRenewDate: false,
                subscriptionOnTrial: false,
                subscriptionTrialEndDate: false,
                subscriptionTrialDays: false,
                cancelRenewError: false,
                cancelRenewSuccess: false,
                cancelResumeProcessing: false,
                addPromotionCode: false,
                promoMessage: false,
                promotionCode:'',
                promotionCodeId:false,
                promoTypingTimer: null,
                promoLoading:false,
                promoText: '',
                promoFree: false
            }
        },
        methods: {
            handleCardSetup: async function() {
                if (this.stripeSuccess)
                    return;

                this.stripeProcessing = true;
                this.stripeError = false;

                if (! this.stripePaymentMethod) {
                    const {setupIntent, error} = await stripe.handleCardSetup(
                        this.stripeclientSecret, this.cardElement, {
                            payment_method_data: {
                                billing_details: {name: this.cardHolderName}
                            }
                        }
                    );

                    if (error) {
                        console.log('error');
                        console.log(error);

                        if (error.hasOwnProperty('message'))
                            this.stripeError = error.message;

                        this.stripeProcessing = false;
                    } else {
                        this.stripePaymentMethod = setupIntent.payment_method;
                        this.updatePaymentMethod(this.stripePaymentMethod);
                    }

                } else
                    this.updatePaymentMethod(this.stripePaymentMethod);
            },
            updatePaymentMethod(payment_method) {
                let self = this;
                let params = {};
                params.payment_method = payment_method;
                if (this.promotionCodeId)
                    params.promotionCodeId = (this.promotionCodeId) ? this.promotionCodeId : false;

                axios({
                    method: 'patch',
                    url: '/api/subscription/update-payment',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    data: params
                })
                    .then(function (response) {
                        if (response.data.success === true) {
                            self.cardLastFour = response.data.last_four;
                            self.cardBrand = response.data.card_brand;
                            self.subscriptionRenewDate = response.data.renew_date;
                            self.subscriptionOnTrial = response.data.on_trial;
                            self.subscriptionTrialEndDate = response.data.trial_end_date;
                            self.subscriptionTrialDays = response.data.trial_days;
                            self.stripeSuccess = true;
                            self.subscriptionType = 'Pro';
                            self.cardInfoHeight = '0'; //slide up card info
                            document.getElementById('card-info').style.height = '0';

                            self.$emit('changeSubscription','Pro');
                            self.$root.reloadFcst = true;

                            gtag('event', 'subscribe', {
                                'event_category': 'subscription',
                                'event_label': (self.subscriptionOnTrial) ? 'on_trial' : 'pro'
                            });
                        } else
                            self.stripeError = response.data.errorMsg;

                        self.stripeProcessing = false;
                     })
                    .catch(function (error) {
                        console.log(error);
                        self.stripeError = 'There was an error processing this request.';
                        self.stripeProcessing = false;
                    });
            },
            toggleCoupon() {
                this.addPromotionCode = ! this.addPromotionCode;
                this.cardInfoHeight = (this.addPromotionCode) ? '230px' : '180px';
            },
            showPromoMessage() {
                document.getElementById('card-promo-message').classList.remove('error');
                this.promoMessage = false;

                let self = this;

                axios({
                    method: 'get',
                    url: '/api/subscription/promo-code/'+this.promotionCode,
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
                })
                    .then(function (response) {
                        self.promoLoading = false;

                        if (response.data.error) {
                            document.getElementById('card-promo-message').classList.add('error');
                            self.promotionCodeId = false;
                        } else if (response.data.promoId)
                            self.promotionCodeId = response.data.promoId;

                        self.promoMessage = response.data.message;
                    })
                    .catch(function (error) {
                        self.promoLoading = false;
                        document.getElementById('card-promo-message').classList.add('error');
                        self.promoMessage = 'There was an error processing Promo Code: '+error;
                        self.promotionCodeId = false;
                    })
            },
            cancelSubscription() {
                this.cancelResumeProcessing = true;
                let self = this;
                axios({
                    method: 'patch',
                    url: '/api/subscription/cancel',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.success) {
                            self.subscriptionCancelled = true;
                            self.subscriptionEndDate = response.data.endDate;
                            self.cancelRenewSuccess = 'You have cancelled your subscription.';

                            gtag('event', 'cancel', {
                                'event_category': 'subscription',
                                'event_label': (self.subscriptionOnTrial) ? 'on_trial' : 'pro'
                            });

                        } else if (response.data.errorMsg)
                            self.cancelRenewError = response.data.errorMsg;

                        self.cancelResumeProcessing = false;
                    })
                    .catch(function (error) {
                        self.cancelRenewError = 'There was an error processing this cancel request.';
                        self.cancelResumeProcessing = false;
                    })
            },
            resumeSubscription() {
                this.cancelResumeProcessing = true;
                let self = this;
                axios({
                    method: 'patch',
                    url: '/api/subscription/renew',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.success) {
                            self.subscriptionCancelled = false;
                            self.cancelRenewSuccess = 'You have resumed your subscription.';

                            gtag('event', 'resume', {
                                'event_category': 'subscription',
                                'event_label': (self.subscriptionOnTrial) ? 'on_trial' : 'pro'
                            });

                        } else if (response.data.errorMsg)
                            self.cancelRenewError = response.data.errorMsg;
                        else
                            self.cancelRenewError = 'There was a problem renewing subscription.';

                        self.cancelResumeProcessing = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        self.cancelRenewError = 'There was an error processing this renew request.';
                        self.cancelResumeProcessing = false;
                    })
            },
            displayAddPayment: function() {
                this.showAddPayment = ! this.showAddPayment;
                this.loadStripe();
            },
            loadStripe() {
                self = this;
                const loadStripeFields = function() {
                    stripe = Stripe(self.stripePubKey);

                    const elements = stripe.elements();
                    self.cardElement = elements.create('card');
                    self.cardElement.mount('#card-element');
                };

                //get stripe info from server, and load stripe sdk
                axios({
                    method: 'get',
                    url: '/api/subscription/stripe-intent',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        self.stripeclientSecret = response.data.client_secret;
                        self.stripePubKey = response.data.pub_key;

                        if (! document.getElementById('stripe-sdk')) {
                            const stripeScript = document.createElement('script');
                            stripeScript.setAttribute('id', 'stripe-sdk');
                            stripeScript.setAttribute('src', 'https://js.stripe.com/v3/');
                            stripeScript.setAttribute('async', 'false');
                            stripeScript.onreadystatechange = loadStripeFields;
                            stripeScript.onload = loadStripeFields;
                            document.head.appendChild(stripeScript);
                        } else
                            loadStripeFields();
                    })
                    .catch(function (error) {console.log(error);})
            }
        },
        watch: {
            promotionCode(newVal, oldVal) {
                this.promoMessage = false;
                this.promoLoading = true;
                clearTimeout(this.promoTypingTimer);

                this.promoTypingTimer = setTimeout(function () {
                    if (self.promotionCode !== '')
                        self.showPromoMessage();
                    else
                        self.promoLoading = false;
                }, 500);
            }
        },
        computed: {
            titleText: function() {
                if (this.subscriptionType === 'Basic')
                    return 'Upgrade To Pro';
                else
                    return 'Manage Subscription';
            },
            chargeDate: function() {
                let date = new Date();
                date.setDate(date.getDate() + this.subscriptionTrialDays);
                let dateTimeFormat = new Intl.DateTimeFormat('en', { year: 'numeric', month: 'short', day: '2-digit' });
                let [{ value: month },,{ value: day },,{ value: year }] = dateTimeFormat.formatToParts(date);

                return month+' '+day+', '+year;
            },
            subscriptionText: function() {
                if (this.subscriptionType === 'Basic') {
                    if (this.subscriptionCancelled)
                        return 'Your subscription to Surf Captain Pro was cancelled and ended on '+this.subscriptionEndDate+'. Renew now for $9.99 per year. <a href="/faq#why-pro-subscription" target="_blank">Read about the Pro benefits.</a>';
                    else
                        return '<p>Add your Credit Card info below to upgrade to Surf Captain Pro for $9.99 per year with a '+this.subscriptionTrialDays+' day trial period.</p><p>Your card will not be charged until '+ this.chargeDate +' and you may cancel anytime before then. <a href="/faq#why-pro-subscription" target="_blank">Read about the Pro benefits.</a></p>';
                } else
                    return 'You are subscribed to Surf Captain Pro for $9.99 per year. <a href="/faq#why-pro-subscription" target="_blank">Read about the Pro benefits.</a> '+this.promoText+' ';
            },
            addPaymentText: function() {
                if (this.subscriptionType === 'Basic') {
                    return 'Add Payment Method';
                } else {
                    if (this.stripeSuccess)
                        return 'Payment Method Accepted';
                    else
                        return 'Update Payment Method';
                }
            },
            cancelRenewMessage() {
                if (this.cancelRenewSuccess)
                    return this.cancelRenewSuccess;
                else if (this.cancelRenewError)
                    return this.cancelRenewError;
                else if (! this.subscriptionCancelled)
                    return 'If you cancel, you will continue to have Pro access until '+this.subscriptionRenewDate+'.';
                else
                    return 'If you renew, your card will not be charged until '+this.subscriptionRenewDate+'.';
            },
            cancelRenewClass() {
                if (this.cancelRenewSuccess)
                    return 'success';
                else if (this.cancelRenewError)
                    return 'error';
                else
                    return 'normal';
            }
        },
        created() {
            let self = this;
            axios({
                method: 'get',
                url: '/api/subscription/details',
                headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
            })
                .then(function (response) {
                    if (response.data.type === 'Basic') {
                        self.showAddPayment = true;
                        self.loadStripe();
                    }

                    self.cardLastFour = response.data.last_four;
                    self.cardBrand = response.data.card_brand;
                    self.subscriptionCancelled = response.data.cancelled;
                    self.subscriptionEndDate = response.data.subscription_ends;
                    self.subscriptionType = response.data.type;
                    self.subscriptionRenewDate = response.data.renew_date;
                    self.subscriptionOnTrial = response.data.on_trial;
                    self.subscriptionTrialEndDate = response.data.trial_end_date;
                    self.subscriptionTrialDays = response.data.trial_days;
                    self.promoText = response.data.promo_text;
                    self.promoFree = response.data.promo_free;
                    self.subscriptionLoaded = true;
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
        mounted() {
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    .header-subscription {
        position: absolute;
        top: 0;
        left: 0;
        background: $blue-800;
        width: 100%;
        min-height: 100%;
        padding: 12px;
        line-height:24px;
        color:#fff;

        &.is-modal {
            position:relative;
        }

        h1 {
            display: block !important;
            text-align: left !important;
            font-weight:900;
            font-size: 24px;
            line-height: 24px;
            border-bottom:0;
        }

        .subscription-summary {
            margin-bottom:1rem;
            padding: 12px 10px;
            background-color: $blue-600;
            border-radius: 4px;
            color:$blue-100;
        }

        .subscription-method {
            margin-bottom:1rem;

            .payment-method {
                margin-right:1rem;
            }

            .button {
                display:inline-block;
                font-size: 0.6rem;
                width: inherit;
                margin: 0;
            }
        }

        .cancel-renew-message {
            margin-top:6px;
            color: $blue-100;
            font-style: italic;

            &.error {
                color: $warning-500;
            }
            &.success {
                color: $success-500;
                font-weight: normal;
            }
        }

        #card-info {
            transition:min-height 1s ease-in;
            overflow:hidden;
        }

        #card-element {
            background-color: #fff;
            padding: 12px 8px;
            min-height:41px;
        }
        #card-info-show-coupon {
            text-align: right;
            margin-top:12px;
        }

        #card-coupon {
            margin-top:1rem;

            #card-coupon-code {

            }
        }

        #card-promo-message {
            line-height: 20px;
            margin: 6px 0;
            color: $success-500;

            &.error {
                color: $warning-500;
            }
        }

        #card-promo-loading {
            color:$gray-500;
        }

        #card-button, .update-btn, .cancel-button, .resume-button {
            font-weight:bold;
            cursor:pointer;
            display:inline-block;
            line-height:38px;
            position:relative;
            padding:0 30px;
            border-radius:19px;
            outline:0;
            width:100%;

            &#card-button, &.cancel-button, &.resume-button {
                background: $blue-00;
                color:$blue-500;
            }

            &#card-button {
                margin-top:12px;
            }

            &.update-btn {
                color:$blue-100;
                border:1px solid $blue-100;
                width:unset;
                line-height: 26px;
                padding: 0 30px;
                font-size: 12px;
            }

            &.success {
                margin-left:0;
                background-color:$success-600;
                color:#fff;
                cursor:default;

                &:hover, &:focus {
                    background-color:$success-600;
                    color:#fff;
                }
            }

            .loader {
                position:absolute;
                top:6px;
                left:10px;
                border: 5px solid #bbc7ce;
                border-top: 5px solid $blue-500;
            }
        }

        .close-subscription {
            position: absolute;
            cursor: pointer;
            top: 15px;
            right: 12px;
        }

        .subscription-text {
            margin: 1rem 0;

            a {
                color: $blue-100;
                text-decoration: underline;
            }
        }

        .stripe-message {
            margin-top:6px;
            color: $warning-500;
            font-size:14px;
        }

        @include breakpoint(medium down) {
            .right-col {
                position: absolute;
                top: 0;
                right: -48px;
                background: $blue-700;
                min-height: 100vh;
                height: 100%;
                width: 48px;
                text-align: center;

                button {
                    padding: 20px 15px;
                    cursor: pointer;
                    outline: 0;

                    &.close-fav {
                        padding: 15px;
                        font-size: 2em;
                        color: #fff;
                    }

                    img {
                        width: 12px;
                        margin-right: 14px;
                    }
                }
            }
        }
    }
</style>
