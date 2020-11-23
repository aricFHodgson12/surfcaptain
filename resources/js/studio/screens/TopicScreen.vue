<template>
    <div>
        <vue-headful
            title="Surf Captain Blog Topics"
            description="Surf Captain Blog topic categories"
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}" class="btn btn-sm btn-outline-secondary">
                Captain's Blog
            </router-link>
        </navbar>

        <div id="blog-topics">
            <h1>Captain's Topics</h1>
            <p id="blog-topics-desc">Choose a topic to view all related Surf Captain articles.</p>

            <section id="blog-topics-list">
                <div v-if="topics.length > 0">
                    <taxonomy-grid :items="topics" type="topic"></taxonomy-grid>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import NProgress from 'nprogress'
    import vueHeadful from 'vue-headful'
    import Navbar from "../components/Navbar";
    import TaxonomyGrid from "../components/TaxonomyGrid";

    export default {
        name: 'topic-screen',

        components: {
            TaxonomyGrid,
            Navbar,
            vueHeadful
        },

        data() {
            return {
                topics: [],
            }
        },

        mounted() {
            this.fetchTopics()
        },

        methods: {
            fetchTopics() {
                this.request()
                    .get(Studio.path + '/api/topics')
                    .then(response => {
                        this.topics = response.data

                        NProgress.done()
                    })
                    .catch(error => {
                        // Add any error debugging...
                        this.$router.push({name: 'home'})

                        NProgress.done()
                    })
            }
        }
    }
</script>

<style lang="scss">

    @import '../../../sass/sc-settings';

    #blog-topics {
        padding-top:3rem!important;

        @include breakpoint(smedium down) {
            padding-top:2rem !important;
        }

        @include blogContentWidth();

        h1 {
            @include mainBlogHeading();

            @include breakpoint(smedium down) {
                font-size:2rem !important;
            }
        }

        #blog-topics-desc {
            font-size: 125%;
            line-height: 1.6;
            color: $blog-text-secondary;
            font-weight:300;
        }

        #blog-topics-list {
            margin-top:3rem!important;
        }
    }

</style>
