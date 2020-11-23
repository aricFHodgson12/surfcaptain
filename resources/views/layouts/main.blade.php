<?php
use Illuminate\Support\Facades\Route;
?>
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
@section('head')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-167105392-1"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'UA-167105392-1');
        </script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover<?=(in_array(Route::currentRouteName(),array('nearby'))) ? ' ,maximum-scale=1.0, user-scalable=no' : '' ?>">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="<?=(config('app.env') == 'prod')?'index,follow':'noindex,nofollow'?>" />
        <meta name="description" content="@yield('description','16 day local surf forecasts.')"/>
        <meta name="theme-color" content="#fff" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Surf Captain">

        <title>@yield('title','Surf Captain')</title>
        <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700|Roboto:300,400,500,700,900&display=swap" rel="stylesheet">
        <link href="{{mix('/css/app.css')}}" rel="stylesheet"/>
        <link href="{{mix('/css/vue-components.css')}}" rel="stylesheet"/>

        <link rel="icon" type="image/png" href="/images/favicon/FaviconA32.png"  sizes="32x32"/>
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA57.png" sizes="57x57">
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA76.png" sizes="76x76">
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA128.png" sizes="128x128">
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA192.png" sizes="192x192">
        <link rel="icon" type="image/png" href="/images/favicon/FaviconA228.png" sizes="228x228">

        <link rel="manifest" href="/webmanifest.json"crossorigin="use-credentials" />
        <link rel="apple-touch-icon" href="/images/manifest/icon-192-blue.png" />
        <link rel="apple-touch-icon" sizes="152x152" href="/images/manifest/icon-152-blue.png" />
        <link rel="apple-touch-icon" sizes="180x180" href="/images/manifest/icon-180-blue.png" />
        <link rel="apple-touch-icon" sizes="167x167" href="/images/manifest/icon-167-blue.png" />
        @php //iphone 5 @endphp
        <link href="/images/splash-screens/launch-640x1136.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //iphone 6 @endphp
        <link href="/images/splash-screens/launch-750x1334.png" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //iphone plus @endphp
        <link href="/images/splash-screens/launch-1863x3312.png" media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
        @php //iphone x @endphp
        <link href="/images/splash-screens/launch-1125x2436.png" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
        @php //iphone xr @endphp
        <link href="/images/splash-screens/launch-828x1792.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //iphone x max @endphp
        <link href="/images/splash-screens/launch-1242x2688.png" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" rel="apple-touch-startup-image" />
        @php //ipad @endphp
        <link href="/images/splash-screens/launch-1536x2048.png" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //ipad pro @endphp
        <link href="/images/splash-screens/launch-1668x2224.png" media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //ipad pro 3 @endphp
        <link href="/images/splash-screens/launch-1668x2388.png" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />
        @php //ipad pro 2 @endphp
        <link href="/images/splash-screens/launch-2048x2732.png" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)" rel="apple-touch-startup-image" />

        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '307958963974406');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=307958963974406&ev=PageView&noscript=1"/></noscript>
        <!-- End Facebook Pixel Code -->
    @show
