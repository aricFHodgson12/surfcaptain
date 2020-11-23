<template>

</template>

<script>

    import NProgress from "nprogress";

    //import Vue from 'vue'
    //import Routes from './routes'
    //import NProgress from 'nprogress'
    //import VueRouter from 'vue-router'
    import moment from 'moment-timezone'
    import HelperMixin from "./mixins/HelperMixin"
    import RequestMixin from "./mixins/RequestMixin"

    //require('bootstrap');

    window.Popper = require('popper.js').default;

    //Vue.prototype.moment = moment;
    window.Vue.prototype.moment = moment;

    // Set the default timezone
    if (window.Studio)
        moment.tz.setDefault(window.Studio.timezone);

    //Vue.mixin(HelperMixin);
    window.Vue.mixin(HelperMixin);
    //Vue.mixin(RequestMixin);
    window.Vue.mixin(RequestMixin);

    // Prevent the production tip on Vue startup
    //Vue.config.productionTip = false;
    window.Vue.config.productionTip = false;

    /*
    //Vue.use(VueRouter);
    window.Vue.use(VueRouter);

    const studioPath = (window.Studio) ? window.Studio.path : '/blog';

    const router = new VueRouter({
        routes: Routes,
        mode: 'history',
        base: studioPath
    });

    NProgress.configure({
        showSpinner: false,
        easing: 'ease',
        speed: 300,
    });

    router.beforeEach((to, from, next) => {
        NProgress.start();
        next()
    });
     */

    const studioAvatar = (window.Studio) ? window.Studio.avatar : '';

    export default {
        name: "studio.vue",
        data() {
            return {
                avatar: studioAvatar
            }
        }
    }
</script>

<style lang="scss">

    @import '../../sass/sc-settings';

    $green: #03a87c;
    @import '../../sass/studio/nprogress';

    $box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);

    #studio {
        background:$blue-00;
        padding:3rem 0 !important;

        @include breakpoint (medium only) {
            padding: 2rem 20px !important;
        }

        @include breakpoint (smedium down) {
            padding:1.5rem 12px !important;
        }
    }

    .card {
        transition: box-shadow .2s ease,-webkit-transform .3s cubic-bezier(.34,2,.6,1) !important;
        transition: transform .3s cubic-bezier(.34,2,.6,1),box-shadow .2s ease !important;
        transition: transform .3s cubic-bezier(.34,2,.6,1),box-shadow .2s ease,-webkit-transform .3s cubic-bezier(.34,2,.6,1) !important;
        box-shadow: 0 2px 4px 0 rgba(0,0,0,.1) !important;

        .card-section {
            color:$dark-letter;
        }

        .card-divider {
            color: $gray-500;
        }

        .card-title {
            color: $dark-letter;
        }
    }

    .card:hover {
        -webkit-transform: translateY(-5px) !important;
        transform: translateY(-5px) !important;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,.12),0 2px 4px 0 rgba(0,0,0,.08) !important;
    }

    .text-decoration-none {
        text-decoration: none!important;
    }

    .text-dark {
        color: #2d3748!important;
    }

    #topic-posts, #tag-posts {
        padding-top:3rem!important;
        @include breakpoint(smedium down) {
            padding-top:2rem !important;
        }

        @include blogContentWidth();

        h1 {
            @include mainBlogHeading;

            @include breakpoint(smedium down) {
                font-size:2rem !important;
            }
        }

        #topic-posts-wrapper, #tag-posts-wrapper {
            margin-top: 1rem!important;
        }
    }

    #blog-home-main {
        @include blogContentWidth;

        padding-top:3rem;

        @include breakpoint(smedium down) {
            padding-top:2rem;
        }

        h1 {
            @include mainBlogHeading;

            @include breakpoint(smedium down) {
                font-size:2rem;
            }
        }
    }

    .blog-header {
        color:$blue-500;
        font-weight:900;
        font-size: 36px;
        margin:2rem 0 1rem;
        line-height:58px;
        background:$blue-00;

        @include breakpoint(medium down) {
            font-size:24px;
        }

        @include breakpoint(smedium down) {
            margin-top:24px;
            line-height:36px;
        }
    }

    .blog-header-secondary {
        font-weight: 300;
        color: #12524E;
        font-size: 24px;
        line-height: 28px;
        margin-bottom: 16px;
    }

</style>
