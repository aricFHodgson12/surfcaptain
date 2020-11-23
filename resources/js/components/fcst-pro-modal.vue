<template>
    <aside id="pro-modal">
        <div id="modal-content">
            <div v-if="isLargeScreen" class="grid-x">
                <div class="content-promote cell auto">
                    <h1>{{ title }}</h1>
                    <div class="promote-info">
                        <p>{{ upgradeText }}</p>
                    </div>
                    <transition name="slideleft">
                        <div class="promote-image" :key="stepNumber" :class="'step'+stepNumber"></div>
                    </transition>
                </div>
                <div class="content-action cell shrink">
                    <sc-register-login
                        v-if="isLoggedIn === false"
                        isModal="true"
                        @registered="registered"
                    ></sc-register-login>
                    <sc-subscription
                        v-if="isLoggedIn === true"
                        is-modal="true"
                        @changeSubscription="changeSubscription"
                    ></sc-subscription>
                </div>
            </div>
            <div v-else>
                <div id="mobile-content">
                    <div id="mobile-content-bg"></div>
                    <div id="mobile-content-bg-image"></div>
                    <h1>{{ title }}</h1>
                    <div id="mobile-content-action">
                        <div id="mobile-content-form">
                            <sc-register-login
                                v-if="isLoggedIn === false"
                                isModal="true"
                                @registered="registered"
                            ></sc-register-login>
                            <sc-subscription
                                v-if="isLoggedIn === true"
                                is-modal="true"
                                @changeSubscription="changeSubscription"
                            ></sc-subscription>
                        </div>
                    </div>
                    <div id="mobile-content-promote">{{ upgradeText }}</div>
                </div>
            </div>
            <a class="pro-modal-close" @click="closeModal" aria-label="Close Modal Box">&times;</a>
        </div>
    </aside>
</template>

<script>
    export default {
        name: "fcst-premium",
        props: ['isLoggedIn','mode','isLargeScreen'],
        data() {
            return {
                stepNumber: 1,
                upgradeText: ''
            };
        },
        computed: {
            title() {
                if (this.mode === 'pro')
                    return 'You\'ve found a pro feature!';
                else
                    return 'You must login for this feature';
            }
        },
        methods: {
            registered() {
                this.stepText = false;
                this.stepNumber = 2;
                this.upgradeText = 'Your registered, High Five!';
            },
            changeSubscription() {
                this.stepText = false;
                this.stepNumber = 2;
                this.upgradeText = 'Boom! You\'ve reach Pro status!';
                this.$root.reloadFcstData = true;
            },
            closeModal() {
                this.$root.toggleProModal();
            }
        },
        mounted() {
            let self = this;
            const hideProModalEventOnClick = function(element) {
                const modalContainer = document.getElementById('pro-modal');
                const proModalClickListener = event => {
                    if (modalContainer.contains(event.target) && !element.contains(event.target) && window.SC.isVisible(element)) { // or use: event.target.closest(selector) === null
                        self.$root.toggleProModal();
                    }
                };
                document.addEventListener('click', proModalClickListener)
            };
            const proModalElement = document.getElementById('modal-content');
            hideProModalEventOnClick(proModalElement);
        },
        created() {

            console.log('is large screen: '+this.isLargeScreen);

            if (this.mode === 'pro')
                this.upgradeText = 'Upgrade to Pro to access 16 day forecasts!';

            if (! this.isLoggedIn)
                this.stepNumber = 3;

            window.SC.preloadImage('/images/surfer-girl.jpg');
            //window.SC.preloadImage('/images/woman-beach.jpg');
        }
    }
</script>

