<template>
    <aside class="header-profile">
        <div class="header-profile-content">
            <h1>Profile</h1>
            <div v-if="settingsLoaded">
                <ul>
                    <li><img src="/images/icon/profile.svg" alt="Profile Icon"> {{ email }}</li>
                    <li v-if="!isMobileNav">
                        <label>SUBSCRIPTION:
                            <div class="header-profile-subscription clearfix">
                                <span>{{ subscription }}</span>
                                <button class="profile-subscription-btn" type="button" @click="toggleSubscription">{{ subscriptionButtonText }}</button>
                            </div>
                        </label>
                    </li>
                    <li>
                        <label>WIND SPEED <transition name="fade"><span class="success" v-if="windSuccess">success</span></transition></label>
                        <select v-model="selectedWind" @change="changeWind()">
                            <option v-for="wind in units.wind"
                                    :value="wind.id"
                            >{{ wind.name}}</option>
                        </select>
                    </li>
                    <li>
                        <label>WAVE HEIGHT <transition name="fade"><span class="success" v-if="wvhtSuccess">success</span></transition></label>
                        <sc-units-toggle variable="wvht" :units="units.wvht" :selected="selectedWvht" @wvhtchange="changeWvht"></sc-units-toggle>
                    </li>
                    <li>
                        <label>TIDE HEIGHT <transition name="fade"><span class="success" v-if="tideSuccess">success</span></transition></label>
                        <sc-units-toggle variable="tide" :units="units.tide" :selected="selectedTide" @tidechange="changeTide"></sc-units-toggle>
                    </li>
                    <li>
                        <label>TEMPERATURE <transition name="fade"><span class="success" v-if="tempSuccess">success</span></transition></label>
                        <sc-units-toggle variable="temp" :units="units.temp" :selected="selectedTemp" @tempchange="changeTemp"></sc-units-toggle>
                    </li>
                    <li>
                        <label>NEWSLETTER <transition name="fade"><span class="success" v-if="subscribeSuccess">success</span></transition></label>
                        <div class="profile-email-subscribe">
                            <label class="container">
                                <input type="checkbox" :checked="emailSubscribed === 1 ? 'checked' : ''">
                                <span class="checkmark" @click="changeEmailSubscription($event)"></span>
                                Subscribe
                            </label>
                        </div>
                    </li>
                    <li v-if="!isMobileNav"><a @click="signOut"><img src="/images/icon/sign-out.svg" alt="Sign Out">Sign Out</a></li>
                    <li>
                        <button type="button" id="delete-account-btn" @click="toggleDeleteModal">DELETE ACCOUNT</button>
                    </li>
                </ul>
            </div>
            <div v-else class="loading-settings">Loading settings...</div>
            <form v-if="!isMobileNav" id="logout-form" action="/logout" method="POST" style="display: none;"></form>
            <div class="right-col hide-for-large">
                <button class="close-profile" @click="$emit('close')">
                    <span><img src="/images/icon/chevron_left_white.svg" alt="Close Profile"></span>
                </button>
            </div>
            <sc-subscription
                v-if="showSubscription"
                :isModal="false"
                @changeSubscription="subscription = $event"
            ></sc-subscription>
        </div>
        <transition name="fade">
            <aside id="delete-account" v-if="showDeleteModal">
                <div class="delete-account-info">
                    <h1>Are you sure you want to delete your Surf Captain account?</h1>
                    <p>{{ deleteText }}</p>
                    <label class="delete-password" for="delete-password-txt" v-if="showPasswordField">
                        Password
                        <input type="password" id="delete-password-txt">
                    </label>
                    <button type="button" class="password-btn" v-if="showPasswordField" @click="confirmPassword">Confirm Password</button>
                    <button type="button" class="cancel-btn" @click="cancelDeleteModal">CANCEL</button>
                    <button type="button" class="delete-btn" v-if="!showPasswordField" @click="showPasswordField = true">DELETE</button>
                    <div class="close-modal" @click="toggleDeleteModal">&times;</div>
                    <div class="delete-message" v-if="deleteMessage">{{ deleteMessage }}</div>
                </div>
            </aside>
        </transition>
    </aside>
</template>

