<template>
    <div>
        <div class="fcst-timeline">
            <div class="timeline-graph">
                <div class="timeline-graph-legend">
                    <div class="graph-legend-mask"></div>
                    <ul v-if="surfswell === 'surf'" class="timeline-graph-legend-surf">
                        <li class="graph-legend-dohead" :class="{active: timeline['wvhtBodySize'][graphHour] === 'dohead'}">D-OH</li>
                        <li class="graph-legend-ohead" :class="{active: timeline['wvhtBodySize'][graphHour] === 'ohead'}">O-Head</li>
                        <li class="graph-legend-head" :class="{active: timeline['wvhtBodySize'][graphHour] === 'head'}">Head</li>
                        <li class="graph-legend-shoulder" :class="{active: timeline['wvhtBodySize'][graphHour] === 'shoulder'}">Shoulder</li>
                        <li class="graph-legend-chest" :class="{active: timeline['wvhtBodySize'][graphHour] === 'chest'}">Chest</li>
                        <li class="graph-legend-waist" :class="{active: timeline['wvhtBodySize'][graphHour] === 'waist'}">Waist</li>
                        <li class="graph-legend-knee" :class="{active: timeline['wvhtBodySize'][graphHour] === 'knee'}">Knee</li>
                    </ul>
                    <ul v-else v-html="swellLevels" class="timeline-graph-legend-swell" :class="'count-'+this.swellLevelCount"></ul>
                </div>
                <div class="graph-image-wrapper">
                    <div class="timeline-graph-image" :class="graphImageClasses">
                        <div class="timeline-graph-svg" @mousemove="mouseMove($event)" @touchstart="touchStart($event)" @touchend="touchEnd()" @touchmove="mouseMove($event)" >
                            <div class="timeline-graph-base" v-html="timeline['baseGraph']" :class="{'days16': fcstdays === 16}"></div>
                            <div v-html="graphSvg"></div>
                        </div>
                        <div class="timeline-graph-marker" :style="{ left: markerLeft + 'px' }"></div>
                        <div class="timeline-graph-time" :style="{left: markerLeft + 'px' }">{{ timeline['time'][graphHour] }}</div>
                    </div>
                </div>
            </div>
            <div class="fcst-legend-row">
                <transition name="fade">
                    <ul key="1" v-if="surfswell === 'surf'" class="fcst-legend">
                        <li class="fcst-legend-clean">Clean</li>
                        <li class="fcst-legend-fair">Fair</li>
                        <li class="fcst-legend-poor">Choppy</li>
                    </ul>
                    <ul key="2" v-else class="fcst-legend">
                        <li class="fcst-legend-swell1">Swell 1</li>
                        <li class="fcst-legend-swell2">Swell 2</li>
                        <li class="fcst-legend-swell3">Swell 3</li>
                        <li class="fcst-legend-swell4">Swell 4</li>
                        <li class="fcst-legend-swell5">Swell 5</li>
                        <li class="fcst-legend-swell6">Swell 6</li>
                    </ul>
                </transition>
            </div>
        </div>
        <transition name="graph-data-slide">
            <div v-show="viewTimelineData" id="timeline-graph-data" class="timeline-graph-data">
                <div class="timeline-graph-data-contents">
                    <div class="timeline-graph-pointer" :style="{left: dataPointerLeft + 'px' }">
                        <svg viewBox="0 0 48 29" width="48" height="29"><polygon points="0,29 48,29 24,0" :class="pointerClass" /></svg>
                    </div>
                    <transition key="1" name="fade" mode="out-in">
                        <div v-if="surfswell === 'surf'"  class="contents-surf">
                            <div class="timeline-graph-txt" :class="timeline['condText'][graphHour]" v-html="surfText"></div>
                            <div class="timeline-graph-details grid-x">
                                <div class="cell medium-8 large-6 timeline-details-swell-wind">
                                    <div class="grid-x">
                                        <div class="cell small-6 medium-5 large-4">
                                            <div class="details-compass-container">
                                                <img class="timeline-details-map" :src="timeline.compassMap" alt="Compass Map"/>
                                                <div class="timeline-details-compass">
                                                    <img class="compass-img" src="/images/icon/fcst-compass.svg" alt="Forecast Compass">
                                                    <img class="wind-arrow compass-wind-arrow" :class="['dir-'+timeline['windDir'][graphHour]]" :src="windArrowImg" alt="Compass Wind Arrow">
                                                    <img class="primary-arrow compass-primary-arrow" :class="'dir-'+timeline['swell1Dir'][graphHour]" src="/images/icon/primary-swell-arrow.svg" alt="Compass Primary Swell Arrow">
                                                    <img class="secondary-arrow compass-secondary-arrow" :class="'dir-'+timeline['swell2Dir'][graphHour]" src="/images/icon/secondary-swell-arrow.svg" alt="Compass Secondary Swell Arrow">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="cell small-6 medium-7 large-8 timeline-details-wind-swell">
                                            <div class="details-wind">
                                                <div class="details-img">
                                                    <img class="wind-arrow" :class="'dir-'+timeline['windDir'][graphHour]" :src="windArrowImg" alt="Wind Arrow">
                                                </div>
                                                <span class="details-label">WIND</span>
                                                <span class="details-txt">{{ timeline['wind'][graphHour] }}</span>
                                            </div>
                                            <div class="details-primary">
                                                <div class="details-img">
                                                    <img class="primary-arrow" :class="'dir-'+timeline['swell1Dir'][graphHour]" src="/images/icon/primary-swell-arrow.svg" alt="Primary Swell Arrow">
                                                </div>
                                                <span class="details-label">PRIMARY</span>
                                                <span class="details-txt">{{ timeline['swell1'][graphHour] }}</span>
                                            </div>
                                            <div class="details-secondary">
                                                <div class="details-img">
                                                    <img class="secondary-arrow" :class="'dir-'+timeline['swell2Dir'][graphHour]" src="/images/icon/secondary-swell-arrow.svg" alt="Secondary Swell Arrow">
                                                </div>
                                                <span class="details-label">SECONDARY</span>
                                                <span class="details-txt">{{ timeline['swell2'][graphHour] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="cell medium-4 large-6 timeline-details-tide">
                                    <div class="grid-x">
                                        <div class="cell large-4">
                                            <div class="details-tide-label">TIDE LEVEL</div>
                                            <div class="details-tide-txt">
                                                {{ timeline['tideLevelDesc'][graphHour]}}<span class="hide-for-medium">,</span>
                                                <span class="details-tide-ht">{{ timeline['tideLevel'][graphHour] }}</span>
                                            </div>
                                        </div>
                                        <div class="cell large-8 tide-svg-container">
                                            <div class="details-tide-svg">
                                                <svg class="tide-svg-image" :class="{'days3': fcstdays === 3}" viewBox="0 0 954 70" preserveAspectRatio="none" v-html="timeline['tideSvg']" :style="'left:'+tideSvgLeft+'%'"></svg>
                                                <div class="tide-svg-marker"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div key="2" v-else class="contents-swell">
                            <div class="graph-data-swells">
                                <h1>SWELLS</h1>
                                <ul class="data-swells-vals">
                                    <li class="data-swell1 fcst-legend-swell1" v-if="timeline['swell']['swell1'][graphHour] !== ''" v-html="timeline['swell']['swell1'][graphHour]"></li>
                                    <li class="data-swell1 fcst-legend-swell2" v-if="timeline['swell']['swell2'][graphHour] !== ''" v-html="timeline['swell']['swell2'][graphHour]"></li>
                                    <li class="data-swell1 fcst-legend-swell3" v-if="timeline['swell']['swell3'][graphHour] !== ''" v-html="timeline['swell']['swell3'][graphHour]"></li>
                                    <li class="data-swell4 fcst-legend-swell4" v-if="timeline['swell']['swell4'][graphHour] !== ''" v-html="timeline['swell']['swell4'][graphHour]"></li>
                                    <li class="data-swell5 fcst-legend-swell5" v-if="timeline['swell']['swell5'][graphHour] !== ''" v-html="timeline['swell']['swell5'][graphHour]"></li>
                                    <li class="data-swell6 fcst-legend-swell6" v-if="timeline['swell']['swell6'][graphHour] !== ''" v-html="timeline['swell']['swell6'][graphHour]"></li>
                                </ul>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </transition>
        <div class="toggle-timeline-data">
            <button type="button" @click="toggleTimelineData">
                <img :src="toggleTimelineIcon" alt=""/>
                <span class="toggle-timeline-data-txt"><span v-html="toggleTimelineAction"></span> Timeline Data</span>
            </button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "fcst-timeline.vue",
        props: ['timeline','nowhour','surfswell','fcstdays','dayrange'],
        data() {
            return {
                graphHour:0,
                nHours: false,
                days: [],
                graphPageX: 0,
                graphWidth:0,
                dataWidth:0,
                pointerWidth:0,
                swellLevels:0,
                swellLevelCount:0,
                tideSvgWidth:0,
                graphMarginLeft:0,
                viewTimelineData: true,
                timelineDraggable: true
            }
        },
        created() {
            this.setupTimeline();
            const timelineDataCookie = localStorage.getItem('sc_timeline_data');
            if (timelineDataCookie !== null)
                this.viewTimelineData = (timelineDataCookie === 'true');

        },
        mounted() {
            this.calcDimensions();
            const self = this;
            window.addEventListener('resize', function() {
                if (window.innerWidth !== window.SC.width) {
                    setTimeout(self.calcDimensions, 1000);
                    window.SC.width = window.innerWidth;
                }
            });
        },
        methods: {
            setupTimeline() {
                this.nHours = this.timeline.time.length;
                this.graphHour = this.nowhour;

                let swLevels = '';
                let swLevelCount = 0;

                let self = this;
                Object.keys(this.timeline.swellLevels).reverse().map(function(key, index) {
                    swLevels = swLevels + '<li>'+self.timeline.swellLevels[key]+'</li>';
                    swLevelCount = index+1;
                });
                this.swellLevels = swLevels;
                this.swellLevelCount = swLevelCount;
            },
            calcDimensions() {
                let timelineGraphSvg = document.getElementsByClassName('timeline-graph-svg');
                this.graphPageX = this.elementOffset(timelineGraphSvg[0]).left;
                this.graphWidth = timelineGraphSvg[0].offsetWidth;

                let timelineData = document.getElementsByClassName('timeline-graph-data-contents');
                this.dataWidth = timelineData[0].offsetWidth;

                let timelinePointer = document.getElementsByClassName('timeline-graph-pointer');
                this.pointerWidth = timelinePointer[0].offsetWidth;

                let tideSvg = document.getElementsByClassName('details-tide-svg');
                this.tideSvgWidth = tideSvg[0].offsetWidth;
            },
            touchStart(event) {
                console.log('touch start');
                this.timelineDraggable = true;
                this.mouseMove(event);
            },
            touchEnd() {
                //there was a bug in safari, where mouseMove would fire the touchStart position after mouseMove was done.
                //setting timelineDraggable allows us to stop tracking after this point
                this.timelineDraggable = false;
            },
            mouseMove(event) {

                let pageX = null;
                if (event.type === 'mousemove')
                    pageX = event.pageX;
                else if (event.type === 'touchstart' || event.type === 'touchmove')
                    pageX = event.touches[0].pageX;

                if (this.timelineDraggable && pageX !== null) {
                    let graphX = (pageX - this.graphPageX);
                    if (this.dayrange === 16 && !this.isLargeScreen())
                        graphX = graphX + (this.graphWidth / 2);
                    else if (this.dayrange === 12)
                        graphX = graphX + (this.graphWidth / (27 / 8.5));
                    else if (this.dayrange === 10)
                        graphX = graphX + (this.graphWidth / (27 / 17));

                    let newHour = Math.round(graphX / this.graphWidth * (this.nHours - 1));

                    if (newHour > (this.nHours - 1))
                        newHour = this.nHours - 1;
                    this.graphHour = newHour;
                }
            },
            elementOffset(el) {
                let rect = el.getBoundingClientRect(),
                    scrollLeft = window.pageXOffset || document.documentElement.scrollLeft,
                    scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                return { top: rect.top + scrollTop, left: rect.left + scrollLeft }
            },
            isLargeScreen: function() {
                return document.documentElement.clientWidth > 1023;
            },
            toggleTimelineData() {
                this.viewTimelineData = ! this.viewTimelineData;

                if (this.viewTimelineData) {
                    let self = this;
                    setTimeout(function() {
                        let tideSvg = document.getElementsByClassName('details-tide-svg');
                        self.tideSvgWidth = tideSvg[0].offsetWidth;
                    },100);
                }

                localStorage.setItem('sc_timeline_data',this.viewTimelineData);

                gtag('event', 'toggle_timeline_data', {
                    'event_category' : 'forecast',
                    'event_label' : (this.viewTimelineData) ? 'show' : 'hide'
                });
            }
        },
        computed: {
            graphSvg() {
               if (this.surfswell === 'surf')
                   return this.timeline['surfSvg'];
               else
                   return this.timeline['swellSvg'];
            },
            markerLeft() {
                return Math.round(this.graphHour / (this.nHours - 1) * this.graphWidth);
            },
            dataPointerLeft() {
                let pointerLeft;

                if (this.isLargeScreen()) {

                    let graphWidth = this.graphWidth;

                    if (this.fcstdays === 8)
                        graphWidth = this.graphWidth / 2;

                    pointerLeft = Math.round(this.markerLeft + (this.dataWidth - graphWidth) - (this.pointerWidth / 2));

                } else {

                    if (this.fcstdays === 8 || this.fcstdays === 16) {
                        if (this.dayrange === 16)
                            pointerLeft = Math.round(this.markerLeft - (this.graphWidth / 2) - (((this.graphWidth / 2) - this.dataWidth) / 2) - (this.pointerWidth / 2));
                        else if (this.dayrange === 8)
                            pointerLeft = Math.round(this.markerLeft - (((this.graphWidth / 2) - this.dataWidth) / 2) - (this.pointerWidth / 2));
                        else if (this.dayrange === 6)
                            pointerLeft = Math.round( this.markerLeft - (this.pointerWidth / 2));
                        else if (this.dayrange === 12)
                            pointerLeft = Math.round( this.markerLeft - (this.graphWidth / (27/8.5) ) - (this.pointerWidth / 2));
                        else if (this.dayrange === 10)
                            pointerLeft = Math.round( this.markerLeft - (this.graphWidth / (17/10) ) - (this.pointerWidth / 2));

                    } else
                        pointerLeft = Math.round(this.markerLeft - ((this.graphWidth - this.dataWidth) / 2) - (this.pointerWidth / 2));
                }

                if ((pointerLeft + this.pointerWidth) > this.dataWidth)
                    return this.dataWidth - this.pointerWidth;
                else if (pointerLeft < 0)
                    return 0;
                else
                    return pointerLeft;
            },
            tideSvgLeft() {
                return this.graphHour / (this.nHours - 1) * 100 * (this.tideSvgImageWidth / this.tideSvgWidth) * -1 + 50; //+50 to go halfway
            },
            surfText() {
               return 'Conditions are '+this.timeline['wvhtText'][this.graphHour]+' and '+this.timeline['condText'][this.graphHour]+
                   ' at <span class="no-wrap">'+this.timeline['time'][this.graphHour]+' on '+this.timeline['day'][this.graphHour]+' '+this.timeline['date'][this.graphHour]+'</span>';
            },
            pointerClass() {
                if (this.surfswell === 'surf')
                    return this.timeline['condText'][this.graphHour];
                else
                    return 'swell';
            },
            windArrowImg() {
                //return '/images/icon/wind-arrow.svg';
                return '/images/icon/wind-arrow-'+this.timeline['condText'][this.graphHour]+'.svg';
            },
            windArrowSize() {

            },
            graphImageClasses() {
                let classes = [];
                if (this.fcstdays === 8)
                    classes.push('days8');
                else if (this.fcstdays === 16)
                    classes.push('days16');

                if (!this.isLargeScreen()) {
                    if (this.dayrange === 16)
                        classes.push('dayrange16');
                    else if (this.dayrange === 12)
                        classes.push('dayrange12');
                    else if (this.dayrange === 10)
                        classes.push('dayrange10');
                }

                return classes;
            },
            tideSvgImageWidth() {
                if (this.fcstdays === 3)
                    return 954;
                else
                    return 954 * 4;
            },
            toggleTimelineIcon() {
                return (this.viewTimelineData) ? '/images/icon/expand_less.svg' : '/images/icon/expand_more.svg';
            },
            toggleTimelineAction() {
                return (this.viewTimelineData) ? 'Hide' : 'Show';
            }
        },
        watch: {
            surfswell() {
                if (this.surfswell === 'surf') {
                    let timelinePointer = document.getElementsByClassName('timeline-graph-pointer');
                    this.pointerWidth = timelinePointer[0].offsetWidth;
                }
            },
            fcstdays() {
                let timelineGraphSvg = document.getElementsByClassName('timeline-graph-svg');

                self = this;
                setTimeout( function() { //have to wait a bit, because width has a 1 second transition
                    self.graphWidth = timelineGraphSvg[0].offsetWidth;
                },1200);
            },
            timeline() {
                this.setupTimeline();
            }
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    $graphBorderColor: rgba($gray-300, 0.5);
    $graphHighlightColor: #4476Db;
    $swell1Color: #D0021C;
    $swell2Color: #396EB8;
    $swell3Color: #60A712;
    $swell4Color: #FFC0CC;
    $swell5Color: #00FFFF;
    $swell6Color: #FFA602;

    .graph-data-slide-enter-active {
        @include transition(all 0.5s ease-in);
    }

    .graph-data-slide-leave-active {
        @include transition(all 0.5s cubic-bezier(0, 1, 0.5, 1));
    }

    .graph-data-slide-enter-to,.graph-data-slide-leave {
        max-height: 300px;
        overflow: hidden;
    }

    .graph-data-slide-enter,.graph-data-slide-leave-to {
        overflow: hidden;
        max-height: 0;
    }

    .fcst-timeline {

        .timeline-graph {
            margin: 15px auto 0;
            width:982px;
            position: relative;
            user-select:none;
            -webkit-user-select:none;
            -ms-user-select:none;

            @include breakpoint(xxlarge only) {
                width:1400px;
            }

            @include breakpoint(xlarge only) {
                width:1200px;
            }

            @include breakpoint(medium down) {
                width:100%;
            }

            .timeline-graph-legend {
                display:inline-block;
                position: absolute;
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

                    @include breakpoint(xxlarge only) {
                        top:38px;
                        line-height:25px;
                        font-size:13px;
                    }

                    @include breakpoint(xlarge only) {
                        top:33px;
                        line-height:21px;
                        font-size:11px;
                    }

                    @include breakpoint(medium down) {
                        left:5px;
                    }

                    @include breakpoint(smedium down) {
                        line-height:14px;
                    }
                }

                .timeline-graph-legend-swell {
                    position:absolute;
                    font-size: 12px;
                    left:2px;

                    @include breakpoint(medium down) {
                        left:5px;
                    }

                    &.count-4 {
                        top: 34px;
                        line-height: 34px;
                        @include breakpoint(smedium down) {
                            top:33px;
                            line-height:26px
                        }
                        @include breakpoint(medium only) {
                            top:39px;
                            line-height:25px
                        }
                        @include breakpoint(large only) {
                            top:39px;
                            line-height:25px
                        }
                        @include breakpoint(xlarge only) {
                            top:34px;
                            line-height:34px
                        }
                        @include breakpoint(xxlarge only) {
                            top:60px;
                            line-height:36px
                        }
                    }
                    &.count-3 {
                        @include breakpoint(smedium down) {
                            top:40px;
                            line-height:27px
                        }
                        @include breakpoint(medium only) {
                            top: 35px;
                            line-height:34px;
                        }
                        @include breakpoint(large only) {
                            top: 35px;
                            line-height:34px;
                        }
                        @include breakpoint(xlarge only) {
                            top:42px;
                            line-height:42px;
                        }
                        @include breakpoint(xxlarge) {
                            top:58px;
                            line-height:46px;
                        }
                    }
                    &.count-2 {
                        @include breakpoint(smedium down) {
                            top:35px;
                            line-height:38px
                        }
                        @include breakpoint(medium only) {
                            top:41px;
                            line-height:44px
                        }
                        @include breakpoint(large only) {
                            top:41px;
                            line-height:44px
                        }
                        @include breakpoint(xlarge only) {
                            top:50px;
                            line-height:56px
                        }
                        @include breakpoint(xxlarge only) {
                            top:50px;
                            line-height:56px
                        }
                        @include breakpoint(xxlarge only) {
                            top:63px;
                            line-height:64px
                        }
                    }
                }

                ul {
                    list-style:none;
                    margin:0;
                }

                li.active {
                    color: $graphHighlightColor;
                    font-weight:bold;
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

                @include breakpoint(large up) {
                    &.days8.dayrange16 {
                        left: -50%;
                    }

                    &.days8 {
                        width: 1908px;
                    }
                }

                @include breakpoint(xlarge only) {
                    width:1172px;

                    &.days8 {
                        width: 2344px;
                    }
                }

                @include breakpoint(xxlarge only) {
                    width:1372px;

                    &.days8 {
                        width: 2744px;
                    }
                }

                @include breakpoint(medium only) {
                    width:100%;
                    margin-left:0;

                    &.days8, &.days16 {
                        width: 200%;
                    }

                    &.dayrange16 {
                        left: -100%;
                    }

                }

                @include breakpoint(smedium down) {
                    width:100%;
                    margin-left:0;

                    &.days8, &.days16 {
                        width: 270%;
                    }

                    &.dayrange12 {
                        left: -85%;
                    }

                    &.dayrange10, &.dayrange10 {
                        left: -170%;
                    }

                }


                .timeline-graph-svg {
                    position: relative;
                    font-size:0;
                    height:163px;

                    @include breakpoint(xxlarge only) {
                        height:236px;
                    }

                    @include breakpoint(xlarge only) {
                        height:200px;
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

                    polyline.swell1 {
                        stroke: $swell1Color;
                        stroke-width: 1.5;
                        fill: none;
                    }

                    polyline.swell2 {
                        stroke: $swell2Color;
                        stroke-width: 1.5;
                        fill: none;
                    }

                    polyline.swell3 {
                        stroke: $swell3Color;
                        stroke-width: 1.5;
                        fill: none;
                    }

                    polyline.swell4 {
                        stroke: $swell4Color;
                        stroke-width: 1.5;
                        fill: none;
                    }

                    polyline.swell5 {
                        stroke: $swell5Color;
                        stroke-width: 1.5;
                        fill: none;
                    }

                    polyline.swell6 {
                        stroke: $swell6Color;
                        stroke-width: 1.5;
                        fill: none;
                    }
                }

                .timeline-graph-marker {
                    width:1px;
                    background-color:$graphHighlightColor;
                    position:absolute;
                    z-index:3;
                    left:0;
                    top:24px;
                    height:135px;
                    @include breakpoint(xxlarge only) {
                        top:30px;
                        height:202px;
                    }
                    @include breakpoint(xlarge only) {
                        height:172px;
                    }
                    @include breakpoint(medium down) {
                        margin-left:0;
                    }
                    @include breakpoint(smedium down) {
                        height:115px;
                    }
                }

                .timeline-graph-time {
                    position: relative;
                    z-index:3;
                    transform: translateX(-50%);
                    display: inline-block;
                    font-weight: bold;
                    color: $graphHighlightColor;
                    font-size: 10px;

                    @include breakpoint(xxlarge only) {
                        font-size:12px;
                    }
                    @include breakpoint(xlarge only) {
                        font-size:11px;
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
                height:129px;

                @include breakpoint(xxlarge only) {
                    top:30px;
                    height:196px;
                }
                @include breakpoint(xlarge only) {
                    height:166px;
                }

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
                    @include breakpoint(xxlarge only) {
                        height:30px;
                    }
                }

                .graph-bg {
                    background:#fff;
                    position:relative;
                    border-left:1px solid $graphBorderColor;
                    border-right:1px solid $graphBorderColor;
                    border-bottom:1px solid $graphBorderColor;
                    height:130px;
                    @include breakpoint(xxlarge only) {
                        height:197px;
                    }
                    @include breakpoint(xlarge only) {
                        height:167px;
                    }
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
                        @include breakpoint(xxlarge only) {
                            height:226px;
                        }
                        @include breakpoint(xlarge only) {
                            height:196px;
                        }
                        @include breakpoint(smedium down) {
                            height:143px;
                        }
                    }

                    .afternoon {
                        height: 135px;
                        width: 1px;
                        position: absolute;
                        background:$graphBorderColor;
                        @include breakpoint(xxlarge only) {
                            height:202px;
                        }
                        @include breakpoint(xlarge only) {
                            height:172px;
                        }
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
                            min-width:48px;
                        }

                        @include breakpoint(xxlarge only) {
                            top:-25px;
                            font-size:14px;
                        }
                        @include breakpoint(xlarge only) {
                            font-size:13px;
                        }
                    }
                }
            }
        }

        .fcst-legend-row {
            max-width: $max-width-content-large;
            margin:0 auto;
            text-align:center;

            @include breakpoint(xlarge only) {
                max-width:$max-width-content-xlarge;
            }

            @include breakpoint(xxlarge only) {
                max-width:$max-width-content-xxlarge;
            }

            @include breakpoint(medium down) {
                padding:0 12px;
            }
        }

        .fcst-legend {
            text-align:center;
            margin:0;
            color:$gray-500;
            font-size: 12px;
            @include breakpoint(xxlarge only) {
                font-size: 14px;
            }
            @include breakpoint(xlarge only) {
                font-size: 13px;
            }

            li {
                display:inline-block;
                padding-left:20px;
                background-size:12px 12px;
                background-repeat:no-repeat;
                background-position:left center;

                &:not(:last-child) {
                    margin-right:15px;
                }

                @include breakpoint(small only) {
                    margin-right:5px;
                }

                &.fcst-legend-clean {
                    background-image:url('/images/icon/circle_clean.svg');
                }
                &.fcst-legend-fair {
                    background-image:url('/images/icon/circle_fair.svg');
                }
                &.fcst-legend-poor {
                    background-image:url('/images/icon/circle_poor.svg');
                }
            }
        }
    }

    .fcst-legend-swell1 {
        background-image:url('/images/icon/circle_swell1.svg');
    }
    .fcst-legend-swell2 {
        background-image:url('/images/icon/circle_swell2.svg');
    }
    .fcst-legend-swell3 {
        background-image:url('/images/icon/circle_swell3.svg');
    }
    .fcst-legend-swell4 {
        background-image:url('/images/icon/circle_swell4.svg');
    }
    .fcst-legend-swell5 {
        background-image:url('/images/icon/circle_swell5.svg');
    }
    .fcst-legend-swell6 {
        background-image:url('/images/icon/circle_swell6.svg');
    }

    .toggle-timeline-data  {
        text-align: center;
        padding-top:14px;

        img {
            width:24px;
        }

        button {
            cursor:pointer;
            outline:0;
            font-weight:500;
            color:$blue-500;
        }
    }

    .timeline-graph-data {
        padding:0 12px;
        margin-top: 14px;

        @include breakpoint(smedium down) {
            padding:0;
        }

        .timeline-graph-data-contents {
            box-shadow: 0 0 12px 0 rgba(49, 50, 57, 0.20);
            border-radius: 2px;
            background: #f9faf9;
            width: 100%;
            max-width: 982px;
            margin: 0 auto;
            position: relative;

            @include breakpoint(xxlarge only) {
                max-width: 1400px;
            }
            @include breakpoint(xlarge only) {
                max-width: 1200px;
            }
        }

        .timeline-graph-pointer {
            position:absolute;
            top:-29px;
            left:80px;
            filter: drop-shadow( 0px -3px 2px rgba(49,50, 57, .1));
            -webkit-filter: drop-shadow( 0px -3px 2px rgba(49,50, 57, .1));
            @include breakpoint(smedium down) {
                top:-26px;
                svg {
                    width:41px;
                }
            }

            @media all and (-ms-high-contrast:none) { //ie11 fix
                top:-28px;
            }

            .clean {
                fill: $clean-cond;
            }
            .fair {
                fill: $fair-cond;
            }
            .choppy {
                fill: $choppy-cond;
            }
            .none {
                fill: $none-cond;
            }
            .swell {
                fill: #fff;
            }
        }

        .timeline-graph-txt {
            background: $gray-100;
            font-weight: 900;
            color: #002D45;
            text-align: center;
            padding:8px 8px 2px;
            font-size: 24px;
            line-height: 48px;

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


            @include breakpoint(smedium down) {
                font-size:18px;
                line-height:26px;
                min-height:62px;
            }
        }

        .timeline-graph-details {
            font-size:16px;
            line-height:26px;
            background:#F9FAF9;

            > div {

                @include breakpoint(smedium down) {
                    padding:14px 0;
                }
            }

            .timeline-details-wind-swell {
                padding-top:24px;
                @include breakpoint(smedium down) {
                    padding-top:0;
                }

                > div {
                    margin-bottom:8px;

                    @include breakpoint(smedium down) {
                        margin-bottom:4px;
                    }
                }
            }

            .timeline-details-tide {

                border-left:1px solid rgba($gray-300,0.5);
                padding:24px 12px 24px 24px;
                line-height:24px;
                color:$gray-500;

                @include breakpoint(medium only) {
                    padding:24px 0 24px 12px;
                }

                @include breakpoint(smedium down) {
                    text-align: center;
                    border-top:1px solid rgba($gray-300,0.5);
                    border-left:none;
                    padding:12px;
                }

                .details-tide-label {
                    font-size:12px;
                    color:$gray-300;

                    @include breakpoint(medium down) {
                        display:none;
                    }
                }

                .details-tide-ht {
                    display:block;
                    @include breakpoint(smedium down) {
                        display:inline-block;
                    }
                }

                .tide-svg-container {
                    @include breakpoint(medium only) {
                        padding-right:12px;
                    }

                    @include breakpoint(smedium down) {
                        padding-top:10px;
                    }
                }

                .details-tide-svg {
                    width:100%;
                    max-width:350px;
                    height:72px;
                    position:relative;
                    overflow:hidden;

                    @include breakpoint(medium down) {
                        height:50px;
                    }

                    @include breakpoint(smedium down) {
                        margin:0 auto;
                    }

                    svg {
                        width: 954px * 4;
                        height: 70px;
                        position:absolute;

                        @include breakpoint(medium down) {
                            height:50px;
                        }

                        path {
                            fill: url(#tide-grad);
                            stroke: $gray-350;
                            stroke-width: 0.5;

                        }

                        line {
                            stroke: $gray-900;
                            stroke-width: 1;
                        }

                        &.days3 {
                            width:954px;

                            path {
                                stroke-width:1;
                            }
                        }
                    }

                    .tide-svg-marker {
                        position: absolute;
                        height:100%;
                        width:2px;
                        background:$gray-900;
                        left:50%;
                    }
                }
            }

            .details-compass-container {
                position:relative;
                padding:4px;
                width:158px;
                margin:0 auto;
            }

            .timeline-details-map {
                width:150px;
                height:150px;
            }

            .timeline-details-compass {
                position:absolute;
                top:29px;
                left:29px;
                width:100px;
                margin:0 auto;

                @include breakpoint(smedium down) {
                    padding-bottom: 8px;
                }

                .compass-img {
                    width:100px;
                }

                .compass-wind-arrow {
                    position: absolute;
                    z-index:1;
                    height: 18px !important;
                    width: 14px !important;

                    &.dir-n {
                         top: -2px;
                         left: 45px;
                     }
                    &.dir-nne {
                         top: 3px;
                         left: 64px;
                     }
                    &.dir-ne {
                         top: 16px;
                         left: 80px;
                     }
                    &.dir-ene {
                         top: 26px;
                         left: 86px;
                     }
                    &.dir-e {
                         top: 43px;
                         left: 89px;
                     }
                    &.dir-ese {
                         top: 63px;
                         left: 85px;
                     }
                    &.dir-se {
                         top: 75px;
                         left: 77px;
                     }
                    &.dir-sse {
                         top: 85px;
                         left: 62px;
                     }
                    &.dir-s {
                         top: 88px;
                         left: 45px;
                     }
                    &.dir-ssw {
                         top: 85px;
                         left: 27px;
                     }
                    &.dir-sw {
                         top: 77px;
                         left: 14px;
                     }
                    &.dir-wsw {
                         top: 62px;
                         left: 4px;
                     }
                    &.dir-w {
                         top: 44px;
                         left: 0;
                     }
                    &.dir-wnw {
                         top: 23px;
                         left: 4px;
                     }
                    &.dir-nw {
                         top: 13px;
                         left: 10px;
                     }
                    &.dir-nnw {
                         top: 2px;
                         left: 27px;
                     }
                }
            }

            .compass-primary-arrow {
                position: absolute;
                height: 16px !important;

                &.dir-n {
                     top: -8px;
                     left: 41px;
                 }
                &.dir-nne {
                     top: -4px;
                     left: 59px;
                 }
                &.dir-ne {
                     top: 7px;
                     left: 78px;
                 }
                &.dir-ene {
                     top: 25px;
                     left: 87px;
                 }
                &.dir-e {
                     top: 44px;
                     left: 89px;
                 }
                &.dir-ese {
                     top: 63px;
                     left: 86px;
                 }
                &.dir-se {
                     top: 79px;
                     left: 73px;
                 }
                &.dir-sse {
                     top: 89px;
                     left: 59px;
                 }
                &.dir-s {
                     top: 90px;
                     left: 38px;
                 }
                &.dir-ssw {
                     top: 88px;
                     left: 20px;
                 }
                &.dir-sw {
                     top: 79px;
                     left: 6px;
                 }
                &.dir-wsw {
                     top: 64px;
                     left: -6px;
                 }
                &.dir-w {
                     top: 41px;
                     left: -12px;
                 }
                &.dir-wnw {
                     top: 23px;
                     left: -9px;
                 }
                &.dir-nw {
                     top: 6px;
                     left: 2px;
                 }
                &.dir-nnw {
                     top: -3px;
                     left: 21px;
                 }
            }

            .compass-secondary-arrow {
                position: absolute;
                height: 16px !important;

                &.dir- {
                    display:none;
                }
                &.dir-n {
                     top: -8px;
                     left: 42px;
                 }
                &.dir-nne {
                     top: -5px;
                     left: 63px;
                 }
                &.dir-ne {
                     top: 8px;
                     left: 79px;
                 }
                &.dir-ene {
                     top: 23px;
                     left: 89px;
                 }
                &.dir-e {
                     top: 42px;
                     left: 92px;
                 }
                &.dir-ese {
                     top: 62px;
                     left: 88px;
                 }
                &.dir-se {
                     top: 78px;
                     left: 78px;
                 }
                &.dir-sse {
                     top: 88px;
                     left: 63px;
                 }
                &.dir-s {
                     top: 92px;
                     left: 42px;
                 }
                &.dir-ssw {
                     top: 88px;
                     left: 23px;
                 }
                &.dir-sw {
                     top: 78px;
                     left: 8px;
                 }
                &.dir-wsw {
                     top: 60px;
                     left: -5px;
                 }
                &.dir-w {
                     top: 42px;
                     left: -9px;
                 }
                &.dir-wnw {
                     top: 22px;
                     left: -5px;
                 }
                &.dir-nw {
                     top: 6px;
                     left: 6px;
                 }
                &.dir-nnw {
                     top: -2px;
                     left: 17px;
                 }
            }

            .details-img {
                width:12px;
                display:inline-block;
            }

            .wind-arrow {
                width:11px;

                &.dir-n {
                 @include transform(rotate(35deg));
                 }
                &.dir-nne {
                 @include transform(rotate(57.5deg));
                 }
                &.dir-ne {
                 @include transform(rotate(80deg));
                 }
                &.dir-ene {
                 @include transform(rotate(102.5deg));
                 }
                &.dir-e {
                 @include transform(rotate(125deg));
                 }
                &.dir-ese {
                 @include transform(rotate(147.5deg));
                 }
                &.dir-se {
                 @include transform(rotate(170deg));
                 }
                &.dir-sse {
                 @include transform(rotate(192.5deg));
                 }
                &.dir-s {
                 @include transform(rotate(215deg));
                 }
                &.dir-ssw {
                 @include transform(rotate(237.5deg));
                 }
                &.dir-sw {
                 @include transform(rotate(260deg));
                 }
                &.dir-wsw {
                 @include transform(rotate(282.5deg));
                 }
                &.dir-w {
                 @include transform(rotate(305deg));
                 }
                &.dir-wnw {
                 @include transform(rotate(327.5deg));
                 }
                &.dir-nw {
                 @include transform(rotate(350deg));
                 }
                &.dir-nnw {
                 @include transform(rotate(372.5deg));
                 }
            }

            .primary-arrow {
                height:12px;

                &.dir-n {
                 @include transform(rotate(239deg));
                 }
                &.dir-nne {
                 @include transform(rotate(261.5deg));
                 }
                &.dir-ne {
                 @include transform(rotate(284deg));
                 }
                &.dir-ene {
                 @include transform(rotate(306.5deg));
                 }
                &.dir-e {
                 @include transform(rotate(329deg));
                 }
                &.dir-ese {
                 @include transform(rotate(351.5deg));
                 }
                &.dir-se {
                 @include transform(rotate(14deg));
                 }
                &.dir-sse {
                 @include transform(rotate(36.5deg));
                 }
                &.dir-s {
                 @include transform(rotate(59deg));
                 }
                &.dir-ssw {
                 @include transform(rotate(76.5deg));
                 }
                &.dir-sw {
                 @include transform(rotate(99deg));
                 }
                &.dir-wsw {
                 @include transform(rotate(121.5deg));
                 }
                &.dir-w {
                 @include transform(rotate(144deg));
                 }
                &.dir-wnw {
                 @include transform(rotate(166.5deg));
                 }
                &.dir-nw {
                 @include transform(rotate(189deg));
                 }
                &.dir-nnw {
                 @include transform(rotate(211.5deg));
                 }
            }

            .secondary-arrow {
                height:12px;

                &.dir-n {
                 @include transform(rotate(135deg));
                 }
                &.dir-nne {
                 @include transform(rotate(157.5deg));
                 }
                &.dir-ne {
                 @include transform(rotate(180deg));
                 }
                &.dir-ene {
                 @include transform(rotate(202.5deg));
                 }
                &.dir-e {
                 @include transform(rotate(225deg));
                 }
                &.dir-ese {
                 @include transform(rotate(247.5deg));
                 }
                &.dir-se {
                 @include transform(rotate(270deg));
                 }
                &.dir-sse {
                 @include transform(rotate(292.5deg));
                 }
                &.dir-s {
                 @include transform(rotate(315deg));
                 }
                &.dir-ssw {
                 @include transform(rotate(337.5deg));
                 }
                &.dir-sw {
                 @include transform(rotate(0deg));
                 }
                &.dir-wsw {
                 @include transform(rotate(22.5deg));
                 }
                &.dir-w {
                 @include transform(rotate(45deg));
                 }
                &.dir-wnw {
                 @include transform(rotate(67.5deg));
                 }
                &.dir-nw {
                 @include transform(rotate(90deg));
                 }
                &.dir-nnw {
                 @include transform(rotate(102.5deg));
                 }
            }

            .details-label {
                color:$gray-900;
                margin-left:5px;

                @include breakpoint(smedium down) {
                    font-size:14px;
                }
            }

            .details-txt {
                color:$gray-500;
                margin-left:7px;

                @include breakpoint(smedium down) {
                    display:block;
                    line-height:22px;
                }

                @include breakpoint(smedium only) {
                    font-size:14px;
                }

                @include breakpoint(small only) {
                    font-size:13px;
                }
            }
        }

        .contents-swell {
            padding: 14px 24px 22px;

            h1 {
                font-size:16px;
                line-height: 26px;
                margin:0 0 12px;
            }

            ul.data-swells-vals {
                margin:0;
                font-size:16px;
                color:$gray-500;
                line-height:30px;
                height:90px;
                column-fill:auto;
                -webkit-column-fill: auto;
                -moz-column-fill: auto;
                column-width: 200px;
                -webkit-column-width: 200px;
                -moz-column-width: 200px;

                li {
                    list-style:none;
                    padding-left: 20px;
                    background-size: 12px 12px;
                    background-repeat: no-repeat;
                    background-position: left center;
                }
            }
        }
    }
</style>
