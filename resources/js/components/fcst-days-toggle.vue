<template>
    <div class="timeline-toggle-days" :class="{'show': showToggle}">
        <span v-if="days === 3" class="toggle-day3-label" :class="{active: days === 3}" @click="toggleDay(3)">3 day</span>
        <span v-else class="toggle-day8-label" :class="{active: days === 8}" @click="toggleDay(8)">8 day</span>
        <div class="days-toggle">
            <div v-if="days === 3" class="day3-toggle" :class="{active: days === 3}" @click="toggleDay(3)"></div>
            <div v-else class="day8-toggle" :class="{active: days === 8}" @click="toggleDay(8)"></div>
            <div class="day16-toggle" :class="{active: days === 16}" @click="toggleDay(16)"></div>
        </div>
        <span class="toggle-day16-label" :class="{active: days === 16}" @click="toggleDay(16)">16 day</span>
    </div>
</template>

<script>
    export default {
        name: "fcst-surfswell-toggle.vue",
        props: ['fcstdays'],
        data: function() {
            return {
                premium: false,
                days: 3,
                showToggle:false
            }
        },
        methods: {
            toggleDay: function(days) {
                if (this.premium) {
                    this.$emit('changefcstdays', days);
                    localStorage.setItem('sc_fcst-days',days);
                } else {
                    this.$root.toggleProModal('pro');

                    gtag('event', 'pro_modal', {
                        'event_category' : 'engagement',
                        'event_label' : 'timmeline'
                    });
                }
            },
            displayToggle() {
                this.showToggle = this.days === 3 || this.$root.isLargeScreen();
            }
        },
        watch: {
            fcstdays() {
                this.days = this.fcstdays;
                if (this.days !== 3)
                    this.premium = true;
            }
        },
        created() {
            this.days = this.fcstdays;
            if (this.days !== 3)
                this.premium = true;
        },
        mounted() {
            this.displayToggle();
            window.addEventListener('resize',this.displayToggle);
        }
    }
</script>

<style scoped lang="scss">
    @import '../../sass/sc-settings.scss';

    .timeline-toggle-days {
        display:none; //hidden for small screens

        &.show {
            font-size: 12px;
            color: #787C7C;
            display: inline-block;

            @include breakpoint(xxlarge only) {
                font-size: 14px;
            }
            @include breakpoint(xlarge only) {
                font-size: 13px;
            }

            .toggle-day3-label, .toggle-day8-label, .toggle-day16-label {
                cursor: pointer;

                &.active {
                    font-weight: bold;
                    color: $blue-700;
                }
            }

            .days-toggle {
                display: inline-block;
                position: relative;
                top: 3px;
                background: $blue-700;
                border-radius: 10px;
                width: 36px;
                height: 16px;
                margin: 0 2px;
                cursor: pointer;

                .day3-toggle, .day8-toggle, .day16-toggle {
                    display: inline-block;
                    width: 12px;
                    height: 12px;
                    margin: 2px;
                    border-radius: 50%;

                    &.active {
                        background: #fff;
                    }
                }
            }
        }
    }
</style>
