<template>
    <div id="fcst" :class="{'simple-mode': simpleMode}">
        <section id="fcst-current">
            <div class="fcst-current-header grid-x">
                <span class="fcst-current-cond cell shrink" :class="data.weather.nowCond"></span>
                <h1 class="cell auto">Surf Conditions are {{ data.weather.nowSurf }}
                    <span v-if="data.weather.nowSurf !== 'flat'"> and {{ data.weather.nowCondDesc }}</span> right now in <span class="no-wrap" v-html="data.title"></span>
                </h1>
            </div>
            <ul class="fcst-current-data">
                <li class="current-data-temp">
                    <div class="grid-x current-data-container">
                        <div class="cell shrink">
                            <div class="current-data-icon wx-icon" :class="'wi-'+data.weather.sky_icon" :title="data.weather.sky"></div>
                        </div>
                        <div class="cell auto">
                            <div class="current-data-info">
                                <div class="current-data-label">WEATHER
                                    <button class="show-for-large" v-tooltip="weatherInfo"><img src="/images/icon/info.svg" class="icon-info" alt=""/></button>
                                </div>
                                <div class="current-data-desc" v-html="currentWeather"></div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="current-data-tide">
                    <div class="grid-x current-data-container">
                        <div class="cell shrink">
                            <div class="current-data-icon tide-icon"></div>
                        </div>
                        <div class="cell auto">
                            <div class="current-data-info">
                                <div class="current-data-label">LOW / HIGH TIDE
                                    <button class="show-for-large" v-tooltip="'The closest low and high tides to now.'"><img src="/images/icon/info.svg" class="icon-info" alt=""/></button>
                                </div>
                                <div class="current-data-desc">{{ data.weather.low_tide }} / {{ data.weather.high_tide }}</div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="current-data-wind">
                    <div class="grid-x current-data-container">
                        <div class="cell shrink">
                            <div class="current-data-icon buoy-icon"></div>
                        </div>
                        <div class="cell auto">
                            <div class="current-data-info">
                                <div class="current-data-label">BUOY {{ data.weather.buoy }}
                                    <button class="show-for-large" v-tooltip="buoyInfo"><img src="/images/icon/info.svg" class="icon-info" alt=""/></button>
                                </div>
                                <div class="current-data-desc" v-html="currentBuoy"></div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="current-data-wtmp">
                    <div class="grid-x current-data-container">
                        <div class="cell shrink">
                            <div class="current-data-icon water-icon"></div>
                        </div>
                        <div class="cell auto">
                            <div class="current-data-info">
                                <div class="current-data-label">WATER TEMP
                                    <button class="show-for-large" v-tooltip="sstInfo"><img src="/images/icon/info.svg" class="icon-info" alt=""/></button>
                                </div>
                                <div class="current-data-desc">{{ data.weather.sst }}&deg; <span class="wetsuit">{{ data.weather.wetsuit }}</span></div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <div id="fcst-toggles" class="grid-x">
                <!--
                <div class="cell small-6" v-if="!isLargeScreen">
                    <sc-simple-toggle
                        :simple-mode="simpleMode"
                        @togglesimplemode="toggleSimpleMode"
                    ></sc-simple-toggle>
                </div>
                -->
                <div class="cell small-12">
                    <sc-fcst-add-fav
                        :location-beach-id="data.beachId"
                        :logged-in="data.loggedIn"
                        @addfavmessage="addFavMessage($event)"
                    ></sc-fcst-add-fav>
                </div>
                <transition name="slide">
                    <div v-if="addFavMessageTxt" id="add-fav-message-txt" :class="addFavMessageClass" @click="closeMsg">
                        {{ addFavMessageTxt }}
                        <span class="close-msg" aria-label="Close Favorites Message">&times;</span>
                    </div>
                </transition>
            </div>
        </section>
        <transition name="fade">
            <section id="fcst-timeline" v-if="!simpleMode">
                <div class="grid-x fcst-timeline-header">
                    <div class="cell small-12 medium-7 large-9">
                        <h1>Forecast</h1>
                    </div>
                    <div class="cell small-12 medium-5 large-3 timeline-toggle grid-x">
                        <div class="cell small-6">
                            <sc-fcst-surfswell-toggle
                                @changesurfswell="changeSurfSwell"
                            ></sc-fcst-surfswell-toggle>
                        </div>
                        <div class="cell small-6 fcst-day-toggle">
                            <sc-fcst-days-toggle
                                @changefcstdays="changeFcstDays"
                                :fcstdays="fcstDays"
                            ></sc-fcst-days-toggle>
                            <sc-fcst-day-range
                                v-if="fcstDays === 8 || fcstDays === 16"
                                @changedayrange="changeDayRange"
                                :dayrangeval="dayrange"
                            ></sc-fcst-day-range>
                        </div>
                    </div>
                </div>
                <sc-fcst-timeline
                    :timeline="data.timeline"
                    :nowhour="data.nowHour"
                    :surfswell="surfswell"
                    :fcstdays="fcstDays"
                    :dayrange="dayrange"
                ></sc-fcst-timeline>
            </section>
        </transition>
        <section id="fcst-summary">
            <div class="fcst-summary-header">
                <h1>Forecast <span v-if="!simpleMode">Details</span></h1>
                <sc-fcst-day-range
                    v-if="fcstDays === 8 || fcstDays === 16"
                    @changedayrange="changeDayRange"
                    :dayrangeval="dayrange"
                ></sc-fcst-day-range>
            </div>
            <sc-fcst-summary
                :summary="data.days"
                :fcstdays="fcstDays"
                :dayrange="dayrange"
            ></sc-fcst-summary>
        </section>
        <section id="fcst-info">
            <div id="fcst-info-container">
                <div id="fcst-info-text">This forecast is for {{ data.beachFaceDir }}, and was updated {{ data.updated }}.</div>
                <div id="fcst-info-more"> <a href="/faq">Read more</a> about the Surf Captain forecasts.</div>
            </div>
        </section>
        <transition name="fade">
            <aside id="fcst-loading-msg" v-if="loadingMsg">
                <div class="fcst-loading-msg-txt">{{ loadingMsg }}</div>
            </aside>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "fcst.vue",
        props:['fcstdata','reloaddata'],
        data() {
          return {
              data: false,
              surfswell: 'surf',
              fcstDays: 3,
              dayrange: 8,
              loadingMsg: false,
              isSmallScreen: false,
              isLargeScreen: false,
              simpleMode: false,
              addFavMessageTxt: false,
              addFavMessageClass: false
          }
        },
        created() {
            this.data = JSON.parse(this.fcstdata);
            this.setFcstDays(this.data.fcst_days);

            if (!this.$root.isLargeScreen() && localStorage.getItem('sc_simple') === 'true')
                this.simpleMode = true;

            const self = this;
            let lastDataUpdate = Math.floor(Date.now() / 1000);

            this.initializeScreenSize();

            window.addEventListener('resize', function() {
                if (window.innerWidth !== window.SC.width) {
                    self.initializeScreenSize();
                    window.SC.width = window.innerWidth;
                }
            });

            // look for browser visibility state change to see if we need to reload data
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    // stop running expensive task
                } else {
                    // page has focus, check for actions
                    //console.log(Math.floor(Date.now() / 1000),self.data.fcst_expires,self.data.weather.expires);
                    let nowTimestamp = Math.floor(Date.now() / 1000);

                    //console.log('last data update',lastDataUpdate,nowTimestamp);
                    if (nowTimestamp > (lastDataUpdate + 300)) { //five minutes after last update attempt

                        if (self.data.fcst_expires && nowTimestamp > self.data.fcst_expires) {
                            self.loadData();
                            lastDataUpdate = nowTimestamp;
                        }
                        else if (self.data.weather.expires && nowTimestamp > self.data.weather.expires) {
                            self.loadWeather();
                            lastDataUpdate = nowTimestamp;
                        }
                    }
                }
            });
        },
        methods: {
            changeSurfSwell: function(val) {
                this.surfswell = val;
                gtag('event', 'surfswell', {
                    'event_category' : 'forecast',
                    'event_label' : val
                });
            },
            changeFcstDays: function(val) {
                this.fcstDays = val;
                gtag('event', 'fcst_days', {
                    'event_category' : 'forecast',
                    'event_label' : val
                });
            },
            initializeScreenSize() {
                this.isLargeScreen = this.$root.isLargeScreen();
                this.isSmallScreen = this.$root.isSmallScreen();

                if (this.isSmallScreen) {
                    this.dayrange = 6;

                } else {
                    this.dayrange = 8;

                    if (!this.$root.isLargeScreen() && localStorage.getItem('sc_simple') === 'true')
                        this.simpleMode = true;

                    if (this.$root.isLargeScreen())
                        this.isLargeScreen = true;
                }
            },
            changeDayRange: function(days) {
                this.dayrange = days;

                let event = (this.$root.isLargeScreen()) ? 'summary_day_range' : 'timeline_day_range';
                gtag('event', event, {
                    'event_category' : 'forecast',
                    'event_label' : days
                });
            },
            setFcstDays: function(fcst_days) {
                if (parseInt(fcst_days) === 3)
                    this.fcstDays = 3;
                else if (parseInt(localStorage.getItem('sc_fcst-days')) === 16)
                    this.fcstDays = 16;
                else
                    this.fcstDays = 8;
            },
            analyticsPageRefresh() {
                gtag('config', 'UA-167105392-1');
            },
            loadData(callBack=false) {
                //fetch new data
                const self = this;

                axios({
                    method: 'get',
                    url: '/forecast/'+this.data.slug,
                    params: {
                        isAjax: true
                    }
                    //headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        self.data = response.data;
                        self.setFcstDays(self.data.fcst_days);
                        self.loadingMsg = 'Forecast Updated';
                        setTimeout(function () {
                            self.loadingMsg = false;
                        }, 2000);
                        self.$root.reloadFcstData = false;
                        self.analyticsPageRefresh();

                        if (callBack)
                            callBack();
                    })
                    .catch(function (error) {
                        console.log('There was an error changing the forecast days',error);
                        self.loadingMsg = false;
                        return false;
                    });
            },
            loadWeather() {
                //this.loadingMsg = 'Refreshing Current Conditions';
                const self = this;

                axios({
                    method: 'get',
                    url: '/weather/'+this.data.slug,
                    params: {
                        isAjax: true
                    }
                    //headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.wvht)
                            self.data.weather.wvht = response.data.wvht;
                        if (response.data.wvper)
                            self.data.weather.wvper = response.data.wvper;
                        if (response.data.buoy)
                            self.data.weather.buoy = response.data.buoy;
                        if (response.data.sky)
                            self.data.weather.sky = response.data.sky;
                        if (response.data.sky_icon)
                            self.data.weather.sky_icon = response.data.sky_icon;
                        if (response.data.wind_spd)
                            self.data.weather.wind_spd = response.data.wind_spd;
                        if (response.data.wind_dir)
                            self.data.weather.wind_dir = response.data.wind_dir;
                        if (response.data.atmp)
                            self.data.weather.atmp = response.data.atmp;
                        if (response.data.nowSurf)
                            self.data.weather.nowSurf = response.data.nowSurf;
                        if (response.data.nowCond)
                            self.data.weather.nowCond = response.data.nowCond;
                        if (response.data.nowCondDesc)
                            self.data.weather.nowCondDesc = response.data.nowCondDesc;
                        if (response.data.expires !== self.data.weather.expires) {
                            self.data.weather.expires = response.data.expires;
                            self.loadingMsg = 'Current Conditions Updated';
                            setTimeout(function() {
                                self.loadingMsg = false;
                            },2000);
                        }

                        self.analyticsPageRefresh();
                    })
                    .catch(function (error) {
                        console.log('There was an error fetching weather data',error);
                        self.loadingMsg = false;
                    });
            },
            toggleSimpleMode() {
                this.simpleMode = ! this.simpleMode;
                localStorage.setItem("sc_simple", this.simpleMode);
            },
            addFavMessage(e) {
                if (e.success) {
                    this.addFavMessageTxt = e.success;
                    this.addFavMessageClass = 'add-fav-success';
                } else {
                    this.addFavMessageTxt = e.error;
                    this.addFavMessageClass = 'add-fav-error';
                }
            },
            closeMsg() {
                this.addFavMessageTxt = false;
                this.addFavMessageClass = false;
            }
        },
        computed: {
            weatherInfo() {
                if (this.data.weather.wind_station)
                    return 'Nearby Wind Station '+this.data.weather.wind_station;
                else
                    return 'No active nearby wind station. Using forecast data.';
            },
            buoyInfo() {
                if (this.data.weather.buoy === 'FORECAST')
                    return 'No active nearby Buoys. Using current forecast data.';
                else
                    return 'Latest wave height and wave period from Buoy '+this.data.weather.buoy;
            },
            sstInfo() {
                if (this.data.weather.sst_station)
                    return 'Water temperature from nearby buoy station '+this.data.weather.sst_station;
                else
                    return 'No nearby water temp measurements, using satellite derived data from yesterday.';
            },
            currentWeather() {
                return this.data.weather.atmp + '&deg; ' + this.data.weather.wind_dir + ' @ ' + this.data.weather.wind_spd;
            },
            currentBuoy() {
                if (this.data.weather.wvht)
                    return this.data.weather.wvht+' @ '+this.data.weather.wvper+'sec';
                else
                    return 'No Waves';
            }
        },
        watch: {
            reloaddata(newVal,oldVal) {
                if (newVal === true) {
                    this.loadingMsg = 'Refreshing Forecast Data';
                    this.loadData();
                }
            }
        },
        components: {
            'sc-fcst-surfswell-toggle': require('./fcst-surfswell-toggle').default,
            'sc-fcst-days-toggle': require('./fcst-days-toggle').default,
            'sc-fcst-add-fav': require('./fcst-add-fav').default,
            'sc-fcst-timeline': require('./fcst-timeline').default,
            'sc-fcst-summary': require('./fcst-summary').default,
            'sc-fcst-day-range': require('./fcst-day-range').default,
            'sc-simple-toggle': require('./main-simple-toggle').default
        }
    }
