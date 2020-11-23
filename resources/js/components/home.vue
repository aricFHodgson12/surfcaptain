<template>
    <div id="home">
        <section id="home-hero">
            <div class="home-hero-content">
                <h1>Surf Forecasts made Awesome</h1>
                <a href="#" @click="togglelocationmenu()"><div class="home-hero-link">Find Your Surf Forecast</div></a>
                <video playsinline muted autoplay="true" loop="true" name="media" preload="auto">
                    <source src="/video/1035869150-lowres-slow.mov" type="video/mp4">
                </video>
            </div>
        </section>
        <section id="home-local">
            <transition name="fade-in-out">
                <div class="home-local-access" v-if="!showTimeline" key="1">
                    <h1>Can we access your location in order to provide you with the best experience?</h1>
                    <p>We use your location to populate the nearest surf forecast timeline on the home page.</p>
                    <div class="home-local-accept" @click="acceptGeoLocation">Accept</div>
                </div>
                <div class="home-local-fcst" v-else key="2">
                    <sc-timeline-graph
                        :reload-timeline="reloadFcst"
                    ></sc-timeline-graph>
                </div>
            </transition>
        </section>
        <section id="home-latest">
            <div class="home-latest-content">
                <header>
                    <div class="grid-x">
                        <div class="cell small-12 medium-shrink small-order-2 medium-order-1">
                            <h1>The latest news from the Captain</h1>
                        </div>
                        <div class="home-latest-follow cell small-12 medium-auto small-order-1 medium-order-2">
                            Follow Us
                            <ul>
                                <li><a href="https://www.facebook.com/surfcaptain" target="_blank"><img src="/images/icon/fb_logo_blue.svg" alt="Facebook"></a></li>
                                <li><a href="https://www.instagram.com/surf_captain" target="_blank"><img src="/images/icon/ig_logo_blue.svg" alt="Instagram"></a></li>
                                <li><a href="https://twitter.com/surf_captain" target="_blank"><img src="/images/icon/tw_logo_blue.svg" alt="Twitter"></a></li>
                            </ul>
                        </div>
                    </div>
                </header>
                <div class="home-latest-blog">
                    <div class="latest-blog-container">
                        <div class="grid-x grid-margin-x small-up-4">
                            <sc-blog
                                v-for="(post,index) in posts"
                                :key="index"
                                :title="post.title"
                                :link="'/blog/'+post.user_id+'/'+post.slug"
                                :summary="post.summary"
                                :featured-image="post.featured_image"
                            ></sc-blog>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="home-about">
            <div class="home-mission">
                <img src="/images/logo/sc_logo_mark_blue.svg" alt="Surf Captain Logo Mark">

                <div class="home-about-label">Our Mission</div>
                <h1>Connecting People to Their Passion for Surfing</h1>
                <p>What inspires us at Surf Captain is connecting our users to their passion: SURFING.
                    Awesome surf forecasts make Surf Captain the direct link to knowing the next great day of surfing.
                    A great day on the water makes everything better, and we love being a part of that.</p>
                <router-link :to="{ name: 'about' }" class="home-mission-link">Learn More</router-link>        
            </div>
        </section>
    </div>
</template>