</head>
<body>
<div id="app">
    <div id="main-body">
        <header id="header">
            <nav class="header-nav">
                <div class="nav-top-container">
                    <div id="nav-top"
                         class="nav-top grid-x <?=(in_array(Route::currentRouteName(),array('nearby'))) ? 'width100' : '' ?> <?=(in_array(Route::currentRouteName(),array('studio'))) ? 'max-width-xl' : '' ?> <?=(in_array(Route::currentRouteName(),array('home','about'))) ? 'max-width-lg' : '' ?>"
                         :class="{'sticky': fixedTopNav}"
                         :style="{'top': topNavHeight+'px'}">
                        <div class="nav-logo cell shrink medium-6 large-4 text-center medium-text-left">
                            <a href="/"><div class="nav-logo-img"></div></a>
                        </div>
                        <div class="nav-search cell auto medium-6 large-4">

                            <div class="nav-search-loc" @click="toggleLocationMenu" :class="{'notice': selectLocationNotice, 'active': locationMenuActive }">
                                <div class="nav-search-loc-txt">
                                    <span v-html="locationName"></span>
                                    <div class="nav-search-loc-img"></div>
                                </div>
                            </div>
                        </div>
                        <div class="nav-user cell large-4 show-for-large">
                            @if ($user)
                                <div class="nav-user-favorites" :class="{'active-menu': favMenuActive }" @click="toggleFavMenu">
                                    <img v-if="favMenuActive" src="/images/icon/favorites_white.svg" width="21" alt="favorites">
                                    <img v-else src="/images/icon/favorites.svg" width="21" alt="favorites">
                                </div>
                                <div class="nav-user-settings" :class="{'active-menu': profileMenuActive }" @click="toggleProfileMenu">
                                    <img v-if="profileMenuActive" src="/images/icon/settings-white-18dp.svg" width="28" alt="settings">
                                    <img v-else src="/images/icon/settings-blue-18dp.svg" width="28" alt="settings">
                                </div>
                            @else
                                <div class="nav-user-signin" :class="{'active-menu': showRegisterForm }">
                                    <div class="nav-user-signin-btn" @click="toggleRegisterForm">Sign In / Register</div>
                                </div>
                            @endif
                        </div>
                        @if ($user)
                            <aside class="header-fav" v-if="favMenuActive && isLargeScreen()" v-cloak>
                                <div class="header-fav-content">
                                    <h1>Favorites</h1>
                                    <div v-if="!faves" class="loading">Loading...</div>
                                    <div v-else-if="Array.isArray(faves) && faves.length === 0" class="loading">You do not have any Favorites Yet.</div>
                                    <sc-fav v-for="fave in faves"
                                            :key="fave.id"
                                            :name="fave.name"
                                            :amcond="fave.amcond"
                                            :pmcond="fave.pmcond"
                                            :amsurf="fave.amsurf"
                                            :pmsurf="fave.pmsurf"
                                            :link="fave.link"
                                            :beachid="fave.beachId"
                                    ></sc-fav>
                                </div>
                            </aside>
                            <keep-alive>
                                <sc-profile v-if="profileMenuActive && isLargeScreen()"
                                            email="{{ $user->email }}"
                                            is-subscribed="{{ $user->is_subscribed }}"
                                            v-cloak
                                            :is-mobile-nav="false"
                                ></sc-profile>
                            </keep-alive>
                        @else
                            <aside v-if="showRegisterForm && isLargeScreen()" class="header-register" v-cloak>
                                <sc-register-login
                                    :is-modal="false"
                                ></sc-register-login>
                            </aside>
                        @endif
                    </div>
                </div>
                <div id="nav-location-menu" class="nav-location-menu <?=(in_array(Route::currentRouteName(),array('studio'))) ? 'max-width-xl' : '' ?> <?=(in_array(Route::currentRouteName(),array('home','about'))) ? 'max-width-lg' : '' ?>" :class="{'active': locationMenuActive}" v-cloak>
                    <div class="nav-location-menu-container">
                        <div class="location-menu-header grid-x">
                            <div class="cell shrink">
                                <h2>Select Location</h2>
                            </div>
                            <div class="location-menu-geolocate cell auto" @click="getGeoLocation(gotoNearbyForecast,nearbyForecastError)">
                                <img src="/images/icon/my_location.svg" title="Nearby Location" alt="Use My location">
                            </div>
                        </div>
                        <div class="location-menu-error" v-if="locationMenuError" v-html="locationMenuError"></div>
                        @component('location-menu')
                        @endcomponent
                    </div>
                </div>
                <sc-alert></sc-alert>
                <div class="nav-links-container <?=(in_array(Route::currentRouteName(),array('home','studio','faq'))) ? Route::currentRouteName() : '' ?>">
                    <div class="nav-links show-for-large <?=(in_array(Route::currentRouteName(),array('nearby'))) ? 'width100' : '' ?>">
                        @if (! $user and $showUnits)
                            <sc-units-toggle variable="cookie"></sc-units-toggle>
                        @endif
                        <ul>
                            <li :class="{'active': pageActive === 'nearby'}">
                                <a href="/nearby">NEARBY</a>
                                <div class="nav-links-active" v-if="pageActive === 'nearby'" v-cloak></div>
                            </li>
                            <li @click="gotoForecast" :class="{'active': pageActive === 'forecast'}">
                                FORECAST
                                <div class="nav-links-active" v-if="pageActive === 'forecast'" v-cloak></div>
                            </li>
                            <li :class="{'active': pageActive === 'maps'}">
                                <a href="/maps">MAPS</a>
                                <div class="nav-links-active" v-if="pageActive === 'maps'" v-cloak></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <main id="main-content" :class="{'overlay':isOverlay}">
            @yield('main-content')
        </main>
        <footer class="footer">
            <div class="grid-x footer-container <?=(in_array(Route::currentRouteName(),array('studio'))) ? 'max-width-xl' : '' ?> <?=(in_array(Route::currentRouteName(),array('home','about'))) ? 'max-width-lg' : '' ?>">
                <div class="cell medium-6">
                    <div class="grid-x">
                        <div class="cell small-12 medium-shrink footer-logo-mark">
                            <img src="/images/logo/sc_logo_mark_white.svg" alt="Surf Captain Logo Mark">
                        </div>
                        <div class="cell small-12 medium-auto footer-about">
                            <p class="footer-about-tagline show-for-medium">Providing the best ocean and weather data to plan your upcoming surf sessions.</p>
                            <div class="footer-about-subscribe">
                                Subscribe to our newsletter for updates.
                                <div class="footer-subscribe-form">
                                    <a href="#" id="subscribe-form-btn" class="footer-subscribe-form-btn" @click="submitSubscribeForm($event)" @keydown.enter="submitSubscribeForm">
                                        <span v-if="!subscribeLoading">Subscribe</span>
                                        <span v-else class="subscribe-loader" v-cloak></span>
                                    </a>
                                    <input type="text" placeholder="Enter your email" v-model="subscribeEmail" @keydown.tab="subscribeButtonFocus($event)">
                                </div>
                                <transition name="fade">
                                    <div class="subscribe-form-msg" v-if="subscribeError || subscribeSuccess" :class="{'error': subscribeError, 'success': subscribeSuccess}" >
                                        <p class="contact-form-error-msg" v-html="subscribeMsg"></p>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-links cell medium-6">
                    <div class="grid-x">
                        <div class="cell small-6 medium-4 small-offset-0 medium-offset-4">
                            <ul>Resources
                                <li><router-link :to="{ name: 'blog' }">Blog</router-link></li>
                                <li><a href="#">Forecast</a></li>
                                <li><a href="#">Location</a></li>
                                <li><router-link :to="{ name: 'maps' }">Maps</router-link></li>
                            </ul>
                        </div>
                        <div class="cell small-6 medium-4">
                            <ul>About the Captain
                                <li><router-link :to="{ name: 'about' }">Our Mission</router-link></li>
                                <li><a href="/about#story">Our Story</a></li>
                                <li><a href="/about#contact">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom grid-x">
                    <div class="cell medium-4 medium-offset-4 footer-copyright">Copyright 2020 Surf Captain</div>
                    <div class="cell medium-4 footer-social">
                        <ul>
                            <li><a href="https://www.facebook.com/surfcaptain" target="_blank"><img src="/images/icon/fb_logo.svg" alt="Facebook"></a></li>
                            <li><a href="https://www.instagram.com/surf_captain" target="_blank"><img src="/images/icon/ig_logo.svg" alt="Instagram"></a></li>
                            <li><a href="https://twitter.com/surf_captain" target="_blank"><img src="/images/icon/tw_logo.svg" alt="Twitter"></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <div id="main-body-overlay" :class="{'active': (showExpandedMenu || (favMenuActive && !isLargeScreen()))}"></div>
    </div>
    <nav id="mobile-nav" class="hide-for-large">
        <div class="nav-mobile-bottom">
            <ul>
                <li @click="gotoForecast">
                    <img src="/images/icon/wave_icon.svg" alt="Surf Forecast"/>
                    <br/>Forecast
                </li>
                <li>
                    <a href="/maps">
                        <img src="/images/icon/map.svg" alt="Surf Maps"/>
                        <br/>Maps
                    </a>
                </li>
                <li>
                    <a href="/nearby">
                        <img src="/images/icon/location_on.svg" alt="Nearby Surf Locations"/>
                        <br/>Nearby
                    </a>
                </li>
                <li @click="toggleFavMenu">
                    <img src="/images/icon/bookmark_border.svg" alt="Favorites"/>
                    <br/>Favorites
                </li>
                <li id="toggle-expanded-menu" @click="toggleExpandedMenu">
                    <img src="/images/icon/menu.svg" alt="More"/>
                    <br/>More
                </li>
            </ul>
        </div>
        <transition name="slide-up-down">
            <div v-if="showExpandedMenu" class="nav-mobile-expanded" v-cloak>
                <div id="nav-mobile-expanded-wrapper">
                    <div class="mobile-expanded-close">
                        <button @click="toggleExpandedMenu" id="mobile-expanded-close-button"  class="close-button" aria-label="Close menu" type="button">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="mobile-expanded-logo">
                        <a href="/"><img src="/images/logo/sc_logo_horizontal_blue.svg" alt="Home Page"></a>
                    </div>
                    <div class="mobile-expanded-links">
                        <ul>
                            @if ($user)
                                <li @click="toggleProfileMenu">Settings</li>
                                <li @click="toggleSubscriptionMenu">Subscription</li>
                            @else
                                <li><sc-units-toggle variable="cookie" label="Units"></sc-units-toggle></li>
                            @endif
                            <li><a href="/blog">Blog</a></li>
                                <li><a href="/about">About</a></li>
                            <li v-if="standAlone === false" @click="toggleInstallApp">Install App</li>
                            @if ($user)
                                <li @click="signOut">Sign Out</li>
                            @endif
                        </ul>
                        @if (!$user)
                            <div class="mobile-expanded-signin">
                                <div class="mobile-expanded-signin-btn" @click="toggleRegisterForm">Sign In / Register</div>
                            </div>
                        @else
                            <form id="logout-form" action="/logout" method="POST" style="display: none;">@csrf</form>
                        @endif
                    </div>
                    <sc-install-app v-if="showInstallApp" @close="toggleInstallApp"></sc-install-app>
                    @if ($user)
                        <keep-alive>
                            <sc-profile v-if="profileMenuActive && !isLargeScreen()"
                                        v-cloak
                                        email="{{ $user->email }}"
                                        is-subscribed="{{ $user->is_subscribed }}"
                                        subscription="{{ $subscription }}"
                                        :subscription-active="subscriptionActive"
                                        @close="toggleProfileMenu"
                                        :is-mobile-nav="true"
                            ></sc-profile>
                        </keep-alive>
                    @else
                        <aside class="header-register" v-if="showRegisterForm && !isLargeScreen()" v-cloak>
                            <sc-register-login
                                @toggle="toggleRegisterForm"
                                :is-modal="false"
                            ></sc-register-login>
                        </aside>
                    @endif
                </div>
            </div>
            <aside class="header-fav" v-else-if="favMenuActive && !isLargeScreen()" v-cloak>
                <div class="header-fav-wrapper">
                    <div class="header-fav-content">
                        <h1>Favorites</h1>
                        <div v-if="!faves" class="loading">Loading...</div>
                        <div v-else-if="Array.isArray(faves) && faves.length === 0" class="loading">You do not have any Favorites Yet.</div>
                        <sc-fav v-for="fave in faves"
                                :key="fave.id"
                                :name="fave.name"
                                :amcond="fave.amcond"
                                :pmcond="fave.pmcond"
                                :amsurf="fave.amsurf"
                                :pmsurf="fave.pmsurf"
                                :link="fave.link"
                                :beachid="fave.beachId"
                        ></sc-fav>
                    </div>
                    <div class="right-col">
                        <button class="close-fav" @click="toggleFavMenu">
                            <span v-if="">&times;</span>
                        </button>
                    </div>
                </div>
            </aside>
        </transition>
    </nav>
    <sc-loading :loading="loading"></sc-loading>
    <transition name="pro-modal-transition">
        <sc-fcst-pro-modal
            v-if="showProModal"
            @if ($user)
                :is-logged-in="true"
            @else
                :is-logged-in="false"
            @endif
            :mode="proModalMode"
            :is-large-screen="isLargeScreen()"
        ></sc-fcst-pro-modal>
    </transition>
</div>

@section('end-body')
    <script>
        window.scLoggedIn = @if ($user) true; @else false;  @endif
    </script>
    <script src="{{ mix('/js/manifest.js') }}"></script>
    <script src="{{ mix('/js/vendor.js') }}"></script>
    <script src="{{ mix('/js/app.js') }}"></script>
@show
</body>
</html>