</script>

<style lang="scss">
    @import '../../sass/_sc-settings.scss';

    #fcst-current {
        width:100%;
        padding:30px 12px 0 12px;
        background:#fff;
        box-shadow: 0 0 12px 0 rgba(49,50,57,0.20);
        position:relative;

        @include breakpoint(medium only) {
            padding:24px 12px 0 12px;
        }
        @include breakpoint(smedium down) {
            padding:8px 12px 0 12px;
        }

        .fcst-current-header {
            width:100%;
            max-width:835px;
            margin:0 auto;

            @include breakpoint(large only) {
                max-width:800px;
            }

            @include breakpoint(medium down) {
                max-width:700px;
            }

            .fcst-current-cond {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                margin: 10px 8px 0 0;
                float:left;

                &.clean {
                     background: $clean-cond;
                 }
                &.fair {
                     background: $fair-cond;
                 }
                &.choppy {
                     background: $choppy-cond;
                 }
                &.none {
                     background: $none-cond;
                 }
            }

            h1 {
                font-weight: 900;
                color: $dark-blue-heading;
                font-size: 18px;
                line-height: 30px;
                margin-bottom:14px;

                @include breakpoint(medium only) {
                    font-size: 24px;
                    line-height: 34px;
                    margin-bottom: 19px;
                }

                @include breakpoint(large) {
                    font-size: 34px;
                    line-height: 48px;
                    margin-bottom:28px;
                }

                @include breakpoint(xlarge only) {
                    font-size: 36px;
                }

                @include breakpoint(smedium down) {
                    .no-wrap {
                        white-space: normal !important;
                    }
                }
            }
        }


        button.has-tooltip {
            margin-left: 2px;
        }

        .icon-info {
            height:16px;
        }

        ul {
            margin: 0;
            list-style: none;
            text-align: center;
            font-size:0;
        }

        li {
            text-align:left;

            @include breakpoint(large) {
                display:inline-block;
                &:not(:last-child) {
                    margin-right:50px;
                }

                @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) { //IE10+
                    width:200px;
                }
                //all Edge browsers, version 12+, even though this really only needs to be applied to edge less than 18
                @supports (-ms-ime-align:auto) {
                    width:200px;
                }
            }

            @include breakpoint(medium only) {
                display:inline-block;
                width:50%;
                margin-bottom:22px;
            }

            @include breakpoint(smedium down) {
                margin-bottom:8px;
                line-height: 0;
            }

            .current-data-container {
                @include breakpoint(medium only) {
                    max-width:250px;
                    margin:0 auto;
                }
                @include breakpoint (smedium only) {
                    max-width:365px;
                    margin:0 auto;
                }
            }

            .current-data-icon {
                display:inline-block;
                width:42px;
                height:42px;
                border-radius: 50%;
                background-color: $blue-100;
                background-position: center;
                background-repeat: no-repeat;
                margin-right:8px;

                @include breakpoint(medium only) {
                    margin-right:12px;
                }

                @include breakpoint(smedium down) {
                    width:30px;
                    height:30px;
                }

                &.wx-icon {

                    background-size: 30px 30px;

                    @include breakpoint(smedium down) {
                        background-size: 26px 26px;
                    }

                    &.wi-day-sunny {
                         background-image: url('/images/wx-icons/wi-day-sunny.svg');
                     }
                    &.wi-day-cloudy {
                         background-image: url('/images/wx-icons/wi-day-cloudy.svg');
                     }
                    &.wi-day-cloudy-windy {
                         background-image: url('/images/wx-icons/wi-day-cloudy-windy.svg');
                     }
                    &.wi-day-fog {
                         background-image: url('/images/wx-icons/wi-day-fog.svg');
                     }
                    &.wi-day-rain {
                         background-image: url('/images/wx-icons/wi-day-rain.svg');
                     }
                    &.wi-day-rain-mix {
                         background-image: url('/images/wx-icons/wi-day-rain-mix.svg');
                     }
                    &.wi-day-snow {
                         background-image: url('/images/wx-icons/wi-day-snow.svg');
                     }
                    &.wi-day-sleet {
                         background-image: url('/images/wx-icons/wi-day-sleet.svg');
                     }
                    &.wi-night-alt-sleet {
                         background-image: url('/images/wx-icons/wi-night-alt-sleet.svg');
                     }
                    &.wi-day-sunny-overcast {
                         background-image: url('/images/wx-icons/wi-day-sunny-overcast.svg');
                     }
                    &.wi-day-windy {
                         background-image: url('/images/wx-icons/wi-day-windy.svg');
                     }
                    &.wi-day-haze {
                        background-image: url('/images/wx-icons/wi-day-haze.svg');
                    }
                    &.wi-strong-wind {
                         background-image: url('/images/wx-icons/wi-strong-wind.svg');
                     }
                    &.wi-night-clear {
                         background-image: url('/images/wx-icons/wi-night-clear.svg');
                     }
                    &.wi-night-alt-cloudy {
                         background-image: url('/images/wx-icons/wi-night-alt-cloudy.svg');
                     }
                    &.wi-night-alt-cloudy-windy {
                         background-image: url('/images/wx-icons/wi-night-alt-cloudy-windy.svg');
                     }
                    &.wi-thunderstorm {
                         background-image: url('/images/wx-icons/wi-thunderstorm.svg');
                     }
                    &.wi-day-thunderstorm {
                         background-image: url('/images/wx-icons/wi-day-thunderstorm.svg');
                     }
                    &.wi-night-alt-thunderstorm {
                         background-image: url('/images/wx-icons/wi-night-alt-thunderstorm.svg');
                     }
                    &.wi-night-alt-rain {
                         background-image: url('/images/wx-icons/wi-night-alt-rain.svg');
                     }
                    &.wi-night-alt-rain-mix {
                         background-image: url('/images/wx-icons/wi-night-alt-rain-mix.svg');
                     }
                    &.wi-cloudy {
                         background-image: url('/images/wx-icons/wi-cloudy.svg');
                     }
                    &.wi-cloudy-windy {
                        background-image: url('/images/wx-icons/wi-cloudy-windy.svg');
                    }
                    &.wi-rain {
                         background-image: url('/images/wx-icons/wi-rain.svg');
                     }
                    &.wi-snow {
                         background-image: url('/images/wx-icons/wi-snow.svg');
                     }
                    &.wi-windy {
                        background-image: url('/images/wx-icons/wi-windy.svg');
                    }
                }

                &.tide-icon {
                    background-size: 24px 24px;

                    @include breakpoint(smedium down) {
                        background-size: 16px 16px;
                    }

                    background-image: url('/images/icon/tide_icon.svg');
                }

                &.buoy-icon, &.water-icon {
                    background-size: 26px 26px;

                    @include breakpoint(smedium down) {
                        background-size: 16px 16px;
                    }

                    &.buoy-icon {
                        background-image: url('/images/icon/buoy.svg');
                    }

                    &.water-icon {
                        background-image: url('/images/icon/waves.svg');
                    }
                }
            }

            .current-data-info {
                display:inline-block;
                vertical-align: top;

                @include breakpoint(large) {

                    @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) { //IE10+
                        width:200px;
                    }

                    @supports (-ms-ime-align:auto) { //all edge browsers version 12+, even though need is for < version 18
                        width:200px;
                    }
                }

                @include breakpoint(medium only) {
                    display:block;
                }

                .current-data-label {
                    font-size: 12px;
                    color: #C3C8C8;
                    line-height: 18px;

                    @include breakpoint(smedium down) {
                        line-height: 30px;
                        display:inline-block;
                        min-width:104px;
                    }
                }

                .current-data-desc {
                    font-size: 15px;
                    color: #787C7C;
                    line-height: 24px;

                    @include breakpoint(large) {
                        .wetsuit {
                            //font-size:14px;
                        }
                    }

                    @include breakpoint(smedium down) {
                        display:inline-block;
                    }
                }
            }
        }

        #fcst-toggles {
            text-align:center;
            padding-bottom:12px;

            .add-fav-error, .add-fav-success {
                font-weight: normal;
                color: #fff;
                padding: 0 25px;
                border-radius: 4px;
                position:relative;
                line-height:38px;
                margin:0 auto;
                cursor:pointer;

                &.add-fav-error {
                    background: $warning-300;
                }
                &.add-fav-success {
                    background: $success-500;
                }

                .close-msg {
                    position:absolute;
                    top: 2px;
                    right: 5px;
                    color:#fff;
                    font-size: 20px;
                    line-height: 20px;
                }
            }
        }
    }

    #fcst-timeline {
        background: rgba($blue-00,0.5);
        padding-bottom:31px;

        @include breakpoint(smedium down) {
            padding-bottom:28px;
        }

        .fcst-timeline-header {
            width:100%;
            margin:0 auto;
            padding:15px 0 0;
            position:relative;
            left:0;
            max-width:$max-width-content-large;

            @include breakpoint(xxlarge only) {
                max-width:$max-width-content-xxlarge
            }

            @include breakpoint(xlarge only) {
                max-width:$max-width-content-xlarge;
            }

            @include breakpoint(medium down) {
                left:0;
                padding: 15px 12px 0;
            }

            h1 {
                font-size: 24px;
                font-weight:bold;
                color: $blue-700;
                line-height: 32px;
                text-align: left;

                @include breakpoint(medium only) {
                    margin:0;
                }

                @include breakpoint(smedium down) {
                    font-size:22px;
                }
            }

            .timeline-toggle {
                padding-top:14px;
                text-align:right;

                @include breakpoint(medium down) {
                    padding-top:6px;
                }

                @include breakpoint(smedium down) {
                    padding-top:8px;
                    text-align:center;
                }

                @include breakpoint(medium down) {
                    .fcst-day-toggle {
                        margin-top: -8px;
                        text-align: right;
                        position: relative;
                        top: 3px;
                    }
                }

                .fcst-range-8-16 {
                    display:none;

                    .fcst-range-btn {
                        display:inline-block;
                        padding:5px 10px;
                        cursor: pointer;
                    }

                    @include breakpoint(medium down) {
                        display:inline-block;
                        color: $blue-500;
                        font-size: 13px;

                        .arrow-left, .arrow-right {
                            height: 15px;
                        }
                    }
                }
            }
        }

        .fcst-legend {
            text-align:center;
        }
    }

    #fcst-info {
        background-color: rgba($blue-00, 0.5);
        color: $green-text;
        font-size: 18px;
        font-weight: 300;
        padding-bottom:48px;
        @include breakpoint(medium down) {
            padding-bottom:28px;
        }

        #fcst-info-container {

            @include maxContentWidth;

            @include breakpoint(medium down) {
                padding: 0 12px;
            }

            @include breakpoint(medium only) {
                font-size:16px;
            }

            @include breakpoint(smedium down) {
                font-size:14px;
            }
        }
    }

    #fcst-loading-msg {
        position: fixed;
        text-align: center;
        color: #fff;
        font-size:16px;
        font-weight:bold;
        z-index:5;
        width:100%;
        left:0;
        bottom: 1rem;
        margin-bottom: env(safe-area-inset-bottom);

        @include breakpoint(medium down) {
            bottom:4.5rem;
        }

        .fcst-loading-msg-txt {
            display:inline-block;
            padding: 12px 50px;
            background: $blue-800;
            border-radius: 8px;
        }
    }

    .simple-mode {

        #fcst-current {
            padding:12px 12px 0 !important;
        }

        .fcst-summary-header {
            padding-top:12px !important;
        }
    }

    .tooltip {
        display: block !important;
        z-index: 10000;
        visibility:hidden;
        border-radius:4px !important;

        .tooltip-inner {
            background: black;
            color: white;
            //padding:0 3px;
        }

        .tooltip-arrow {
            width: 5px;
            height: 5px;
            border-style: solid;
            position: absolute;
            margin: 5px;
            border-color: black;
            z-index: 1;
        }

        &[x-placement^="top"] {
            margin-bottom: 5px;

            .tooltip-arrow {
                border-width: 5px 5px 0 5px;
                border-left-color: transparent !important;
                border-right-color: transparent !important;
                border-bottom-color: transparent !important;
                bottom: -5px;
                left: calc(50% - 5px);
                margin-top: 0;
                margin-bottom: 0;
            }
        }

        &[x-placement^="bottom"] {
            margin-top: 5px;

            .tooltip-arrow {
                border-width: 0 5px 5px 5px;
                border-left-color: transparent !important;
                border-right-color: transparent !important;
                border-top-color: transparent !important;
                top: -5px;
                left: calc(50% - 5px);
                margin-top: 0;
                margin-bottom: 0;
            }
        }

        &[x-placement^="right"] {
            margin-left: 5px;

            .tooltip-arrow {
                border-width: 5px 5px 5px 0;
                border-left-color: transparent !important;
                border-top-color: transparent !important;
                border-bottom-color: transparent !important;
                left: -5px;
                top: calc(50% - 5px);
                margin-left: 0;
                margin-right: 0;
            }
        }

        &[x-placement^="left"] {
            margin-right: 5px;

            .tooltip-arrow {
                border-width: 5px 0 5px 5px;
                border-top-color: transparent !important;
                border-right-color: transparent !important;
                border-bottom-color: transparent !important;
                right: -5px;
                top: calc(50% - 5px);
                margin-left: 0;
                margin-right: 0;
            }
        }

        &.popover {
            $color: #f9f9f9;

            .popover-inner {
                background: $color;
                color: black;
                padding: 24px;
                border-radius: 5px;
                box-shadow: 0 5px 30px rgba(black, .1);
            }

            .popover-arrow {
                border-color: $color;
            }
        }

        &[aria-hidden='true'] {
            visibility: hidden;
            opacity: 0;
            transition: opacity .15s, visibility .15s;
        }

        &[aria-hidden='false'] {
            visibility: visible;
            opacity: 1;
            transition: opacity .15s;
        }
    }

</style>