<style lang="scss">
    @import '../../sass/_sc-settings.scss';

    .pro-modal-transition-enter-active, .pro-modal-transition-leave-active {
        @include breakpoint(medium down) {
            @include slideUpDownTransition;
        }

        @include breakpoint(large up) {
            @include fadeTransition;
        }
    }

    @include breakpoint(medium down) {
        .pro-modal-transition-enter-to, .pro-modal-transition-leave {
            @include slideUpDownEnterTo;
        }
    }

    .pro-modal-transition-enter, .pro-modal-transition-leave-to {
        @include breakpoint(medium down) {
            @include slideUpDownEnter;
        }

        @include breakpoint(large up) {
            @include fadeEnter;
        }
    }

    #pro-modal {
        position:fixed;
        top:0;
        left:0;
        right:0;
        height:100vh;
        overflow:hidden;
        background:rgba(255,255,255,0.8);
        z-index: 9;

        #modal-content {
            background:#fff;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            border: 1px solid $gray-800;
            border-radius: 10px;
            box-shadow: 0 0 10px #000;
            width:900px;
            min-height:500px;
            overflow-y:auto;

            @include breakpoint(xlarge only) {
                width:1000px;
                min-height:550px;
            }

            @include breakpoint(xxlarge only) {
                width:1100px;
                min-height:600px;
            }

            @include breakpoint(medium only) {
                width:85%;
                height:85%;
            }

            @include breakpoint(smedium down) {
                top:0;
                left:0;
                transform:none;
                border:0;
                border-radius:0;
                box-shadow:none;
                width:100%;
                height:100%;
                min-height:100%;
            }

            &.noscroll {
                overflow:hidden;
            }
        }

        .content-action {
            background-color: #002034;
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
            position:relative;
            width:320px;

            @include breakpoint(medium down) {
                position:absolute;
                border-radius:8px;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
            }

            @include breakpoint(small only) {
                width:280px;
                min-width: inherit;
            }

            label, a {
                color:#fff;
            }
        }

        .slideleft-enter-active, .slideleft-leave-active {
            transition: transform 1s ease-out;
        }
        .slideleft-enter {
            transform:translateX(100%);
        }
        .slideleft-enter-to {
            transform:translateX(0);
        }
        .slideleft-leave-to {
            transform:translateX(-100%);
        }

        .content-promote {
            text-align: center;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            position: relative;
            overflow:hidden;
            background: rgba(0, 45, 69, 0.5);

            @include breakpoint(large) {
                min-height:500px;
            }

            @include breakpoint(xlarge) {
                min-height:550px;
            }

            @include breakpoint(xxlarge) {
                min-height:600px;
            }

            @include breakpoint (smedium down) {
                border-radius:0;
            }

            .promote-image {
                position: absolute;
                top:0;
                z-index: -1;
                width:100%;
                height:100%;
                background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 70%, rgb(166, 103, 4) 90%), url(/images/surfer-sunshine-barrel.jpg);
                background-size: cover;
                background-position:center;
                background-repeat: no-repeat;
                -webkit-filter: grayscale(100%);
                filter: grayscale(100%);

                &.step2 {
                    background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 70%, rgb(166, 103, 4) 90%), url(/images/surfer-girl.jpg);
                }

                &.step3 {
                    background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 70%, rgb(166, 103, 4) 90%), url(/images/woman-beach.jpg);
                }
            }

            h1 {
                margin-top: 27px;
                color: #fff;
                font-size: 33px;
                font-weight:900;
                line-height: 48px;
                position:relative;

                @include breakpoint(smedium only) {
                    font-size:22px;
                    margin-top:1rem;
                }

                @include breakpoint(small only) {
                    font-size:19px;
                    margin-top:0;
                }
            }

            .promote-info {
                position: absolute;
                bottom:0;
                width:100%;
                padding: env(safe-area-inset-bottom);

                p {
                    font-size: 18px;
                    color: #fff;

                    @include breakpoint(smedium down) {
                        font-size:16px;
                    }

                    @include breakpoint(small only) {
                        font-size:14px;
                    }
                }
            }
        }

        #mobile-content {
            position:relative;
            background: #364d59;
            padding-bottom:8rem;

            #mobile-content-bg {
                width: 100vw;
                height: 100vh;
                position: absolute;
                z-index: 1;
                background: rgba(0, 45, 69, 0.5);
                top: 0;
                left: 0;
            }

            #mobile-content-bg-image {
                position:absolute;
                top:0;
                left:0;
                z-index:0;
                width: 100vw;
                height: 100vh;
                background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 70%, rgb(166, 103, 4) 90%), url(/images/surfer-sunshine-barrel.jpg);
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                -webkit-filter: grayscale(100%);
                filter: grayscale(100%);
            }

            > h1 {
                text-align:center;
                padding:2rem 0 1rem;
                color: #fff;
                font-size: 23px;
                font-weight: 900;
                line-height: 48px;
                position: relative;
                z-index:2;
            }

            #mobile-content-action {
                z-index:2;
                width:320px;
                margin:0 auto;
                position:relative;

                @include breakpoint(small only) {
                    width:280px;
                }

                #mobile-content-form {
                    background-color: #002034;

                    label, a {
                        color: #fff;
                    }
                }
            }

            #mobile-content-promote {
                z-index:2;
                padding:2rem 0;
                text-align:center;
                position:relative;
                font-size: 16px;
                color: #fff;
            }
        }

        .pro-modal-close {
            z-index:2;
            position: absolute;
            top: 0;
            right: 0;
            padding: 0 10px;
            color:#fff;
            font-size: 31px;
            line-height: 31px;
            cursor: pointer;

            @include breakpoint(medium down) {
                top:10px;
            }

            @include breakpoint(small only) {
                font-size:25px;
                line-height:25px;
                padding:0 5px;
            }
        }
    }
</style>
