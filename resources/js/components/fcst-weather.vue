<template>
    <ul class="fcst-current-data grid-x">
        <li class="current-data-temp cell small-6 large-auto">
            <div class="grid-x current-data-container">
                <div class="cell shrink">
                    <div class="current-data-icon wx-icon" :class="skyIcon" :title="data.sky"></div>
                </div>
                <div class="cell auto">
                    <div class="current-data-info">
                        <div class="current-data-label">WEATHER</div>
                        <div class="current-data-desc">{{ atmp }}&deg; {{ data.wind_dir }} @ {{ windSpd }}</div>
                    </div>
                </div>
            </div>
        </li>
        <li class="current-data-tide cell small-6 large-auto">
            <div class="grid-x current-data-container">
                <div class="cell shrink">
                    <div class="current-data-icon tide-icon"></div>
                </div>
                <div class="cell auto">
                    <div class="current-data-info">
                        <div class="current-data-label">LOW / HIGH TIDE</div>
                        <div class="current-data-desc">{{ data.low_tide }} / {{ data.high_tide }}</div>
                    </div>
                </div>
            </div>
        </li>
        <li class="current-data-wind cell small-6 large-auto">
            <div class="grid-x current-data-container">
                <div class="cell shrink">
                    <div class="current-data-icon buoy-icon"></div>
                </div>
                <div class="cell auto">
                    <div class="current-data-info">
                        <div class="current-data-label">BUOY {{ data.buoy }}</div>
                        <div class="current-data-desc">{{ wvht }} @ {{ data.wvper }}sec</div>
                    </div>
                </div>
            </div>
        </li>
        <li class="current-data-wtmp cell small-6 large-auto">
            <div class="grid-x current-data-container">
                <div class="cell shrink">
                    <div class="current-data-icon water-icon"></div>
                </div>
                <div class="cell auto">
                    <div class="current-data-info">
                        <div class="current-data-label">WATER TEMP</div>
                        <div class="current-data-desc">{{ sst }}&deg; <span class="wetsuit">{{ data.wetsuit }}</span></div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</template>

<script>
    export default {
        name: "fcst-weather.vue",
        props: ['weather','units'],
        data() {
            return {
                data: false
            }
        },
        computed: {
            skyIcon() {
                return 'wi-' + this.data.sky_icon;
            },
            atmp() {
                return window.SC.convertTemp(this.data.atmp,this.units.temp);
            },
            windSpd() {
                return window.SC.convertWindSpeed(this.data.wind_spd,this.units.wind) + this.units.wind;
            },
            wvht() {
                return window.SC.convertHeight(this.data.wvht,this.units.wvht)+' '+this.units.wvht;
            },
            sst() {
                return window.SC.convertTemp(this.data.sst,this.units.temp);
            }
        },
        created: function() {
            this.data = JSON.parse(this.weather);
        }
    }
</script>

<style scoped>

</style>
