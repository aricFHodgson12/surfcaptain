<template>
    <div class="timeline-wrapper">
        <div class="geo-location-error" v-html="geoLocationErrorMsg" v-if="geoLocationErrorMsg"></div>
        <div class="timeline-graph-wrapper" v-else-if="showTimeline">
            <div class="timeline-graph">
                <h1 v-html="timelineTitle"></h1>
                <div class="timeline-graph-legend">
                    <div class="graph-legend-mask"></div>
                    <ul class="timeline-graph-legend-surf">
                        <li class="graph-legend-dohead">D-OH</li>
                        <li class="graph-legend-ohead">O-Head</li>
                        <li class="graph-legend-head">Head</li>
                        <li class="graph-legend-shoulder">Shoulder</li>
                        <li class="graph-legend-chest">Chest</li>
                        <li class="graph-legend-waist">Waist</li>
                        <li class="graph-legend-knee">Knee</li>
                    </ul>
                </div>
                <div class="graph-image-wrapper">
                    <div class="timeline-graph-image">
                        <div class="timeline-graph-svg">
                            <div class="timeline-graph-base" v-html="baseGraph"></div>
                            <div v-html="graphSvg"></div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="fcst-legend">
                <li class="fcst-legend-clean">Clean</li>
                <li class="fcst-legend-fair">Fair</li>
                <li class="fcst-legend-choppy">Choppy</li>
            </ul>
            <a :href="'/forecast/'+slug" @click="gotoForecast"><div class="home-local-link">View Full Forecast</div></a>
        </div>
    </div>
</template>

<script>
    export default {
        name: "main-timeline-graph.vue",
        props: ['reloadTimeline'],
        data() {
            return {
                graphSvg: '',
                baseGraph: '',
                locName: '',
                slug: '',
                lat: false,
                lon: false,
                geoLocationErrorMsg: false,
                showTimeline: false
            };
        },
        created() {
            if (window.SC.getCookie('beach'))
                this.loadTimeline();
            else
                this.checkGeoLocation();
        },
        methods: {
            gotoForecast() {
              this.$root.beach = this.slug;
              this.$root.gotoForecast();
            },
            checkGeoLocation() {
                const self = this;
                this.$root.getGeoLocation(function(position) {
                    self.lat = position.coords.latitude;
                    self.lon = position.coords.longitude;
                    self.loadTimeline();
                }, this.geoLocationError, false);
            },
            loadTimeline() {

                let url = '/api/timeline/';
                if (window.SC.getCookie('beach'))
                    url = url + window.SC.getCookie('beach');
                else
                    url = url + 'nearby/lat/'+this.lat+'/lon/'+this.lon;

                let self = this;
                axios({
                    method: 'get',
                    url: url,
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
                })
                    .then(function (response) {
                        if (response.data.errorMsg) {
                            self.geoLocationErrorMsg = response.errorMsg;
                        } else {
                            self.baseGraph = response.data.baseGraph;
                            self.graphSvg = response.data.graphSvg;
                            self.locName = response.data.locName;
                            self.slug = response.data.slug;
                            self.showTimeline = true;
                        }
                    })
                    .catch(function (error) {
                        self.geoLocationErrorMsg = 'There was an error fetching nearby location: '+error;
                    });

                localStorage.setItem('sc_geolocate','true');
                this.$root.useGeoLocate = true;
            },
            geoLocationError(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        localStorage.removeItem('sc_geolocate');
                        this.$root.useGeoLocate = false;
                        break;
                    case error.POSITION_UNAVAILABLE:
                        this.geoLocationErrorMsg = "Location information is unavailable.";
                        break;
                    case error.TIMEOUT:
                        this.geoLocationErrorMsg = "The request to get user location timed out.";
                        break;
                    case error.UNKNOWN_ERROR:
                        this.geoLocationErrorMsg = "An unknown error occurred.";
                        break;
                }
            },
        },
        computed: {
            timelineTitle() {
                if (this.locName)
                    return '3 day surf timeline for <span class="no-wrap">'+this.locName+'</span>';
                else
                    return '';
            }
        },
        watch: {
            reloadTimeline(newVal,oldVal) {
                if (newVal === true) {
                    this.loadTimeline();
                }
            }
        }
    }
</script>

