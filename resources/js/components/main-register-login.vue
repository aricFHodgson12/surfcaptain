<template>
    <div>
        <form class="header-register-form" :class="{'is-modal': isModal}">
            <div class="register-login-switch" v-if="formActive === 'register' || formActive === 'signin' || formActive === 'verify'">
                <div class="register-switch" :class="{'switch-active': formActive === 'register'}" @click="formActive = 'register'">Register</div>
                <div class="login-switch" :class="{'switch-active': formActive !== 'register'}" @click="formActive = 'signin'">Sign In</div>
            </div>
            <h1 v-if="formActive === 'reset'">Reset Password</h1>
            <div v-if="emailVerified && formActive === 'signin'" class="register-email-verified">Your email has been verified. Sign in below.</div>
            <div v-else-if="resetVerified && formActive === 'change'" class="register-email-verified">Change your password below</div>
            <div v-else-if="facebookError" class="register-email-facebook-error">Sorry, we can't use Facebook Login for your account. Please register with email/password below.</div>
            <div v-if="(formActive === 'signin' || formActive === 'register') && facebookError === false">
                <div class="fb-login-wrapper">
                    <a href="/login/facebook">
                        <div class="fb-login">
                            <div class="fb-login-logo"><img src="/images/icon/fb_logo_ltblue.svg"></div>
                            <div class="fb-login-txt">Log In With Facebook</div>
                        </div>
                    </a>
                </div>
                <div class="fb-or-login">
                    <span>OR</span>
                </div>
            </div>
            <!--
            <label v-if="formActive === 'register'">Name
                <input name="name" type="text"  v-model="name" placeholder="">
            </label>
            -->
            <label>Email
                <input name="email" type="text" v-model="email" placeholder="">
            </label>
            <label v-if="formActive !== 'reset'"><span v-if="formActive === 'change'">New </span>Password
                <input name="password" type="password" v-model="password" placeholder="">
            </label>
            <div class="grid-x" v-if="formActive === 'signin'">
                <div class="cell small-6 register-remember-me">
                    <input v-model="remember" type="checkbox" id="remember-me">
                    <label for="remember-me">Stay Signed In</label>
                </div>
                <div class="cell small-6 register-link" >
                    <a @click="formActive = 'reset'">Forgot Your Password?</a>
                </div>
            </div>
            <div class="register-newsletter" v-else-if="formActive  === 'register'">
                <input v-model="newsletter" type="checkbox" id="newsletter">
                <label for="newsletter">Subscribe to our newsletter <span class="hide-for-small-only">for updates</span></label>
            </div>
            <div v-else-if="formActive === 'change' || formActive === 'reset'" class="register-link">
                <a @click="formActive = 'signin'">Back to Sign In</a>
            </div>
            <div class="register-form-error">{{ errorMsg }}</div>
            <div v-if="successMessage" class="register-form-success">{{ successMessage }}</div>
            <button v-if="!successMessage" class="button" @click.prevent="submitForm()"><span v-if="processForm" class="loader"></span>{{ formAction }}</button>
        </form>
        <div class="right-col hide-for-large" :class="{'is-modal': isModal}">
            <button class="close-fav" @click="$emit('toggle')">
                <span><img src="/images/icon/chevron_left_white.svg"></span>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        props:['isModal'],
        data: function() {
            return {
                email: '',
                //name: '',
                password: '',
                remember: true,
                newsletter: true,
                formActive: 'signin',
                errorMsg: '',
                processForm: false,
                successMessage:false,
                token:false,
                emailVerified: false,
                resetVerified: false,
                facebookError: false
            }
        },
        methods: {
            capitalize: function(text) {
                return window.SC.capitalize(text);
            },
            submitForm: function() {

                //this.$emit('signedin');
                //return;

                this.processForm = true;
                const self = this;
                let params = {};

                params.email = this.email;
                if (this.formActive !== 'reset')
                    params.password = this.password;


                if (this.formActive === 'signin')
                    params.remember = this.remember;
                else if (this.formActive === 'register')
                    params.is_subscribed = this.newsletter;
                else if (this.formActive === 'change') {
                    params.token = this.token;
                    params.password_confirmation = this.password; //system is expecting this
                }

                const emailRe = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

                //if (this.formActive === 'register' && this.name === '')
                //    this.errorMsg = 'You must include a name';
                if (! emailRe.test(this.email))
                    this.errorMsg = 'Your email is not valid';
                else if (this.formActive === 'register' && this.password.length < 8)
                    this.errorMsg = 'Your password must be at least 8 characters';
                else if (this.formActive !== 'reset' && this.password === '')
                    this.errorMsg = 'You must enter a password';
                else
                    this.errorMsg = '';

                if (! this.errorMsg) {

                    let url = '/login/ajax';
                    if (this.formActive === 'register')
                        url = '/register/ajax';
                    else if (this.formActive === 'reset')
                        url = '/password/email-ajax';
                    else if (this.formActive === 'change')
                        url = '/password/reset-ajax';
                    else if (this.formActive === 'verify')
                        url = '/login/verify-email';

                    //url = window.location.protocol+'//'+window.location.hostname+url;

                    axios({
                        method: 'post',
                        url: url,
                        headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                        data: params,
                    })
                        .then(function (response) {

                            if (response.data.loginFromRegister === true)
                                self.formActive = 'signin';
                            else if (response.data.sendToRegister === true)
                                self.formActive = 'register';
                            else if (response.data.needsVerification === true)
                                self.formActive = 'verify';

                            if (response.data.errorMsg !== undefined && response.data.errorMsg !== '') {

                                setTimeout(function() { //timeout, helps with formActive watcher that sets error msg to ''
                                    self.errorMsg = response.data.errorMsg;
                                },200);
                                self.processForm = false;

                            } else {

                                if (self.formActive === 'signin' || self.formActive === 'change') {
                                    self.successMessage = 'Success! Logging in...';

                                    if (self.formActive === 'signin') {
                                        gtag('event', 'sign_in', {
                                            'event_category': 'engagement'
                                        });
                                    }

                                    if (!self.isModal) {
                                        self.$root.loading = true;
                                        window.location = window.location.pathname;
                                    } else {
                                        let queryParams = '';
                                        if (response.data.subscription === 'basic')
                                            queryParams = '?profile=true';

                                        self.$root.loading = true;
                                        window.location = window.location.pathname + queryParams;
                                    }

                                } else if (self.formActive === 'reset') {
                                    self.successMessage = 'Check your email for reset link';

                                } else if (self.formActive === 'verify') {
                                    self.successMessage = 'Check your email for verification link';

                                } else { //register
                                    self.successMessage = 'Check your email to verify address';
                                    if (self.isModal)
                                        self.$emit('registered');

                                    gtag('event', 'register', {
                                        'event_category': 'engagement'
                                    });
                                }

                                self.processForm = false;
                            }
                        })
                        .catch(function () {
                            self.errorMsg = 'There was a server error logging in';
                            self.processForm = false;
                        });
                } else
                    this.processForm = false;
            }
        },
        watch: {
            formActive: function() {
                this.errorMsg = '';
                if (this.formActive === 'reset' && this.token !== false)
                    this.formActive = 'change';
            }
        },
        computed: {
            formAction: function() {
                if (this.formActive === 'signin')
                    return 'Sign In';
                else if (this.formActive === 'register')
                    return 'Register';
                else if (this.formActive === 'reset')
                    return 'Send Reset Link';
                else if (this.formActive === 'change')
                    return 'Change Password';
                else if (this.formActive === 'verify')
                    return 'Send Verification Email';
            }
        },
        created: function() {

            if (window.SC.urlParams.password_token !== undefined) {
                this.token = window.SC.urlParams.password_token;
                this.formActive = 'change';
                this.resetVerified = true;

                if (window.SC.urlParams.email !== undefined)
                    this.email = window.SC.urlParams.email;

            } else if (window.SC.urlParams.login !== undefined) {
                if (window.SC.urlParams.verified !== undefined)
                    this.emailVerified = true;

                else if (window.SC.urlParams.facebookError !== undefined) {
                    this.formActive = 'register';
                    this.facebookError = true;
                }
            }
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    .register-newsletter label, .register-remember-me label {
        display:inline-block;
        margin-left:5px !important;
        margin-right:0 !important;
    }

    .fb-login-wrapper {
        text-align: center;
    }

    .fb-login {

        font-weight:bold;
        cursor:pointer;
        display:inline-block;
        color:$blue-100;
        line-height:38px;
        position:relative;
        padding:0 30px;
        border:1px solid $blue-100;
        border-radius:19px;

        .fb-login-logo {
            width: 20px;
            display:inline-block;
            margin-right:5px;
        }

        .fb-login-txt {
            display:inline-block;
        }
    }

    .fb-or-login {
        border-bottom:1px solid $gray-200;
        text-align:center;
        line-height: 0.1em;
        margin: 25px 0;
        font-weight: bold;
        color: $gray-200;
        span {
            background:$blue-800;
            padding: 0 8px;
        }
    }

    .right-col.is-modal {
        display:none;
    }
</style>

