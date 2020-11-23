<template>
    <li class="fcst-summary-day" :class="{'day-active': isDayActive}" @click="$emit('changeactiveday',dayNum)">
        <div class="grid-x">
            <div class="small-1 large-12 cell">
                <!--<div class="summary-day-text">{{ day }}</div>-->
                <div class="summary-day-abbrev">{{ dayText}}</div>
                <div class="summary-day-wx">
                    <div class="summary-day-wx-image">
                        <img :src="wxiconImage" alt="Weather Icon">
                    </div>
                </div>
            </div>
            <div class="small-10 large-12 cell summary-day-cond">
                <div class="grid-x">
                    <div class="small-6 large-12 cell">
                        <div class="summary-day-am" :class="amcond"><span class="summary-ampm">AM</span> {{ amsurf }}</div>
                    </div>
                    <div class="small-6 large-12 cell">
                        <div class="summary-day-pm" :class="pmcond"><span class="summary-ampm">PM</span> {{ pmsurf }}</div>
                    </div>
                </div>
            </div>
            <div class="small-1 large-12 cell">
                <div class="summary-day-expand"><i></i></div>
            </div>
        </div>
        <div class="summary-day-bottom-white"></div>
    </li>
</template>

<script>
    export default {
        name: "fcst-day.vue",
        props: ['day','date','amsurf','pmsurf','amcond','pmcond','dayNum','dayActive','wxicon'],
        data: function() {
            return {
            }
        },
        computed: {
            dayText() {
                return (this.day.substring(0, 3) + ' ' + this.date);
                //return (this.day + ' ' + this.date);
            },
            isDayActive() {
                return (parseInt(this.dayActive) === parseInt(this.dayNum));
            },
            wxiconImage() {
                if (this.wxicon !== '')
                    return '/images/wx-icons/wi-'+this.wxicon+'.svg';
                else
                    return '/images/wx-icons/none.svg';
            }
        },
        created() {
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    li.fcst-summary-day {
        display:inline-block;
        background:#fff;
        box-shadow: 0 0 12px 0 rgba(49,50,57,0.20);
        border-radius: 2px;
        position:relative;
        padding: 0 12px;
        cursor:pointer;
        z-index:1;
        width:113px;
        text-align:center;

        @include breakpoint(xxlarge only) {
            width: 163px;
        }

        @include breakpoint(xlarge only) {
            width:135px;
        }

        @include breakpoint(medium only) {
            padding:12px 12px 0;
            margin-bottom:16px;
        }

        @include breakpoint(medium down) {
            display:block;
            width:100%;
        }

        @include breakpoint(smedium down) {
            margin-bottom:12px;
            height:68px;
            padding: 6px 7px;
        }

        &.day-active {
            z-index:4;
        }

        &:not(:last-child) {
            margin-right:8px;

            @include breakpoint(xlarge up) {
                margin-right:11px;
            }

            @include breakpoint(medium down) {
                margin-right:0;
            }

        }

        &.summary-day-day16 {

            @include breakpoint(medium down) {
                height: 86px;
            }

            .day16-text {
                color:$blue-800;
                font-size: 16px;
                margin: 26px 0 3px;
                line-height: 23px;

                @include breakpoint(medium down) {
                    margin:0;
                }

                @include breakpoint(xxlarge) {
                    margin:26px 0;
                }
            }

            .summary-day-cond {
                font-size:14px;

                @include breakpoint(medium down) {
                    margin-bottom:10px;
                }
            }
        }

        .summary-day-text {
            display:none;

            @include breakpoint(medium only) {
                display:block;
                color: $gray-500;
                font-size: 12px;
                line-height: 24px;
            }
        }

        .summary-day-abbrev {
            position:absolute;
            top:6px;
            left:15px;
            font-size: 12px;
            color: $gray-500;
            line-height: 24px;

            @include breakpoint(medium only) {
                position:relative;
                top:-6px;
                left:0;
            }

            @include breakpoint(smedium down) {
                top:0px;
                left:7px;
            }
        }

        .summary-day-wx {
            margin:24px 0 8px;
            text-align: center;

            @include breakpoint(medium only) {
                margin:0;
                position:relative;
                top:-8px;
            }
            @include breakpoint(smedium down) {
                margin:18px 0 0;
            }

            img {
                height:40px;

                @include breakpoint(medium down) {
                    max-height: 30px;
                    max-width: 100%;
                }
            }
        }

        .summary-day-cond {
            font-size: 16px;
            line-height: 26px;
            text-align:center;

            @include breakpoint(smedium down) {
                > div {
                    height: 56px;
                    -webkit-box-align: center;
                    align-items: center; //vertically align
                }
            }

            .summary-day-am, .summary-day-pm {
                margin: 6px auto;
                color: #0C2455;
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
                    background: $choppy-cond;
                }

                &.none {
                    background: $none-cond;
                }
            }
        }

        .summary-day-expand {
            padding:8px 0;

            @include breakpoint(medium down) {
                margin-top: 7px;
                text-align: right;
                padding-right: 4px;
            }

            @include breakpoint(smedium down) {
                margin-top:14px;
            }

            @include breakpoint(small only) {
                padding-right: 0;
            }

            i {
                display:inline-block;
                width: 12px;
                height: 8px;
                background-image: url('/images/icon/expand.svg');
                background-position: center;
                background-repeat: no-repeat;
                background-size: 12px 8px;

                @include breakpoint(medium down) {
                    width: 8px;
                    height: 12px;
                    background-image: url('/images/icon/chevron_right.svg');
                    background-position: center;
                    background-repeat: no-repeat;
                    background-size: 8px 12px;
                }
            }
        }

        .summary-day-bottom-white {
            display:none;
            position: absolute;
            top: 178px;
            left: 0;
            height: 12px;
            width: 100%;
            background: #fff;
            z-index: 3;

            @include breakpoint(medium down) {
                display:none;
            }
        }
    }
</style>
