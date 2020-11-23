<template>
    <div class="grid-x featured-post">
        <div v-for="post in availablePosts" class="cell small-12 medium-6 featured-post-container">
            <router-link :to="{ name: 'post', params: { identifier: publicIdentifier(post), slug: post.slug } }" class="text-decoration-none">
                <div class="card">
                    <div class="card-image">
                        <img v-if="post.featured_image" :src="post.featured_image" class="card-image" :alt="post.featured_image_caption">
                    </div>
                    <div class="card-section">
                        <h4 class="card-title">{{ post.title }}</h4>
                        <p>{{ post.summary }}</p>
                    </div>
                    <div class="card-divider">
                        <span>{{ moment(post.published_at).format('MMM D') }}</span>
                        <span>{{ post.read_time }}</span>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'featured-post-list',

        props: {
            posts: {
                type: Array,
                required: true,
            },
        },

        data() {
            return {
                availablePosts: this.posts,
            }
        }
    }
</script>

<style lang="scss">
    @import '../../../sass/sc-settings';

    .featured-post {

        .featured-post-container {
            margin-bottom:1.5rem !important;

            @include breakpoint(medium up) {
                &:nth-child(odd) {
                    padding-right: 12px;
                }
            }

            .card {
                box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;

                .card-divider {
                    font-size: 80%;
                    background:unset;

                    > span {
                        width:50%;
                        &:last-child {
                            text-align: right;
                        }
                    }
                }

                .card-image img {
                    width: 100%;
                    object-fit: cover;

                    @include breakpoint(large only) {
                        max-height:300px;
                    }

                    @include breakpoint(xlarge) {
                        max-height:350px;
                    }

                    @include breakpoint(medium down) {
                        max-height:225px;
                    }
                }
            }
        }
    }

</style>
