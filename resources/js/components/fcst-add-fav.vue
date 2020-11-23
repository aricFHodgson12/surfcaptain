<template>
    <button class="fcst-add-fav-btn" type="button">
        <div class="fcst-add-remove-fav" @click="addRemoveFav">
            <img v-if="favActionText" :src="favActionIcon">
            {{ favActionText }}
            <transition name="fade">
                <span v-if="loading" class="fav-loader"></span>
            </transition>
        </div>
    </button>
</template>

<script>
    export default {
        name: "fcst-add-fav.vue",
        props: ['locationBeachId','loggedIn'],
        data() {
            return {
                action: false,
                loading:false,
                error:false,
                success:false
            }
        },
        methods: {
            addRemoveFav() {
                this.error = false;
                this.success = false;
                if (this.action === 'add')
                    this.addFav();
                else
                    this.removeFav();
            },
            removeFav() {
                this.loading = true;
                let self = this;
                axios({
                    method: 'delete',
                    url: '/api/favorites/remove-favorite/'+this.locationBeachId,
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.success) {
                            self.action = 'add';
                            self.$emit('addfavmessage', { success: 'This location has been removed'});
                        } else
                            self.$emit( 'addfavmessage', { error: 'Sorry, an error occurred'});

                        self.loading = false;

                        gtag('event', 'fave', {
                            'event_category' : 'forecast',
                            'event_label' : 'remove'
                        });
                    })
                    .catch(function (error) {
                        console.log(error);
                        self.$emit('addfavmessage', { error: 'Sorry, an error occurred'});
                        self.loading = false;
                    })
            },
            addFav() {
                if (this.loggedIn) {
                    this.loading = true;
                    let self = this;
                    axios({
                        method: 'post',
                        url: '/api/favorites/add-favorite/' + this.locationBeachId,
                        headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                    })
                        .then(function (response) {
                            if (response.data.success) {
                                self.action = 'remove';
                                self.$emit('addfavmessage',{ success: 'This location has been saved to your favorites'});
                            } else
                                self.$emit('addfavmessage',{ error: 'Sorry, an error occurred'});
                            self.loading = false;

                            gtag('event', 'fave', {
                                'event_category' : 'forecast',
                                'event_label' : 'add'
                            });
                        })
                        .catch(function (error) {
                            console.log(error);
                            self.loading = false;
                            self.$emit('addfavmessage', { error: 'Sorry, an error occurred'});
                        })
                } else
                    this.$root.toggleProModal('register');
            }
        },
        computed: {
            favActionText() {
                if (this.action === 'add')
                    return 'Add to Favorites';
                else if (this.action === 'remove')
                    return 'Remove from Favorites';
                else
                    return '';
            },
            favActionIcon() {
                if (this.action === 'add')
                    return '/images/icon/bookmark_border.svg';
                else
                    return '/images/icon/bookmark_border.svg';
            }
        },
        created() {
            //is this location a fav?
            if (this.loggedIn) {
                let self = this;
                axios({
                    method: 'get',
                    url: '/api/favorites/is-favorite/' + this.locationBeachId,
                    headers: {'X-CSRF-TOKEN': window.SC.csrfToken},
                })
                    .then(function (response) {
                        if (response.data.isFavorite)
                            self.action = 'remove';
                        else
                            self.action = 'add';
                    })
                    .catch(function (error) {
                        console.log(error);
                    })
            } else
                this.action = 'add';
        }
    }
</script>

<style lang="scss">
    @import '../../sass/sc-settings.scss';

    .fcst-add-fav-btn {
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

        @include breakpoint(medium only) {
            margin: 18px 0;

        }

        @include breakpoint(large) {
            margin:36px 0 24px;
        }

        img {
            width:16px;
            margin-right:3px;
        }

        .fcst-add-remove-fav {
            display:inline-block;
            position:relative;

            .fav-loader {
                @include loader($blue-500, #fff, 3px, 20px, 20px);
                position:absolute;
                left:50%;
                top:18px;
                z-index:1;
            }
        }
    }
</style>
