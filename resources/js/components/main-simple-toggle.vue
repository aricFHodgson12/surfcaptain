<template>
    <div id="simple-toggle">
        <button type="button" id="simple-toggle-btn" @click="$emit('togglesimplemode')">
            <img v-if="simpleText" :src="simpleIcon">
            {{ simpleText }}
        </button>
    </div>
</template>

<script>
    export default {
        props: ['simpleMode'],
        name: "main-simple-toggle.vue",
        computed: {
            simpleText() {
                if (this.simpleMode)
                    return 'Show Timeline';
                else
                    return 'Hide Timeline';
            },
            simpleIcon() {
                if (this.simpleMode)
                    return '/images/icon/expand_more.svg';
                else
                    return '/images/icon/expand_less.svg';
            }
        }
    }
</script>

<style lang="scss">

    @import '../../sass/sc-settings.scss';

    #simple-toggle-btn {

        font-size:14px;
        color: $blue-500;
        font-weight:500;
        outline:0;
        cursor:pointer;

        @include breakpoint(small only) {
            line-height:20px;
        }

        @include breakpoint(smedium down) {
            margin:12px 0;
        }

        @include breakpoint(medium) {
            margin: 18px 0;
        }

        img {
            width:16px;
            margin-right:3px;
        }
    }
</style>
