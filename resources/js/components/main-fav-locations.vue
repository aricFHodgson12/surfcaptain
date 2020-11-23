<template>
    <div class="header-fav-locs">
        <transition name="slide-left-right">
            <div key="1" v-if="favRemoved === false">
                <div class="grid-x">
                    <div class="fav-name-remove cell large-3 small-order-1 large-order-1" :class="nameRemoveClass">
                        <transition name="slide-left-right">
                            <div key="1" v-if="remove === false">
                                <div class="header-fav-remove" @click="remove=true">
                                    <img src="/images/icon/remove_circle_outline_24px.svg">
                                </div>
                                <div class="header-fav-name">{{ name }}</div>
                            </div>
                            <div key="2" v-else class="header-fav-remove-btns">
                                <button type="button" class="remove-btn" @click="removeFav(beachid)">REMOVE <span v-if="loading" class="remove-loader"></span></button>
                                <button type="button" class="cancel-btn" @click="remove=false">CANCEL</button>
                            </div>
                        </transition>
                    </div>
                    <div class="header-fav-fcst cell large-6 small-12 small-order-3 large-order-2">
                        <div class="header-fav-fcst-row">
                            <div class="header-fav-ampm header-fav-am">AM</div>
                            <div class="header-fav-cond" v-bind:class="amcond">{{ capitalize(amcond) }}: {{ amsurf }}</div>
                        </div>
                        <div class="header-fav-fcst-row">
                            <div class="header-fav-ampm">PM</div>
                            <div class="header-fav-cond" v-bind:class="pmcond">{{ capitalize(pmcond) }}: {{ pmsurf }}</div>
                        </div>
                    </div>
                    <div v-if="remove === false || isLargeScreen() === true" class="cell small-2 large-3 small-order-2 large-order-3 header-fav-link">
                        <a :href="'/forecast/'+link">
                            <span class="show-for-large">View Full Forecast</span>
                            <img src="/images/icon/search_blue_100.svg">
                        </a>
                    </div>
                </div>
                <transition name="fade">
                    <div v-if="favMessage" class="fav-message">{{ favMessage }}</div>
                </transition>
            </div>
            <div key="2" v-else class="header-fav-removed">
                {{ name }} has been removed from your favorites.
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: "fav-locations.vue",
        props: ['beachid','name','amcond','pmcond','amsurf','pmsurf','link'],
        data() {
            return {
                remove: false,
                favRemoved: false,
                favMessage: false,
                loading:false
            }
        },
        methods: {
            capitalize(cond) {
                return window.SC.capitalize(cond);
            },
            isLargeScreen() {
                return this.$root.isLargeScreen();
            },
            removeFav(beachid) {
                this.loading = true;

                let self = this;
                axios({
                    method: 'delete',
                    url: '/api/favorites/remove-favorite/'+beachid,
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.success)
                            self.favRemoved = true;
                        else
                            self.favMessage = 'Sorry, an error occurred';

                        self.loading = false;

                        gtag('event', 'fave', {
                            'event_category' : 'forecast',
                            'event_label' : 'remove'
                        });
                    })
                    .catch(function (error) {
                        self.favMessage = "Sorry, an error occurred";
                        self.loading = false;
                    })
            }
        },
        computed: {
            nameRemoveClass() {
                return (this.remove) ? 'small-12' : 'small-10';
            }
        },
        mounted() {
            this.$root.loadAnchorListeners(); //this reloads all anchor tags, and assigns click listener.
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';
</style>
