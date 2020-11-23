<template>
    <div v-if="availablePosts.length > 0" class="post-list">
        <div v-for="post in availablePosts" class="post-list-container">
            <router-link :to="{ name: 'post', params: { identifier: publicIdentifier(post), slug: post.slug } }" class="text-decoration-none">
                <div class="card">
                    <div class="grid-x">
                        <div class="cell" :class="post.featured_image ? 'small-8 medium-9' : 'small-12'">
                            <div class="card-section">
                                <h4 class="card-title">{{ post.title }}</h4>
                                <p class="post-list-summary">{{ post.summary }}</p>
                                <p class="post-list-footer">{{ moment(post.published_at).format('MMM D') }} — {{ post.read_time }}</p>
                            </div>
                        </div>
                        <div v-if="post.featured_image" class="cell small-4 medium-3">
                            <div class="card-image">
                                <img :src="post.featured_image" class="card-image" :alt="post.featured_image_caption">
                            </div>
                        </div>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'post-list',

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

    .post-list-container {

        .card {
            box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;
            margin-bottom:1.5rem !important;

            .card-section {
                margin: 0 0 0.75rem 0 !important;
            }

            .card-image img {
                height:100%;
                object-fit:cover;

                @include breakpoint(smedium down) {
                    max-height: 102px;
                }

                @include breakpoint(medium) {
                    width:100%;
                    max-height:112px;
                }

                @include breakpoint(xlarge) {
                    max-height:142px;
                }
            }

            .post-list-summary {
                color: $dark-letter;
            }

            .post-list-footer {
                color: $gray-500;
                font-size: 80%;
            }
        }
    }

</style>
