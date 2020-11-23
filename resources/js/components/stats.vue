<template>
    <div id="stats">
        <h1>Captain Stats</h1>
        <div class="grid-x">
            <div class="cell medium-6 medium-text-center">
                <h2>Total Active Users:</h2>
                <div class="val">{{ userCount }}</div>
            </div>
            <div class="cell medium-6 medium-text-center">
                <h2>Total Active Subscribers:</h2>
                <div class="val">{{ usersSubscribed }}</div>
            </div>
        </div>
        <div class="grid-x">
            <div class="cell medium-6 medium-text-center">
                <h3>New Users Today: </h3>
                <div class="val">{{ usersToday }}</div>
            </div>
            <div class="cell medium-6 medium-text-center">
                <h3>New Subscribers Today:</h3>
                <div class="val">{{ subscribersToday }}</div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "stats.vue",
        data() {
            return {
                userCount:0,
                usersSubscribed:0,
                usersToday:0,
                subscribersToday:0
            }
        },
        created() {
            let self = this;

            //fetch alert
            axios({
                method: 'get',
                url: '/api/stats',
                headers: {'X-CSRF-TOKEN': window.SC.csrfToken}
            })
                .then(function (response) {
                    if (response.data) {
                        self.userCount = response.data.userCount;
                        self.usersSubscribed = response.data.usersSubscribed;
                        self.usersToday = response.data.usersToday;
                        self.subscribersToday = response.data.subscribersToday;
                    }
                })
                .catch(function (error) {
                    console.log('There was an error fetching alert');
                });
        }
    }
</script>

<style lang="scss">
    @import '../../sass/_sc-settings.scss';

    #stats {
        width:100%;
        @include maxWidth;

        @include breakpoint(smedium down) {
            margin:0 12px;
        }

        h1 {
            font-size:2.5rem;
            font-family:$roboto-font;
            font-weight:900;
            margin:1rem 0 2rem;
            color:$dark-blue-heading;
        }

        h2,h3 {
            font-family:$roboto-font;
            font-weight:500;
            font-size:2rem;
            color: $blue-500;


            @include breakpoint(smedium down) {
                font-size:1.5rem;
            }
        }

        .val {
            color:$gray-600;
            font-size:1.5rem;

            @include breakpoint(smedium down) {
                font-size:1.2rem;
            }
        }

        @include breakpoint(medium) {
            .grid-x {
                margin-bottom: 1rem;
            }
        }

        @include breakpoint(smedium down) {
            .cell {
                margin-bottom:1rem;
            }
        }
    }
</style>
