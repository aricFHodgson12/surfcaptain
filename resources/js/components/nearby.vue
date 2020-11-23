<template>
    <div id="nearby">
        <div class="grid-x">
            <div class="cell large-auto small-order-1 large-order-2" id="nearby-map" :style="'height:'+mapHeight">
                <l-map
                    id="nearby-lmap"
                    v-if="showMap"
                    :bounds="mapBounds"
                    :options="mapOptions"
                    @update:center="mapCenterUpdate"
                    @update:zoom="mapZoomUpdate"
                    @update:bounds="mapBoundsUpdate"
                >
                    <l-tile-layer
                        :url="mapTileUrl"
                        :attribution="mapAttribution"
                        :tile-size="512"
                        :options="{'zoomOffset':-1}"
                    />
                    <l-marker v-for="(location,index) in locations"
                              :key="location.locationBeachId"
                              :lat-lng="[location.locationLat, location.locationLon]"
                              @click="toggleDetails(index)"
                    >
                        <l-icon
                            :id="'marker-'+index"
                            icon-url="images/icon/map_marker.svg"
                            :icon-size="[36,48]"
                            :icon-anchor="[18,48]"
                            :class-name="'marker-'+index"
                            :tooltip-anchor="[10,-24]"
                        />
                        <l-tooltip>{{ location.locationName }}</l-tooltip>
                    </l-marker>
                </l-map>
                <transition name="fade">
                    <div id="search-map" v-if="showSearchButton" @click="fetchLocations(sortMethod,true)">{{ searchButtonText }}</div>
                </transition>
                <div id="nearby-map-loading" v-if="!showMap">Loading Map</div>
            </div>
            <div class="cell large-shrink small-order-2 large-order-1" id="nearby-list" :style="'height:'+listHeight" :class="{'mobile-list-opened': mobileListOpened}">
                <div id="nearby-list-wrapper">
                    <div id="nearby-list-mobile" class="grid-x" v-if="!isLargeScreen()">
                        <div id="nearby-list-mobile-link" class="cell small-4" @click="toggleMobileList()"><a>{{ mobileHandleText }}</a></div>
                        <div class="cell small-4 hide">
                            <div id="nearby-list-mobile-handle"></div>
                        </div>
                    </div>
                    <div id="nearby-list-title">
                        <div id="nearby-sort" v-if="!showDetail" key="sort">
                            <button id="nearby-sort-btn" type="button" @click="toggleSortOptions">
                                <span id="nearby-sort-title">Nearby Locations By: {{ sortLabel }}</span>
                                <img src="/images/icon/arrow_drop_down.svg" alt=""/>
                            </button>
                            <transition name="slide">
                                <ul id="nearby-sort-options" v-if="showSortOptions">
                                    <li @click="fetchLocations('geog')">Geography</li>
                                    <li @click="fetchLocations('surf')">Surf Height</li>
                                    <li @click="fetchLocations('cond')">Surf Conditions</li>
                                </ul>
                            </transition>
                        </div>
                        <div id="nearby-detail-back" v-else @click="toggleDetails(false)" key="back">
                            <button id="nearby-detail-back-btn" type="button">
                                <img src="/images/icon/arrow_back.svg" alt="Back Button"/>
                                Back to Results
                            </button>
                        </div>
                    </div>
                    <div id="loading-locations" v-if="!locationsLoaded">Loading Locations...</div>
                    <transition name="slide-left-right">
                        <ul id="nearby-locs" v-if="!showDetail">
                            <li v-for="(loc,index) in locations" class="nearby-loc">
                                <div class="nearby-loc-title" @click="toggleDetails(index)">{{ loc.locationName }}</div>
                                <div class="nearby-loc-conditions">
                                    <div class="nearby-loc-cond cond-am" v-html="nearbyCond('AM',loc.condAm, loc.surfAm)"></div>
                                    <div class="nearby-loc-cond cond-pm" v-html="nearbyCond('PM',loc.condPm, loc.surfPm)"></div>
                                </div>
                            </li>
                        </ul>
                        <div id="nearby-detail" v-else>
                            <div class="nearby-loc-title">{{ detailName }}</div>
                            <div class="nearby-loc-conditions">
                                <div class="nearby-loc-cond cond-am" v-html="nearbyCond('AM',detailCondAm, detailSurfAm)"></div>
                                <div class="nearby-loc-cond cond-pm" v-html="nearbyCond('PM',detailCondPm, detailSurfPm)"></div>
                            </div>
                            <div id="nearby-loc-text">
                                <div class="nearby-loc-text-label">SURF</div>
                                <div class="nearby-loc-text">{{ detailSurfText }}</div>
                                <div class="nearby-loc-text-label">CONDITIONS</div>
                                <div class="nearby-loc-text">{{ detailCondText }}</div>
                            </div>
                            <div id="nearby-loc-link">
                                <button id="nearby-loc-btn" type="button"><a @click="$root.gotoForecast()" :href="detailLink">View Location Forecast</a></button>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    //import L from 'leaflet';
    import {latLng, latLngBounds} from "leaflet";

    export default {
        props: ['reloaddata'],
        name: "nearby.vue",
        data() {
            return {
                locations: [],
                showSortOptions: false,
                showDetail:false,
                detailName: false,
                detailCondAm: false,
                detailSurfAm: false,
                detailCondPm: false,
                detailSurfPm: false,
                detailSurfText: false,
                detailCondText: false,
                detailLink: false,
                mapHeight: '800px',
                mapHeightSet: false,
                mobileListOpened: false,
                nearbyListEl: false,
                mobileListHeight: '-200',
                mobileDetailHeight: false,
                locationsLoaded: false,
                sortMethod: 'geog',
                showMap: false,
                mapZoom: 9,
                mapBounds: false,
                minLat: false,
                maxLat: false,
                minLon: false,
                maxLon: false,
                mapCenter: latLng(0, 0),
                mapOptions: {
                    zoomSnap: 0.5
                },
                //mapTileUrl: 'https://wc-maps.s3.amazonaws.com/map-tiles/{z}/{x}/{y}.png',
                //mapTileUrl: 'https://api.maptiler.com/maps/312a706c-63ae-4baa-ba5d-1b3f451560f5/{z}/{x}/{y}.png?key=6JMGpIGamjZCUbJu7oLx',
                mapTileUrl: 'https://api.maptiler.com/maps/012cb6ec-79f8-4e69-acf9-9d73140c5598/{z}/{x}/{y}@2x.jpg?key=6JMGpIGamjZCUbJu7oLx',
                mapAttribution:
                    '<a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap</a>',
                showSearchButton: false
            }
        },
        methods: {
            mapZoomUpdate(zoom) {
                this.mapZoom = zoom;
            },
            mapCenterUpdate(center) {
                this.mapCenter = center;
            },
            mapBoundsUpdate(bounds) {
                this.minLat = bounds._southWest.lat;
                this.minLon = bounds._southWest.lng;
                this.maxLat = bounds._northEast.lat;
                this.maxLon = bounds._northEast.lng;
                this.showSearchButton = true;
            },
            nearbyCond(ampm, cond, surf) {
                return '<img src="/images/icon/circle_'+cond+'.svg" />' + '<span class="ampm">'+ ampm +'</span>' + window.SC.capitalize(cond) + ', ' + surf;
            },
            toggleSortOptions() {
                this.showSortOptions = ! this.showSortOptions;
            },
            toggleDetails(locIndex) {

                let markers = document.getElementsByClassName('leaflet-marker-icon');
                markers.src = 'images/icon/map_marker.svg';


                if (locIndex !== false) {
                    let detailLocation = this.locations[locIndex];
                    this.detailName = detailLocation.locationName;
                    this.detailCondAm = detailLocation.condAm;
                    this.detailCondPm = detailLocation.condPm;
                    this.detailSurfAm = detailLocation.surfAm;
                    this.detailSurfPm = detailLocation.surfPm;
                    this.detailSurfText = detailLocation.surfText;
                    this.detailCondText = detailLocation.condText;
                    this.detailLink = '/forecast/' + detailLocation.locationBeachSlug;
                    this.showDetail = true;

                    let activeMarker = document.getElementsByClassName('marker-'+locIndex)[0];
                    setTimeout(function() {
                        //need a small delay after click
                        activeMarker.src = 'images/icon/map_marker_active.svg';
                    },50);

                } else
                    this.showDetail = false;

                this.scrollToTop();

                if (!this.isLargeScreen()) {

                    if (this.showDetail) {
                        this.mobileListOpened = true;
                        //this.$root.bodyScroll = true;
                        let self = this;
                        setTimeout(function () {
                            //make nearby list el the height of the details section
                            let nearbyDetailElHeight = document.getElementById('nearby-detail').offsetHeight;
                            let nearbyListTitleHeight = document.getElementById('nearby-list-title').offsetHeight;
                            self.mobileDetailHeight = (nearbyDetailElHeight + nearbyListTitleHeight) * -1;
                            self.mobileListElTop(self.mobileDetailHeight);
                        }, 10);
                    } else
                        this.mobileListElTop(this.mobileListHeight);
                }
            },
            mapSize() {
                let viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
                let headerNavHeight = document.getElementsByClassName('header-nav')[0].offsetHeight;

                if (this.$root.isLargeScreen()) {
                    this.mapHeight = (viewportHeight - headerNavHeight).toString() + 'px';
                    this.mapHeightSet = true;
                } else {
                    //sometimes the nearby-list-mobile element is available right away, so use loop and check every 10ms to see if element exists
                    let setMobileListTop = setInterval(checkNearbyListMobile, 100);
                    let self = this;
                    let xInt = 0;
                    function checkNearbyListMobile() {
                        let mobileNavHeight = document.getElementsByClassName('nav-mobile-bottom')[0].offsetHeight;
                        let nearbyListMobile = document.getElementById('nearby-list-mobile');
                        if (nearbyListMobile) {
                            let mobileListHeight = nearbyListMobile.offsetHeight;
                            self.mapHeight = (viewportHeight - headerNavHeight - mobileNavHeight - mobileListHeight).toString() + 'px';
                            self.mapHeightSet = true;
                            if (this.mobileListOpened)
                                self.mobileListElTop(this.mobileListHeight);
                            else
                                self.mobileListElTop(0);
                            clearInterval(setMobileListTop);
                        }
                        xInt++;
                        if (xInt > 100)
                            clearInterval(setMobileListTop);
                    }

                    //this.$root.bodyScroll = (this.mobileListOpened);
                }
            },
            fetchLocations(sort=null, searchArea=false) {
                this.locationsLoaded = false;
                this.showSortOptions = false;

                if (sort)
                    this.sortMethod = sort;

                let data = {};
                data.sortMethod = this.sortMethod;

                if (searchArea) {
                    data.minLat = this.minLat;
                    data.minLon = this.minLon;
                    data.maxLat = this.maxLat;
                    data.maxLon = this.maxLon;
                }

                let self = this;
                axios({
                    method: 'get',
                    url: '/api/nearby/locations',
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    params: data
                })
                    .then(function (response) {
                        self.locationsLoaded = true;
                        self.locations = response.data.locations;

                        if (searchArea)
                            self.showSearchButton = false;
                        else {
                            self.mapBounds = latLngBounds([
                                [response.data.map.minLat, response.data.map.minLon],
                                [response.data.map.maxLat, response.data.map.maxLon]
                            ]);
                        }
                        self.showLocations();
                        self.$root.reloadFcstData = false;
                    })
                    .catch(function (error) {
                        document.getElementById('nearby-map-loading').innerHTML('There was an error fetching the map locations: '+error);
                        console.log('There was an error fetching the map locations: '+error);
                        if (searchArea)
                            self.showSearchButton = false;
                    });
            },
            showLocations() {
                //make sure map height is set, then show locations on map.
                let isMapHeightSet = setInterval(checkMapHeight, 50);
                let self = this;
                let xInt = 0;
                function checkMapHeight() {
                    if (self.mapHeightSet) {
                        self.showMap = true;
                        clearInterval(isMapHeightSet);
                    }
                    xInt++;
                    if (xInt > 100)
                        clearInterval(isMapHeightSet);
                }
            },
            isLargeScreen() {
                return this.$root.isLargeScreen();
            },
            toggleMobileList() {
                this.mobileListOpened = ! this.mobileListOpened;

                if (this.mobileListOpened)
                    this.mobileListElTop(-200);
                else
                    this.mobileListElTop(0);
            },
            mobileListElTop(top) {
              this.nearbyListEl.style.top = top+'px';
              this.nearbyListEl.style.marginBottom = top+'px';
            },
            addListSwipeListener() {
                let self = this;
                let nearbyListHandle = document.getElementById('nearby-list-mobile');

                let viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
                let mobileNavHeight = document.getElementsByClassName('nav-mobile-bottom')[0].offsetHeight;
                let mobileListHeight = nearbyListHandle.offsetHeight;
                let pageYStartPos = viewportHeight - mobileNavHeight - mobileListHeight;

                let pageYDiff = 0;
                let pageYThreshold = 100;
                let allowClose = false;

                nearbyListHandle.addEventListener("touchstart", touchStart);
                nearbyListHandle.addEventListener("touchmove", touchMove);
                nearbyListHandle.addEventListener("touchend", touchEnd);

                function touchMove(e) {
                    pageYDiff = e.touches.item(0).pageY - pageYStartPos;
                    //console.log('page y diff: '+pageYDiff);
                    if (! self.mobileListOpened && pageYDiff < 0)
                        self.mobileListElTop(pageYDiff);
                    else if (self.mobileListOpened && pageYDiff > 0 && allowClose === true) {
                        self.$root.bodyScroll = false;
                        let detailsTop = (self.showDetail) ? self.mobileDetailHeight : self.mobileListHeight;
                        self.mobileListElTop(parseInt(detailsTop) + pageYDiff);
                    }
                }

                function touchStart(e) {
                    pageYDiff = 0;
                    pageYStartPos = e.touches.item(0).pageY;

                    if (self.mobileListOpened)
                        allowClose = (window.pageYOffset <= 0);
                }

                function touchEnd() {
                    let detailsTop = (self.showDetail) ? self.mobileDetailHeight : self.mobileListHeight;

                    //pageYDiff = 0, handles single tap without moving, which will open/close the menu
                    if (! self.mobileListOpened) {
                        if (pageYDiff < (pageYThreshold * -1) || pageYDiff === 0) {
                            self.mobileListOpened = true;
                            self.mobileListElTop(detailsTop);
                            self.$root.bodyScroll = true;
                        } else
                            self.mobileListElTop(0);
                    } else if ( (allowClose && pageYDiff > pageYThreshold) || pageYDiff === 0) {
                            self.mobileListOpened = false;
                            self.mobileListElTop(0);
                            self.$root.bodyScroll = false;

                            if (pageYDiff === 0)
                                self.scrollToTop();

                    } else if (allowClose) {
                            self.mobileListElTop(detailsTop);
                            self.$root.bodyScroll = true;
                    }
                }
            },
            scrollToTop() {
                window.scrollTo(0, 0);
            }
        },
        computed: {
            listHeight() {
                return (this.$root.isLargeScreen()) ? this.mapHeight : '';
            },
            sortLabel() {
                if (this.sortMethod === 'geog')
                    return 'Geography';
                else if (this.sortMethod === 'surf')
                    return 'Surf Height';
                else if (this.sortMethod === 'cond')
                    return 'Conditions';
            },
            searchButtonText() {
                if (!this.locationsLoaded)
                    return 'Loading Locations';
                else
                    return  'Search This Area';
            },
            mobileHandleText() {
                if (this.mobileListOpened)
                    return 'HIDE LIST';
                else
                    return 'SHOW LIST';
            }
        },
        created() {
            this.fetchLocations();

            let self = this;
            window.addEventListener('beforeunload', function() {
                self.scrollToTop();
            });

            if (! this.$root.isSmallScreen())
                this.mobileListHeight = -250;
        },
        mounted() {
            this.nearbyListEl = document.getElementById('nearby-list');
            this.mapSize();

            let self = this;
            if (this.isLargeScreen()) {
                window.addEventListener('resize', function () {
                    self.mapSize();
                });
            }

            //if (! this.isLargeScreen())
            //    this.addListSwipeListener();

            document.getElementsByTagName('body')[0].classList.add('overscroll-contain');

        },
        watch: {
            reloaddata(newVal) {
                if (newVal === true)
                    this.fetchLocations();
            }
        },
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings';

    #nearby {

        #nearby-map {
            z-index: 0;
            background: $blue-00;
            position:relative;

            .leaflet-container {
                background: $blue-00;
                z-index: 0;
            }

            .leaflet-bar a, .leaflet-bar a:hover {
                color: $dark-letter;
            }

            /*
            @include breakpoint(medium down) {
                .leaflet-tooltip-pane {
                    display: none;
                }
            }
             */

            .leaflet-marker-pane img {
                outline: 0;
            }

            #search-map {
                position: absolute;
                z-index: 1;
                left: 50%;
                transform: translateX(-50%);
                top: 10px;
                padding: 5px 25px;
                background: $blue-500;
                border-radius: 4px;
                color: #fff;
                font-size: 14px;
                font-weight:500;
                box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.25);
                cursor:pointer;
            }

            #nearby-map-loading {
                color:$gray-500;
                font-size:16px;
                position: absolute;
                left:50%;
                top:5%;
                transform:translateX(-50%);
            }
        }

        #nearby-list {
            z-index:1;
            position: relative;
            background: #fff;
            top:0;
            //transition:top 0.5s;
            min-width:320px;
            overflow-y:auto;
            @include breakpoint(medium down) {
                overscroll-behavior: none;
            }

            &.mobile-list-opened {
                position:relative !important;
                //top:-200px !important;
            }

            @include breakpoint(medium down) {
                width:100%;
            }

            @include breakpoint(large) {
                width:400px;
            }

            @include breakpoint(large) {
                overflow-y: auto;
            }

            #nearby-list-mobile {
                padding:9px 0;
                box-shadow: 0 -10px 12px 0 rgba(49,50,57,0.20);
                border-radius: 4px 4px 0 0;
                border-bottom:1px solid $gray-200;

                @include breakpoint(medium) {
                    padding:15px 0;
                }

                #nearby-list-mobile-link {
                    margin-left:12px;
                }

                #nearby-list-mobile-handle {
                    height:6px;
                    margin-top:7px;
                    border-radius:4px;
                    background: $blue-00;

                    @include breakpoint(medium) {
                        width:72px;
                        height:8px;
                    }
                }

                #nearby-list-mobile-line {
                    width:100%;
                    height:1px;
                    background:$gray-200;
                }
            }


            #nearby-list-title {
                border-bottom: 1px solid $gray-200;
                position: relative;
                font-family:$roboto-condensed-font;

                @include breakpoint(large) {
                    border-top: 1px solid $gray-200;
                }

                #nearby-sort-btn, #nearby-detail-back-btn {
                    font-size:10px;
                    color:$blue-500;
                    cursor:pointer;
                    outline:0;
                    padding: 12px 20px;
                    @include breakpoint(smedium down) {
                        padding: 12px;
                    }

                    &#nearby-sort-btn {
                        outline: 0;

                        img {
                            width: 10px;
                        }
                    }

                    &#nearby-detail-back-btn {

                        img {
                            width: 24px;
                            margin-right: 3px;
                        }
                    }
                }

                #nearby-sort-options {
                    list-style: none;
                    margin: 0;
                    width: 100%;
                    position: absolute;
                    background: #fff;
                    border-bottom: 1px solid $gray-200;
                    box-shadow: 0 5px 5px 0 rgba(0, 0, 0, 0.41);

                    li {
                        color: $blue-500;
                        border-top: 1px solid $gray-200;
                        font-size: 12px;
                        cursor: pointer;
                        padding: 12px 20px;
                        @include breakpoint(medium down) {
                            padding:12px;
                        }
                    }
                }
            }

            #loading-locations {
                color: $blue-900;
                font-size: 16px;
                padding: 12px;

                @include breakpoint(large) {
                    padding: 12px 20px;
                }
            }

            #nearby-locs {
                list-style: none;
                margin: 0;
            }

            .nearby-loc, #nearby-detail {
                border-bottom:1px solid $gray-200;
                padding: 12px 12px 12px 20px;

                @include breakpoint(smedium down) {
                    padding:12px;
                }

                .nearby-loc-title {
                    font-size:16px;
                    line-height:24px;
                    color:$gray-500;
                    margin-bottom:8px;
                }

                &.nearby-loc .nearby-loc-title {
                    cursor:pointer;
                }

                .nearby-loc-conditions {

                    .nearby-loc-cond {
                        display:inline-block;
                        color:$gray-400;
                        font-size:12px;

                        img {
                            width:12px;
                            margin-right:5px;
                        }

                        .ampm {
                            font-weight:bold;
                            margin-right:4px;
                        }

                        &.cond-am {
                            min-width:120px;
                        }

                        &.cond-pm {
                            margin-left: 20px;
                        }
                    }
                }

                #nearby-loc-text {
                    border-top:1px solid $gray-200;
                    font-size:12px;
                    margin-top:12px;

                    .nearby-loc-text-label {
                        color:$gray-300;
                        margin:4px 0;
                    }

                    .nearby-loc-text {
                        color:$gray-900;
                        letter-spacing: 0;
                        line-height:14px;
                    }
                }

                #nearby-loc-link {
                    text-align:center;
                    margin-top:12px;

                    #nearby-loc-btn {
                        @include actionButton();

                        a {
                            color: $blue-500;
                        }
                    }
                }
            }
        }
    }
</style>
