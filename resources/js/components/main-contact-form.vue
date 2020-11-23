<template>
    <form id="about-contact-form">
        <label>YOUR NAME
            <input class="about-name" v-model="name">
        </label>
        <label>
            EMAIL
            <input class="about-email" v-model="email">
        </label>
        <label>
            MESSAGE
            <textarea class="about-message" v-model="message"></textarea>
        </label>
        <div class="text-right">
            <button class="about-contact-submit" @click="submitContactForm">Submit
                <span v-if="processForm" class="contact-form-loader"></span>
            </button>
        </div>
        <transition name="slide">
            <div class="about-form-error" v-if="errorMsg" v-cloak>
                <p class="contact-form-error-msg">{{ errorMsg }}</p>
                <button class="close-button" aria-label="Dismiss alert" type="button" @click="closeContactAlert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="about-form-success" v-else-if="!successHidden" v-cloak>
                <p>Thanks, we'll respond to your message shortly!</p>
                <button class="close-button" aria-label="Dismiss alert" type="button" @click="closeContactSuccess">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </transition>
    </form>
</template>

<script>
    export default {
        name: "main-contact-form.vue",
        data: function() {
            return {
                name: '',
                email: '',
                message: '',
                errorMsg: false,
                successHidden: true,
                processForm: false
            }
        },
        methods: {
            submitContactForm: function(event) {
                event.preventDefault();
                this.processForm = true;
                this.successHidden = true;
                this.errorMsg = false;

                if (window.SC.emailIsValid(this.email) === false) {
                    this.errorMsg = 'Your email address is invalid.';
                    this.processForm = false;
                    return;
                }

                //send off the form data.
                var self = this;
                var params = {};
                params.email = this.email;
                params.name = this.name;
                params.message = this.message;

                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                axios({
                    method: 'post',
                    url: '/contact',
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    data: params,
                })
                    .then(function (response) {
                        if (response.data.valid) {
                            self.successHidden = false;
                            self.email = '';
                            self.name = '';
                            self.message = '';

                            gtag('event', 'sign_in', {
                                'event_category': 'engagement'
                            });

                        } else {
                            self.errorMsg = response.data.errorMsg;
                        }
                        self.processForm = false;
                    })
                    .catch(function (error) {
                        if (error.response.status === 422) {
                            //get first error message in errors object
                            var firstProp;
                            var jsonObj = error.response.data.errors;
                            for(var key in jsonObj) {
                                if(jsonObj.hasOwnProperty(key)) {
                                    firstProp = jsonObj[key];
                                    break;
                                }
                            }
                            self.errorMsg = firstProp[0];
                        } else
                            self.errorMsg = 'There was a problem processing the form';

                        self.processForm = false;
                    });
            },
            closeContactAlert: function() {
                this.errorMsg = false;
            },
            closeContactSuccess: function() {
                this.successHidden = true;
            }
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';
    #about-contact-form {
        .about-form-error, .about-form-success {
            padding: 10px;
            margin-top: 12px;
            border-radius: 4px;
            color: #fff;
            position: relative;

            &.about-form-error {
                background: $warning-500;
            }

            &.about-form-success {
                background: $success-500;
            }

            p {
                margin: 0;
            }

            .close-button {
                position: absolute;
                right: 10px;
                color: #fff;
                outline: 0;
            }
        }

        label {
            display: block;
            font-family: $roboto-condensed-font;
            font-size: 10px;
            color: $light-gray;
            letter-spacing: 0.4px;
            margin-top: 12px;

            input, textarea {
                display: block;
                width: 100%;
                outline: 0;
                padding: 10px 16px;
                border: 1px solid $blue-300;
                border-radius: 19px;
                font-size: 14px;
                color: $light-gray;
                line-height: 18px;
                font-family: $roboto-font;
            }

            textarea {
                height: 112px;
            }
        }

        .about-contact-submit {
            background: $blue-300;
            border-radius: 19px;
            font-size: 14px;
            color: #fff;
            text-align: center;
            line-height: 14px;
            padding: 12px 48px;
            outline:0;
            cursor:pointer;
            position:relative;

            .contact-form-loader {
                @include loader($blue-00, $blue-300, 3px, 20px, 20px);
                position:absolute;
                top:9px;
                right:11px;
            }
        }
    }

</style>