<script>
    import subscription from './main-subscription';

    export default {
        name: "main-profile.vue",
        props: ['email','isSubscribed','subscriptionActive','isMobileNav'],
        components: {
          subscription
        },
        data: function () {
            return {
                units: {
                    'wind': [],
                    'wvht': [],
                    'tide': [],
                    'temp': []
                },
                userSettings: {
                    'wind': false,
                    'wvht': false,
                    'tide': false,
                    'temp': false
                },
                emailSubscribed: false,
                settingsLoaded: false,
                unitsChanged: false,
                selectedWind: 5,
                selectedWvht: 1,
                selectedTide: 3,
                selectedTemp: 8,
                windSuccess: false,
                wvhtSuccess: false,
                tideSuccess: false,
                tempSuccess: false,
                subscribeSuccess:false,
                showSubscription: false,
                showDeleteModal: false,
                showPasswordField: false,
                deleteMessage:''
            }
        },
        methods: {
            signOut: function() {
                this.$root.loading = true;
                //add crf to, then submit form
                document.getElementById('logout-form').appendChild(window.SC.csrfInput);
                document.getElementById('logout-form').submit();
            },
            changeEmailSubscription(e) {
                let self = this;
                let params = {};
                params.subscribe = (this.emailSubscribed) ? 0 : 1;

                //load settings here
                axios({
                    method: 'patch',
                    url: '/api/email-subscribe',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    data: params
                })
                    .then(function (response) {
                        if (! response.errorMsg) {
                            self.emailSubscribed = params.subscribe;

                            self.subscribeSuccess = true;
                            setTimeout(function() {
                                self.subscribeSuccess = false;
                            },5000);
                        } else
                            console.log(response.errorMsg);
                    })
                    .catch(function (error) {
                        console.log('There was an error changing email subscription',error);
                    });
            },
            changeWind: function() {
                this.updateSetting('wind',this.selectedWind)
            },
            changeWvht: function(newVal) {
                this.selectedWvht = newVal;
                this.updateSetting('wvht',this.selectedWvht);
            },
            changeTide: function(newVal) {
                this.selectedTide = newVal;
                this.updateSetting('tide',this.selectedTide);
            },
            changeTemp: function(newVal) {
                this.selectedTemp = newVal;
                this.updateSetting('temp',this.selectedTemp);
            },
            updateSetting: function(setting,val,success) {
                let self = this;
                let params = {};
                params.setting = setting;
                params.value = val;

                //load settings here
                axios({
                    method: 'patch',
                    url: '/api/user-setting/update',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    data: params
                })
                    .then(function (response) {
                        if (response.data.success) {
                            self.unitsChanged = true;
                            self.$root.reloadFcst = true;
                            self[setting+'Success'] = true;
                            setTimeout(function() {
                                self[setting+'Success'] = false;
                            },5000);
                        } else
                            console.log(response.data.errorMsg);
                    })
                    .catch(function (error) {
                        console.log('There was an error changing user setting',error);
                    });
            },
            selectedVal: function(val1,val2) {
                if (val1 === val2) return 'selected';
                else return 'false';
            },
            toggleSubscription: function() {
                this.showSubscription = !this.showSubscription;
                //close profile, if we went directly to subscription on mobile view
                if (! this.showSubscription && this.subscriptionActive) {
                    this.$emit('close');
                }

                gtag('event', 'show', {
                    'event_category': 'subscription',
                    'event_label': this.subscription
                });
            },
            toggleDeleteModal() {
                this.showDeleteModal = !this.showDeleteModal;
            },
            cancelDeleteModal() {
                this.toggleDeleteModal();
                this.showPasswordField = false;
            },
            confirmPassword() {
                this.$root.loading = true;
                let self = this;
                let params = {};
                params.password = document.getElementById('delete-password-txt').value;

                axios({
                    method: 'get',
                    url: '/api/user-setting/confirm-password',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    params: params
                })
                    .then(function (response) {
                        if (response.data.errorMsg) {
                            self.deleteMessage = response.data.errorMsg;
                            self.$root.loading = false;
                        } else
                            self.deleteAccount();
                    })
                    .catch(function (error) {
                        self.deleteMessage = 'There was a problem confirming your password: '+error;
                        self.$root.loading = false;
                    });

            },
            deleteAccount() {
                this.$root.loading = true;
                let self = this;
                axios({
                    method: 'delete',
                    url: '/api/user-setting/delete',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
                })
                    .then(function (response) {
                        if (response.data.errorMsg) {
                            self.deleteMessage = response.data.errorMsg;
                            self.$root.loading = false;
                        } else {
                            self.deleteMessage = 'Your account has been deleted';

                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        }
                    })
                    .catch(function (error) {
                        self.deleteMessage = 'There was a problem deleting your account: '+error;
                        self.$root.loading = false;
                    });
            }
        },
        computed: {
            subscriptionButtonText: function() {
                if (this.subscription === 'Basic')
                    return 'UPGRADE TO PRO';
                else
                    return 'MANAGE SUBSCRIPTION';
            },
            deleteText() {
                if (this.showPasswordField)
                    return 'You must confirm your password before proceeding to delete your account. ';
                else
                    return 'Deleting your account will remove all information associated with your account including subscription history, location favorites, and user settings. Once deleted, this action cannot be undone.'
            }
        },
        mounted: function() {
            if (this.subscriptionActive || window.SC.urlParams.subscription !== undefined)
                this.showSubscription = true;
        },
        watch: {
            subscriptionActive(newVal) {
                this.showSubscription = newVal;
            }

        },
        created: function() {
            this.emailSubscribed = parseInt(this.isSubscribed);

            let self = this;
            //load settings here
            axios({
                method: 'get',
                url: '/api/user-setting/units',
                headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
            })
                .then(function (response) {
                    self.subscription = response.data.userSubscription;
                    self.units = response.data.units;
                    self.selectedWind = response.data.userSettings.wind;
                    self.selectedWvht = response.data.userSettings.wvht;
                    self.selectedTide = response.data.userSettings.tide;
                    self.selectedTemp = response.data.userSettings.temp;
                    self.settingsLoaded = true;
                })
                .catch(function (error) {
                    if (typeof(error.response) !== 'undefined' && error.response.status === 422) {
                        //get first error message in errors object
                        let firstProp;
                        let jsonObj = error.response.data.errors;
                        for(let key in jsonObj) {
                            if(jsonObj.hasOwnProperty(key)) {
                                firstProp = jsonObj[key];
                                break;
                            }
                        }
                        //self.showErrorMsg(firstProp[0]);
                    } else
                        console.log('There was a problem processing the form');
                });
        }
    }