<script>
    import { EventBus } from './event-bus.js';

    export default {
        name: "home.vue",
        props: ['reloadFcst','blogPosts'],
        data() {
            return {
                posts:[],
                showTimeline: false
            }
        },
        created() {

            let self = this;
            axios({
                method: 'get',
                url: '/api/home/blogpost/',
                headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
            })
            .then(function (response) {
                self.posts = response.data;
                self.showTimeline = ( window.SC.getCookie('beach') || !!(localStorage.getItem('sc_geolocate')) );

            })
            .catch(function (error) {
                console.log(error);
            })

        },
        components: {
            'sc-timeline-graph': require('./main-timeline-graph').default
        },
        methods: {
            acceptGeoLocation() {
                console.log('accept geo location');
                localStorage.setItem('sc_geolocate','true');
                this.showTimeline = true;
            },
            togglelocationmenu() {
                EventBus.$emit('toggleLocationMenu');
            }
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    #home-hero {
        width:100%;
        color:#fff;
        text-align: center;
        position:relative;
        overflow:hidden;

        .home-hero-content {
            padding:138px 0;
            background: rgba(0, 45, 69, 0.5);

            @include breakpoint(medium only) {
                padding:94px 0;
            }

            @include breakpoint(smedium down) {
                padding:78px 0;
            }

            h1 {
                font-weight:900;
                font-size: 48px;
                line-height: 56px;
                //width: 610px;
                margin:0 auto;
                padding:0 15px;
                //text-shadow: 1px 1px 2px #000;
                font-family:$roboto-font;

                @include breakpoint(medium only) {
                    font-size:44px;
                    line-height:50px;
                    width:540px;
                }

                @include breakpoint(smedium down) {
                    font-size:32px;
                    line-height:40px;
                    width:100%;
                }
            }

            a {
                color: #FFFFFF;
            }

            .home-hero-link {
                border: 2px solid #FFFFFF;
                border-radius: 19px;
                font-weight:500;
                font-size: 14px;
                line-height: 38px;
                width:176px;
                margin:24px auto 0;
            }

            video, img {
                z-index:-1;
                width:100%;
                min-width:600px;
                overflow:hidden;
                position:absolute;
                left:0;
                top:0;

                //@include breakpoint(large) {
                //    transform: translateY(-20%);
                //}
            }
        }
    }

    #home-local {
        box-shadow: 0 0 12px 0 rgba(49,50,57,0.20);
        position: relative;

        .home-local-access, .home-local-fcst {
            width:100%;
            text-align: center;
            margin:0 auto;
            padding:12px;

            &.home-local-access {
                max-width:610px;
            }

            @include breakpoint(large) {
                padding:24px 0;
            }

            h1 {
                font-weight:300;
                color: $green-text;
                font-size: 20px;
                line-height: 28px;
                margin-bottom:11px;

                @include breakpoint(large) {
                    font-size: 24px;
                    line-height: 32px;
                }
            }

            p {
                font-size: 14px;
                color: #787C7C;
                line-height: 26px;
                margin:12px 0;

                @include breakpoint(large) {
                    margin:0;
                }
            }

            .home-local-accept {
                margin-top:14px;
                @include actionButton;
            }

            .home-local-link {
                margin-top:24px;
                @include actionButton;
            }
        }

        ul.fcst-legend {
            margin:16px 0 0 0;
            list-style:none;
            text-align:center;

            @include breakpoint(smedium down) {
                margin:12px 0 0 0;
            }

            li {
                display:inline-block;
                padding-left:20px;
                margin-right:15px;
                background-size:12px 12px;
                background-repeat:no-repeat;
                background-position:left center;

                @include breakpoint(small only) {
                    margin-right:5px;
                }

                &.fcst-legend-clean {
                    background-image:url('/images/icon/circle_clean.svg');
                }
                &.fcst-legend-fair {
                    background-image:url('/images/icon/circle_fair.svg');
                }
                &.fcst-legend-choppy {
                    background-image:url('/images/icon/circle_poor.svg');
                }
            }
        }
    }

    #home-latest {
        color: $blue-500;
        background-color: $blue-00;
        padding:24px 0;

        .home-latest-content {
            max-width:$max-width;
            margin:0 auto;
            padding:0 20px;

            @include breakpoint(smedium down) {
                padding:0 12px;
            }
        }

        h1 {
            font-weight:900;
            font-size: 36px;
            margin:0;
            line-height:58px;
            background:$blue-00;

            @include breakpoint(medium only) {
                font-size:24px;
            }

            @include breakpoint(smedium down) {
                font-size:28px;
                margin-top:24px;
                line-height:36px;
            }
        }

        .home-latest-follow {
            font-family:$roboto-condensed-font;
            font-size: 14px;
            line-height: 58px;
            text-align:right;
            color:$blue-300;

            @include breakpoint(smedium down) {
                text-align:center;
                line-height:inherit;
            }

            ul {
                list-style: none;
                margin: 0;
                display:inline-block;

                li {
                    margin-left: 8px;
                    display:inline-block;

                    img {
                        width:23px;
                    }
                }
            }
        }

        .home-latest-blog {
            padding-top:16px;
            @include breakpoint(medium down) {
                overflow-x:scroll;
            }

            @include breakpoint(smedium down) {
                padding-top:12px;
            }

            .latest-blog-container {
                width: $max-width - 40px;
            }

            .card {
                background: #fff;
                box-shadow: 0 0 12px 0 rgba(49,50,57,0.20);
                border-radius: 2px;
                cursor:pointer;

                .card-section {
                    padding:12px;

                    .featured-image {
                        height:209px;
                        object-fit:cover;
                        width: 100%;
                        background: $gray-100;
                    }
                }
            }

            .home-latest-blog-label {
                font-family: $roboto-condensed-font;
                font-size: 10px;
                color: #63C6C1;
                line-height: 18px;
                padding:12px 0 4px;
            }

            h2 {
                font-size: 16px;
                line-height: 24px;
                color: $dark-letter;
                min-height:48px; //ensures at leat 2 line heights
            }

            p {
                color: $dark-letter;
                font-size: 12px;
                line-height: 18px;
                height:72px;
                overflow-y:hidden;
            }

            .home-latest-blog-link {
                font-size: 12px;
                color: $blue-300;
                text-align: right;
                line-height: 11px;
                img {
                    margin-left:8px;
                    width:8px;
                }
            }
        }
    }

    #home-about {
        padding:48px 12px;
        max-width:610px;
        margin:0 auto;
        text-align:center;

        @include breakpoint(large) {
            padding: 48px 20px;
        }

        img {
            width: 200px;
            margin-bottom:28px;
        }

        .home-about-label {
            font-family: $roboto-condensed-font;
            font-weight:700;
            font-size: 14px;
            color: $blue-300;
            line-height: 28px;

            @include breakpoint(smedium down) {
                margin-top:24px;
            }
        }

        h1 {
            font-weight:900;
            font-size: 36px;
            color: $blue-500;
            line-height: 44px;
            margin-top:5px;

            @include breakpoint(medium down) {
                font-size: 28px;
                line-height: 36px;
            }

            @include breakpoint(smedium down) {
                max-width:100%;
            }
        }

        p {
            font-size: 16px;
            color: $dark-letter;
            line-height: 26px;
            margin:14px 0 22px;

            @include breakpoint(smedium down) {
                max-width:100%;
            }
        }

        .home-mission-link {
            @include actionButton();
        }
    }

</style>
