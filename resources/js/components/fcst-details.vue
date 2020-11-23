<template>
    <section id="fcst-details">
        <div id="fcst-details-wrapper">
        <div class="day-details-close" @click="$emit('changeactiveday',dayActive)"></div>
        <div class="day-top-summary hide-for-large">
            <div class="summary-day-text">{{ summary.date }}</div>
            <div class="summary-day-wx"><img :src="wxIconImage" alt="Weather Icon"></div>
            <div class="grid-x">
                <div class="small-6 large-12 cell">
                    <div class="summary-day-am" :class="summary.amcond"><span class="summary-ampm">AM</span> {{ summary.amsurf }}</div>
                </div>
                <div class="small-6 large-12 cell">
                    <div class="summary-day-pm" :class="summary.pmcond"><span class="summary-ampm">PM</span> {{ summary.pmsurf }}</div>
                </div>
            </div>
        </div>
        <div>
            <div class="day-summary">
                <!--<div class="day-date">{{ upperCase(summary.date) }}</div>-->
                <div class="grid-x">
                    <div class="large-6 day-summary-text day-summary-surf">
                        <span class="day-summary-label">SURF:</span>{{ summary.surfTxt}}
                    </div>
                    <div class="large-6 day-summary-text day-summary-cond">
                        <span class="day-summary-label">CONDITIONS:</span>{{ summary.condTxt }}
                    </div>
                </div>
            </div>
            <div class="grid-x">
                <div class="day-details-left cell small-12 large-auto">
                    <div class="details-left-header">
                        <h1>DAILY DETAILS</h1>
                        <div class="detail-toggle-hours">
                            <span class="detail-toggle-three-hour" :class="{active: threeHourActive}" @click="toggleThreeHour">3 hrs</span>
                            <div class="hours-toggle">
                                <div class="three-hour-toggle" :class="{active: threeHourActive}" @click="toggleThreeHour"></div>
                                <div class="hourly-toggle" :class="{active: hourlyActive}" @click="toggleHourly"></div>
                            </div>
                            <span class="detail-toggle-hourly" :class="{active: hourlyActive}" @click="toggleHourly">Hourly</span>
                        </div>
                    </div>
                    <table>
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <transition-group name="fade" tag="tbody">
                        <tr v-for="(hour,index) in summary.hourly"
                            v-if="hour.wind !== '' &&
                            (threeHourActive && index%3 === 0 && index >= 6 && index <= 18 || hourlyActive)"
                            :key="'key'+index">
                            <td><div class="day-hour-txt" :class="hour.cond">{{ hourTime(index) }}</div></td>
                            <td>{{ hour.wind }}</td>
                            <td><span class="hourly-swell">{{ hour.swell1 }}</span><br><span class="hourly-swell">{{ hour.swell2 }}</span>
                            </td>
                            <td v-html="hourTemp(hour.temp,hour.wxicon)"></td>
                        </tr>
                        </transition-group>
                    </table>
                </div>
                <div class="day-details-right cell small-12 large-shrink">
                    <div class="day-details-right-content">
                        <div class="grid-x">
                            <div class="cell medium-6 large-12">
                                <div class="day-details-am-contents">
                                    <div class="day-details-am" :class="summary.amcond"><span class="day-details-ampm">AM</span> {{ summary.amsurf }}</div>
                                    <div class="day-details-am-lowtide">
                                        <span class="day-details-label">LOW TIDE</span>
                                        <span class="day-details-value">{{ summary.low_am }}</span>
                                    </div>
                                    <div class="day-details-am-hightide">
                                        <span class="day-details-label">HIGH TIDE</span>
                                        <span class="day-details-value">{{ summary.high_am }}</span>
                                    </div>
                                    <div class="day-details-firstlight">
                                        <span class="day-details-label">FIRST LIGHT</span>
                                        <span class="day-details-value">{{ summary.firstlight }}</span>
                                    </div>
                                    <div class="day-details-srise">
                                        <span class="day-details-label">SUNRISE</span>
                                        <span class="day-details-value">{{ summary.sunrise }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="cell medium-6 large-12">
                                <div class="day-details-pm-contents">
                                    <div class="day-details-pm" :class="summary.pmcond"><span class="day-details-ampm">PM</span> {{ summary.pmsurf }}</div>
                                    <div class="day-details-pm-lowtide">
                                        <span class="day-details-label">LOW TIDE</span>
                                        <span class="day-details-value">{{ summary.low_pm }}</span>
                                    </div>
                                    <div class="day-details-pm-hightide">
                                        <span class="day-details-label">HIGH TIDE</span>
                                        <span class="day-details-value">{{ summary.high_pm }}</span>
                                    </div>
                                    <div class="day-details-srise">
                                        <span class="day-details-label">SUNSET</span>
                                        <span class="day-details-value">{{ summary.sunset }}</span>
                                    </div>
                                    <div class="day-details-lastlight">
                                        <span class="day-details-label">LAST LIGHT</span>
                                        <span class="day-details-value">{{ summary.lastlight }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</template>

<script>
    export default {
        name: "fcst-details.vue",
        props: ['dayActive','summary'],
        data: function() {
            return {
                threeHourActive: true,
                hourlyActive: false,
            }
        },
        methods: {
            hourTime(hour) {
                if (parseInt(hour) === 0)
                    return '12am';
                else if (parseInt(hour) < 12)
                    return hour+'am';
                else if (parseInt(hour) === 12)
                    return '12pm';
                else
                    return (hour-12)+'pm';
            },
            toggleThreeHour() {
                this.threeHourActive = true;
                this.hourlyActive = false;
                localStorage.setItem('sc_hourly','3');

                gtag('event', 'hourly', {
                    'event_category' : 'forecast',
                    'event_label' : 3
                });
            },
            toggleHourly() {
                this.threeHourActive = false;
                this.hourlyActive = true;
                localStorage.setItem('sc_hourly','1');

                gtag('event', 'hourly', {
                    'event_category' : 'forecast',
                    'event_label' : 1
                });
            },
            upperCase(text) {
                return window.SC.upperCase(text);
            },
            wxicon(image) {
                return '/images/wx-icons/'+image+'.svg'
            },
            hourTemp(temp,wxicon) {
                if (temp)
                    return temp+' <img class="hourly-wxicon" src="/images/wx-icons/wi-'+wxicon+'.svg" alt="Weather Icon">';
                else
                    return '';
            }
        },
        computed: {
            wxIconImage() {
                return '/images/wx-icons/wi-'+this.summary.wxicon+'.svg';
            }
        },
        created() {
            let scHourlyCookie = parseInt(localStorage.getItem('sc_hourly'));
            if (scHourlyCookie === 1) {
                this.threeHourActive = false;
                this.hourlyActive = true;
            } else {
                this.threeHourActive = true;
                this.hourlyActive = false;
            }
        },
        mounted() {
        }
    }
</script>

<style lang="scss">

@import '../../sass/sc-settings.scss';

#fcst-details {
    background:#fff;
    box-shadow: 0 0 12px 0 rgba(49,50,57,0.20);
    width:982px;
    margin:0 auto;
    border-radius:2px;
    position: relative;
    border-top: 1px solid $gray-300;
    z-index: 3;

    @include breakpoint(xxlarge only) {
        width:1400px;
    }

    @include breakpoint(xlarge only) {
        width:1200px;
    }

    @include breakpoint(medium down) {
        position: fixed;
        top: 0;
        left: 0;
        bottom:0;
        right:0;
        width:100%;
        box-shadow: none;
        border-radius: 0;
        z-index: 4;
    }

    #fcst-details-wrapper {
        height:100%;
        overflow-y:auto;
        overscroll-behavior: contain;

        &.noscroll {
            overflow:hidden;
        }

        @include breakpoint(medium down) {
            padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
        }
    }

    .day-details-close {
        position: fixed;
        right:5px;
        top:5px;
        padding:15px 15px;
        background-image: url(/images/icon/close_black.svg);
        background-position: center;
        background-repeat: no-repeat;
        background-size: 14px 14px;
        background-color:#fff;
        border-radius:4px;
        cursor:pointer;

        &.scrolling {
            position:absolute;
        }

        @include breakpoint(large) {
            display:none;
        }
    }

    .day-top-summary {
        margin:12px;
        text-align:center;

        .summary-day-text {
            font-size: 12px;
            line-height: 24px;
            color: $gray-500;
        }

        .summary-day-wx {
            margin:4px 0 6px 0;
            img {
                width:46px;
            }
        }

        .summary-day-am, .summary-day-pm {
            display:none;
            font-size:16px;
            margin: 6px auto;
            color: $blue-800;
            text-align: center;
            border-radius: 14px;
            line-height: 28px;
            width:100%;

            .summary-ampm {
                font-size:11px;
                color: $blue-700;
            }

            @include breakpoint(medium only) {
                display:inline-block;
                width:75%;
            }

            @include breakpoint(smedium down) {
                display: inline-block;

                &.summary-day-pm, &.summary-day-am {
                    width:92%;
                }
            }

            &.clean {
                background: $clean-cond;
            }

            &.fair {
                background: $fair-cond;
            }

            &.choppy {
                background: $poor-cond;
            }
        }
    }

    .day-summary {
        border-bottom:2px solid $blue-00;
        padding: 24px 35px;

        @include breakpoint(medium down) {
            padding:12px;
        }

        .day-date {
            display:none;

            @include breakpoint(large) {
                display:block;
                padding-bottom: 4px;
                font-size: 16px;
                color: $gray-900;
            }
            @include breakpoint(xxlarge only) {
                font-size:18px;
                padding-bottom:8px;
            }
        }

        .day-summary-surf,.day-summary-cond {
            padding-right:1rem;

            @include breakpoint(xxlarge only) {
                padding-right:2rem;
            }

            &.day-summary-surf {
                @include breakpoint(medium down) {
                    padding-right:0;
                }
            }

            &.day-summary-cond {
                @include breakpoint(medium down) {
                    margin-top:12px;
                    padding-left:0;
                }
            }
        }

        .day-summary-text {
            color:#12524E;
            font-weight:300;
            font-size:18px;
            @include breakpoint(xxlarge only) {
                font-size:20px;
            }
            @include breakpoint(medium down) {
                font-size:16px;
            }

            .day-summary-label {
                font-size:12px;
                color:$gray-350;
                font-weight:normal;
                padding-right:8px;
                @include breakpoint(xxlarge only) {
                    font-size:14px;
                }
            }
        }
    }

    .day-details-left {
        padding-bottom: 18px;

        @include breakpoint(medium down) {
            padding: 0 12px;
        }

        .details-left-header {
            padding:8px 35px;
            border-bottom:2px solid $blue-00;

            @include breakpoint(medium down) {
                padding:8px 0;
            }

            h1 {
                display:inline-block;
                font-size:16px;
                line-height:16px;
                color: $gray-900;
                font-weight:normal;
            }

            .detail-toggle-hours {
                float:right;
                font-size: 12px;
                color: #787C7C;
                display:inline-block;

                .detail-toggle-three-hour, .detail-toggle-hourly {
                    cursor:pointer;
                    &.active {
                        font-weight: bold;
                        color: $blue-700;
                    }

                    @include breakpoint(medium down) {
                        vertical-align:top;
                        font-size:13px;
                    }
                }

                .hours-toggle {
                    display:inline-block;
                    position: relative;
                    top: 3px;
                    background: $blue-700;
                    border-radius: 10px;
                    width:36px;
                    height:16px;
                    margin: 0 2px;
                    cursor:pointer;

                    @include breakpoint(medium down) {
                        width:50px;
                        height:22px;
                        top:-2px;
                    }

                    .three-hour-toggle, .hourly-toggle {
                        display:inline-block;
                        width:12px;
                        height:12px;
                        margin: 2px;
                        border-radius: 50%;

                        @include breakpoint(medium down) {
                            width: 16px;
                            height:16px;
                            margin:3px;
                        }

                        &.active {
                            background: #fff;
                        }
                    }
                }
            }
        }

        table {
            width:100%;
            text-align: center;
            padding:0 35px;
            border-spacing: 0;

            @include breakpoint(medium down) {
                padding:0;
            }

            td {
                border-top:2px solid $blue-00;
                padding:5px 0;

                @include breakpoint(xxlarge only) {
                    padding:15px 0;
                }

                @include breakpoint(xlarge only) {
                    padding:10px 0;
                }
            }

            thead {
                color: $gray-350;
                line-height: 24px;

                th {
                    padding:20px 0 4px;
                    font-size: 12px;
                    font-weight:400;

                    @include breakpoint(medium down) {
                        padding:12px 0 4px;
                    }
                }

                th:first-child {
                    width:10%;
                }
                th:nth-child(2) {
                    width:20%;

                    @include breakpoint(medium down) {
                        width:28%;
                    }

                    &:after {
                        content: 'WIND';
                    }
                }
                th:nth-child(3) {
                    width:45%;

                    @include breakpoint(medium down) {
                        width:42%;
                    }

                    &:after {
                        content: 'SWELL';
                    }
                }
                th:nth-child(4) {
                    width:25%;

                    @include breakpoint(medium down) {
                        width:20%;
                    }

                    &:after {
                        content: 'WEATHER';
                    }
                }
            }

            tbody {

                td {
                    font-size: 16px;
                    line-height: 24px;
                    color: $gray-500;

                    @include breakpoint(xxlarge only) {
                        font-size:18px;
                    }

                    @include breakpoint(xlarge only) {
                        font-size:17px;
                    }

                    @include breakpoint(medium down) {
                        font-size:14px;
                    }

                    @include breakpoint(small only) {
                        font-size:12px;
                    }

                    .hourly-swell {
                        white-space:pre;
                    }

                    .hourly-wxicon {
                        width:28px;
                        height:28px;
                    }
                }

                tr:nth-child(even) {
                    background: rgba(#efefef,0.5);
                }

                td:first-child {
                    font-size: 12px;
                    line-height: 26px;
                    color: $gray-700;
                    height:42px;

                    div.day-hour-txt {
                        border-radius:50%;
                        width:42px;
                        line-height:42px;

                        @include breakpoint(medium down) {
                            width:34px;
                            line-height:34px;
                            font-size:10px;
                        }

                        &.clean {
                            background: $clean-cond;
                        }
                        &.fair {
                            background: $fair-cond;
                        }
                        &.choppy {
                            background: $choppy-cond;
                        }
                    }
                }
            }
        }

        .day-details-hourly-toggle {
            background: #D7D9E0;
            border-radius: 17px;
            font-size: 12px;
            color: #fff;
            float:right;
            margin-top:14px;
            cursor:pointer;

            .day-details-hourly-3hrs, .day-details-hourly-1hr {
                display:inline-block;
                border-radius: 17px;
                text-align:center;
                padding:0 9px;
                line-height:28px;

                &.hourly-active {
                    background: #4476DB;
                }
            }
        }
    }

    .day-details-right {
        width: 294px;
        background: $gray-100;
        border-radius: 2px;
        padding:31px 29px 38px;

        @include breakpoint(xxlarge only) {
            width:420px;
        }

        @include breakpoint(xlarge only) {
            width:360px;
        }

        @include breakpoint(medium down) {
            width: 100%;
            padding: 28px 0 120px;

            .day-details-right-content {
                width: 100%;
                margin:0 auto;
            }
        }

        .day-details-am-contents, .day-details-pm-contents {
            width:236px;
            margin:0 auto;

            @include breakpoint (xlarge) {
                width:75%;
            }

            @include breakpoint (medium only) {
                width:75%;
            }

            @include breakpoint (smedium down) {
                width: 100%;
                max-width: 300px;
                margin: 0 auto;
                padding: 0 12px;
            }
        }

        .day-details-am, .day-details-pm {
            border-radius: 14px;
            font-size: 16px;
            color: $blue-800;
            text-align: center;
            line-height: 28px;
            width:100%;

            .day-details-ampm {
                font-size:11px;
                color: $blue-700;
            }

            &.clean {
                background: $clean-cond;
            }
            &.fair {
                background: $fair-cond;
            }
            &.poor, &.choppy {
                background: $poor-cond;
            }

            &.day-details-pm {
                margin-top: 19px;

                @include breakpoint(medium only) {
                    margin-top:0;
                }
            }
        }

        .day-details-label,.day-details-value {
            font-size:16px;
            line-height:26px;
            display:inline-block;
            color:$gray-500;

            &.day-details-label {
                margin:10px 14px 0 0;
                color:$gray-900;
            }

            @include breakpoint(xxlarge only) {
                font-size:18px;
                line-height:30px;
            }
        }
    }
}
</style>