</script>

<style lang="scss">
    @import "../../sass/sc-settings";

    .header-profile {
        width:296px;
        padding:12px;
        @include breakpoint(medium down) {
            padding:0;
        }

        .header-profile-subscription {
            color: #fff;
            font-size: 14px;

            button {
                float: right;
                margin: 0;
                outline: 0;

                font-weight:bold;
                cursor:pointer;
                display:inline-block;
                color:$blue-100;
                line-height:38px;
                position:relative;
                padding:0 30px;
                border:1px solid $blue-100;
                border-radius:19px;
            }
        }

        .loading-settings {
            margin-top: 1rem;
        }

        @include breakpoint(medium down) {
            padding-bottom:12px;
        }

        @include breakpoint(medium down) {
            width:100%;
        }

        h1 {
            @include breakpoint(large) {
                display: none;
            }
            @include breakpoint(medium down) {
                margin:0;
            }
        }

        .nav-units, .simple-toggle-options {
            background: inherit;
            color: $blue-100;
            border: 1px solid $blue-100;
            position:relative;
            top:0;

            .active {
                background: $blue-100 !important;
                color:$blue-800 !important;
            }
        }

        ul {
            margin:0;
            list-style:none;
            font-weight:700;

            li {
                border-top:2px solid $blue-600 !important;
                color:#fff !important;
                font-size: 16px !important;
                line-height: 26px !important;
                padding:12px 0 !important;

                @include breakpoint(smedium down) {
                    padding:12px !important;
                }

                &:first-child {
                    border-top:none !important;
                    @include breakpoint(large) {
                        border-top: 2px solid $blue-600 !important;
                    }
                }
                &:last-child {
                    border-bottom:2px solid $blue-600 !important;
                }

                select {
                    width:240px;
                }

                .unit-toggle {
                    display:inline-block;
                }
            }

            img {
                width:20px;
                margin-right:14px;
            }
        }

        .profile-email-subscribe {
            /* Customize the label (the container) */
            .container {
                display: block;
                position: relative;
                cursor: pointer;
                @include userSelectNone();
                margin: 0;
                color: #fff;
                font-size: 14px;
                padding-left:35px;
            }

            /* Hide the browser's default checkbox */
            .container input {
                position: absolute;
                opacity: 0;
                cursor: pointer;
                height: 0;
                width: 0;
            }

            /* Create a custom checkbox */
            .checkmark {
                position: absolute;
                top: 0;
                left: 0;
                height: 25px;
                width: 25px;
                background-color: #eee;
                border-radius:4px;
            }

            /* On mouse-over, add a gray background color */
            .container:hover input ~ .checkmark {
                background-color: #ccc;
            }

            /* When the checkbox is checked, add a blue background */
            .container input:checked ~ .checkmark {
                background-color: #1779ba;
            }

            /* Create the checkmark/indicator (hidden when not checked) */
            .checkmark:after {
                content: "";
                position: absolute;
                display: none;
            }

            /* Show the checkmark when checked */
            .container input:checked ~ .checkmark:after {
                display: block;
            }

            /* Style the checkmark/indicator */
            .container .checkmark:after {
                left: 10px;
                top: 6px;
                width: 5px;
                height: 10px;
                border: solid white;
                border-width: 0 3px 3px 0;
                @include transform(rotate(45deg));
            }
        }

        #delete-account-btn {
            @include borderBtn;
        }
    }

    #delete-account {
        position:fixed;
        top:0;
        bottom:0;
        left:0;
        right:0;
        background:rgba(0,0,0,0.5);

        .delete-account-info {
            padding: 30px 12px 12px;
            background: #fff;
            border-radius: 8px;
            width: 300px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);

            h1 {
                font-size: 18px;
                margin: 0 0 6px;
                padding: 0;
                color: $blue-800;
                line-height: 26px;
                border-bottom: 0;
            }

            p {
                font-size: 14px;
                color: $gray-600;
            }

            .cancel-btn {
                @include borderBtn($gray-400);
            }

            .delete-btn {
                @include borderBtn($warning-500);
                margin: 12px auto;
            }

            .password-btn {
                @include borderBtn($blue-300);
                margin: 12px auto;
            }

            .close-modal {
                position:absolute;
                top:0;
                right:0;
                padding:2px 5px;
                color:#000;
                font-size:24px;
            }

            .delete-password {
                color:$gray-500;

                input {
                    border-radius:4px;
                }
            }

            .delete-message {
                color:$warning-500;
                margin-top:12px;
            }
        }
    }
</style>