<style lang="scss">
    @import '../../sass/_sc-settings.scss';
    $graphBorderColor: rgba($gray-300, 0.5);

    .geo-location-error {
        text-align:center;
        color:$warning-500;
    }

    .timeline-graph {
        margin: 15px auto 0;
        width:982px;
        position: relative;
        user-select:none;
        -webkit-user-select:none;
        -ms-user-select:none;

        @include breakpoint(medium down) {
            width:100%;
        }

        .timeline-graph-legend {
            display:inline-block;
            position: absolute;
            left:0;
            color:$blue-700;
            z-index:3;
            pointer-events:none;
            text-align: right;
            @include breakpoint(medium down) {
                text-align:left;
            }

            .graph-legend-mask {
                display:none;

                @include breakpoint(medium down) {
                    display:block;
                    position: absolute;
                    left: 0;
                    top: 24px;
                    background: #fff;
                    opacity: 0.5;
                    width: 50px;
                    height: 130px;
                }

                @include breakpoint(smedium down) {
                    height:110px;
                    width:46px;
                }
            }

            .timeline-graph-legend-surf {
                position:absolute;
                top: 27px;
                left: -27px;
                line-height: 17px;
                font-size:10px;
                width:50px;

                @include breakpoint(medium down) {
                    left:5px;
                }

                @include breakpoint(smedium down) {
                    line-height:14px;
                }
            }

            ul {
                list-style:none;
                margin:0;
            }
        }

        .graph-image-wrapper {
            overflow-x:hidden;
        }

        .timeline-graph-image {
            width: 954px;
            margin-left: 28px;
            position:relative;
            left: 0;
            transition: 1s;

            @include breakpoint(medium only) {
                width:100%;
                margin-left:0;
            }

            @include breakpoint(smedium down) {
                width:100%;
                margin-left:0;
            }


            .timeline-graph-svg {
                position: relative;
                font-size:0;
                height:170px;

                @include breakpoint(medium only) {
                    height:160px;
                }

                @include breakpoint(smedium down) {
                    height: 134px;
                }

                line {
                    stroke-width:1;
                }

                path.choppy,polyline.choppy {
                    fill: $choppy-cond;
                }

                path.clean,polyline.clean {
                    fill: $clean-cond;
                }

                path.fair,polyline.fair {
                    fill: $fair-cond;
                }

                path.none,polyline.none {
                    fill: $none-cond;
                }
            }
        }

        .timeline-graph-base {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index:1;
        }

        svg {
            position: absolute;
            z-index:2;
            width:100%;
            top:24px;
            left:0;
            height:129px;

            @include breakpoint(smedium down) {
                height:110px;
            }
        }

        .graph-base {
            width: 100%;
            height: 100%;
            overflow: hidden;

            .day-header {
                background:#fff;
                border: 1px solid $graphBorderColor;
                height: 24px;
            }

            .graph-bg {
                background:#fff;
                position:relative;
                border-left:1px solid $graphBorderColor;
                border-right:1px solid $graphBorderColor;
                border-bottom:1px solid $graphBorderColor;
                height:130px;

                @include breakpoint(smedium down) {
                    height:110px;
                }

                .night {
                    position:absolute;
                    height:100%;
                    background: $gray-100;

                    .sunrise-icon, .sunset-icon {
                        display:none;

                        @include breakpoint(large up) {
                            display:block;
                            width: 11px;
                            height: 7px;
                            position: absolute;
                            bottom: -10px;

                            &.sunrise-icon {
                                background-image: url(/images/icon/sunrise-icon.svg);
                                right: -6.5px;
                            }

                            &.sunset-icon {
                                background-image: url(/images/icon/sunset-icon.svg);
                            }
                        }
                    }
                }

                .midnight {
                    position: absolute;
                    top:-24px;
                    width: 1px;
                    background: $graphBorderColor;
                    height: 159px;

                    @include breakpoint(smedium down) {
                        height:143px;
                    }
                }

                .afternoon {
                    height: 135px;
                    width: 1px;
                    position: absolute;
                    background:$graphBorderColor;

                    @include breakpoint(smedium down) {
                        height:119px;
                    }
                }

                .graph-day-date {
                    top: -21px;
                    position: absolute;
                    transform: translateX(-50%);
                    color: $gray-500;
                    font-size: 12px;
                    &:last-child {
                        min-width:42px;
                    }
                }
            }
        }
    }
</style>
