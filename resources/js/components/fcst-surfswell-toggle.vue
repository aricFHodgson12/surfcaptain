<template>
    <div class="timeline-toggle-surfswell">
        <span class="toggle-surf-label" :class="{active: surfActive}" @click="toggleSurf">Surf</span>
        <div class="surfswell-toggle">
            <div class="surf-toggle" :class="{active: surfActive}" @click="toggleSurf"></div>
            <div class="swell-toggle" :class="{active: swellActive}" @click="toggleSwell"></div>
        </div>
        <span class="toggle-swell-label" :class="{active: swellActive}" @click="toggleSwell">Swell</span>
    </div>
</template>

<script>
    export default {
        name: "fcst-surfswell-toggle.vue",
        data: function() {
            return {
                surfActive: true,
                swellActive: false,
            }
        },
        methods: {
            toggleSurf: function() {
               this.surfActive = true;
               this.swellActive = false;
               this.$emit('changesurfswell','surf');
                //window.SC.setCookie('sc_surfswell', 'surf');
            },
            toggleSwell: function() {
                this.surfActive = false;
                this.swellActive = true;
                this.$emit('changesurfswell','swell');
                //window.SC.setCookie('sc_surfswell', 'swell');
            },
        },
        mounted() {

        }
    }
</script>

<style scoped lang="scss">
    @import '../../sass/sc-settings.scss';

    .timeline-toggle-surfswell {
        font-size: 12px;
        color: #787C7C;
        text-align:left;

        @include breakpoint(xxlarge only) {
            font-size:14px;
        }

        @include breakpoint(xlarge only) {
            font-size:13px;
        }

        .toggle-surf-label, .toggle-swell-label {
            cursor:pointer;
            &.active {
                font-weight: bold;
                color: $blue-700;
            }
        }

        .surfswell-toggle {
            display:inline-block;
            position: relative;
            top: 3px;
            background: $blue-700;
            border-radius: 10px;
            width:36px;
            height:16px;
            margin: 0 2px;
            cursor:pointer;

            .surf-toggle, .swell-toggle {
                display:inline-block;
                width:12px;
                height:12px;
                margin: 2px;
                border-radius: 50%;

                &.active {
                    background: #fff;
                }
            }
        }
    }
</style>
