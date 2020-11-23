<template>
    <div>
        <div class="fcst-summary-days-container">
            <div class="fcst-summary-days-wrapper">
                <ul class="fcst-summary-days" :class="{'active': dayActive, 'days16': fcstdays === 16 || fcstdays === 8, 'show8-16': dayrange === 16}" >
                    <sc-fcst-day v-for="(summaryDay,index) in summary" :key="index"
                        :day="summaryDay.day"
                        :date="summaryDay.dateNum"
                        :amsurf="summaryDay.amsurf"
                        :pmsurf="summaryDay.pmsurf"
                        :amcond="summaryDay.amcond"
                        :pmcond="summaryDay.pmcond"
                        :wxicon="summaryDay.wxicon"
                        :day-num="index+1"
                        :day-active="dayActive"
                        @changeactiveday="changeActiveFcstDay($event)"
                     ></sc-fcst-day>
                    <li class="fcst-summary-day summary-day-day16" v-if="fcstdays === 3" @click="showProModal">
                        <div class="grid-x">
                            <div class="small-12 cell day16-text">16 Day Forecasts</div>
                            <div class="small-10 small-offset-1 large-12 large-offset-0 cell summary-day-cond">
                                <div class="grid-x">
                                    <div class="small-6 large-12 cell">
                                        <div class="summary-day-am clean">$9.99 / yr</div>
                                    </div>
                                    <div class="small-6 large-12 cell">
                                        <div class="summary-day-pm clean">14 day trial</div>
                                    </div>
                                </div>
                            </div>
                            <div class="small-1 large-12 cell">
                                <div class="summary-day-expand"><i></i></div>
                            </div>
                        </div>
                        <div class="summary-day-bottom-white"></div>
                    </li>
                </ul>
                <div class="days-wrapper-gradient"></div>
            </div>
        </div>
        <transition :name="detailsTransition">
            <sc-fcst-details
                v-show="viewDetails"
                :summary="summary[detailDay]"
                :day-active="dayActive"
                @changeactiveday="changeActiveFcstDay($event)">
            </sc-fcst-details>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "fcst-summary.vue",
        props: ['summary','fcstdays','dayrange'],
        data() {
            return {
                dayActive:false,
                documentWidth:0,
                viewDetails: false
            }
        },
        methods: {
            changeActiveFcstDay(activeDay) {
                if (parseInt(activeDay) === parseInt(this.dayActive))
                    this.dayActive = false;
                else
                    this.dayActive = activeDay;
            },
            showProModal() {
                this.$root.toggleProModal('pro');

                gtag('event', 'pro_modal', {
                    'event_category' : 'engagement',
                    'event_label' : 'summary'
                });
            },
            isLargeScreen: function(width = document.documentElement.clientWidth) {
                return width >= 1024;
            },
            addSwipeDownListener() {
                let fcstDetails = document.getElementById('fcst-details');
                let fcstDetailsWrapper = document.getElementById('fcst-details-wrapper');
                let closeButton = document.getElementsByClassName('day-details-close')[0];
                let allowPullClose = true;
                let pageYStartPos = 0;
                let pageYDiff = 0;
                let pageYThreshold = 120;

                fcstDetailsWrapper.addEventListener("touchstart", touchStart);
                fcstDetailsWrapper.addEventListener("touchmove", touchMove);
                fcstDetailsWrapper.addEventListener("touchend", touchEnd);

                function touchMove(e) {
                    if (! allowPullClose)
                        return;

                    pageYDiff = e.touches.item(0).pageY - pageYStartPos;
                    if (pageYDiff > 0) {
                        fcstDetailsWrapper.classList.add('noscroll');
                        closeButton.classList.add('scrolling');
                        fcstDetails.style.top = pageYDiff.toString() + 'px';
                    }
                }

                function touchStart(e) {
                    allowPullClose = (fcstDetailsWrapper.scrollTop === 0);
                    pageYStartPos = e.touches.item(0).pageY;
                }

                function touchEnd() {
                    if (pageYDiff > pageYThreshold)
                        closeButton.click();
                    else {
                        fcstDetailsWrapper.classList.remove('noscroll');
                        closeButton.classList.remove('scrolling');
                        fcstDetails.style.top = '0px';
                    }

                }

                closeButton.addEventListener('click', function _func() {
                    fcstDetailsWrapper.removeEventListener('touchstart',touchStart);
                    fcstDetailsWrapper.removeEventListener('touchmove', touchMove);
                    fcstDetailsWrapper.removeEventListener('touchend', touchEnd);
                    closeButton.removeEventListener('click', _func);
                });

            }
        },
        created() {
            /*
            if (this.isLargeScreen())
                this.dayActive = 1;
            else
                this.viewDetails = false;
             */

        },
        mounted() {
            this.documentWidth = document.documentElement.clientWidth;
            let self = this;
            window.addEventListener('resize', function() {
                if (window.innerWidth !== window.SC.width) {
                    self.documentWidth = document.documentElement.clientWidth;
                    window.SC.width = window.innerWidth;
                }
            });
        },
        computed: {
            detailDay() {
                if (this.dayActive)
                    return this.dayActive - 1;
                else
                    return 1;
            },
            detailsTransition() {
                if (this.isLargeScreen())
                    return 'slide-details';
                else
                    return 'slide-details-mobile';
            }
        },
        watch: {
            dayActive(newVal,oldVal) {
                if (oldVal === false || newVal === false) {

                    this.viewDetails = ! this.viewDetails;

                    if (! this.isLargeScreen()) {

                        this.$root.toggleOverlay();

                        if (this.viewDetails) {

                            this.addSwipeDownListener();
                            gtag('event', 'fcst_day_details', {
                                'event_category' : 'forecast',
                                'event_label' : 'day'+newVal
                            });
                        } else {

                            setTimeout(function() {
                                document.getElementById('fcst-details').style.top = '0px';
                            },1000);
                        }
                    }
                }
            },
            documentWidth(newVal,oldVal) {
                if (this.isLargeScreen(oldVal) && ! this.isLargeScreen(newVal)) {
                    window.SC.hide(document.getElementById('fcst-details'));
                }
            }
        },
        components: {
            'sc-fcst-day': require('./fcst-day').default,
            'sc-fcst-details': require('./fcst-details').default
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    .slide-details-enter-active {
        @include transition(all 0.5s ease-in);
    }

    .slide-details-leave-active {
        @include transition(all 0.5s cubic-bezier(0, 1, 0.5, 1));
    }

    .slide-details-enter-to,.slide-details-leave {
        max-height: 600px;
        overflow: hidden;
    }

    .slide-details-enter,.slide-details-leave-to {
        overflow: hidden;
        max-height: 0;
    }

    .slide-details-mobile-enter-active, .slide-details-mobile-leave-active {
        transform: translateY(0vh);
        transition: all 0.5s ease-in-out;
    }

    .slide-details-mobile-enter, .slide-details-mobile-leave-to {
        transform: translateY(100vh);
    }


    #fcst-summary {
        background-color:rgba($blue-00,0.5);
        padding-bottom:24px;

        @include breakpoint(smedium down) {
            padding-bottom:14px;
        }

        .fcst-summary-header {
            width:$max-width-content-large;
            margin:0 auto;
            position:relative;

            @include breakpoint(xlarge only) {
                width:$max-width-content-xlarge;
            }

            @include breakpoint(xxlarge only) {
                width:$max-width-content-xxlarge;
            }

            @include breakpoint(medium only) {
                width:100%;
                padding-left:12px;
            }

            @include breakpoint(smedium down) {
                width:100%;
                padding:0 12px;
            }

            h1 {
                font-weight: 700;
                color: $blue-700;
                font-size: 24px;
                line-height: 32px;

                @include breakpoint(smedium down) {
                    font-size:22px;
                }
            }

            .fcst-range-8-16 {
                display:none;

                @include breakpoint(large up) {
                    color: $blue-500;
                    font-size: 13px;
                    display: block;
                    position: absolute;
                    right: 0;
                    bottom: 0;
                }

                @include breakpoint(xxlarge only) {
                    font-size:14px;
                }

                .fcst-range-btn {
                    display:inline-block;
                    padding:5px 10px;
                    cursor:pointer;
                }

                .arrow-left, .arrow-right {
                    height:13px;

                    @include breakpoint(xxlarge only) {
                        height:14px;
                    }
                }
            }
        }

        .fcst-summary-days-container {
            margin:0 auto;

            @include breakpoint(large only) {
                width:$max-width-content-large;
            }

            @include breakpoint(xlarge only) {
                width:$max-width-content-xlarge;
            }

            @include breakpoint(xxlarge only) {
                width:$max-width-content-xxlarge;
            }


            .fcst-summary-days-wrapper {

                @include breakpoint(large up) {
                    width: $max-width-content-large + 20;
                    overflow: hidden;
                    position: relative;
                    top: 9px;
                    padding-bottom: 9px;
                }

                @include breakpoint(xlarge only) {
                    width: $max-width-content-xlarge + 20;
                }

                @include breakpoint(xxlarge only) {
                    width: $max-width-content-xxlarge + 20;
                }

                .days-wrapper-gradient {
                    display:none;

                    @include breakpoint(large up) {
                        display:block;
                        position: absolute;
                        top: 0;
                        right: 0;
                        height: 100%;
                        width: 30px;
                        background-image: linear-gradient(to right, rgba(#ecf3f6, 0), rgba(#ecf3f6, 1));
                        z-index: 1;
                    }
                }
            }
        }
        ul.fcst-summary-days {
            list-style:none;
            margin:14px 0 0 0;
            font-size:0;
            width:100%;

            @include breakpoint(large up) {
                margin:5px 0 0 0;

                &.days16 {
                    width:250%;
                    position:relative;
                    left:0;
                    transition:1s;
                }
            }

            &.show8-16 {
                left:-947px;

                @include breakpoint(xlarge only) {
                    left:-1132px;
                }

                @include breakpoint(xxlarge only) {
                    left:-1372px;
                }
            }

            @include breakpoint(medium down) {
                margin:16px 0 0 0;
                padding:0 12px;
            }

            @include breakpoint(large) {
                &.active {

                    li.day-active {
                        border: 1px solid $gray-300;
                    }

                    li:not(.day-active) {
                        opacity: 0.5;
                        border:none;
                    }

                    .summary-day-bottom-white {
                        display: block;
                    }
                }
            }
        }
    }
</style>
