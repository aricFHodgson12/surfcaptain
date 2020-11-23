<template>
    <div>
        <vue-headful
            title="Surf Captain Blog Tags"
            description="Surf Captain tags describe the content within the blog post."
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}" class="btn btn-sm btn-outline-secondary">
                Captain's Blog
            </router-link>
        </navbar>

        <div id="blog-tags">
            <h1>Captain's Tags</h1>
            <p id="blog-tags-desc">Choose a tag to view all related Surf Captain articles.</p>

            <section id="blog-tags-list">
                <div v-if="tags.length > 0">
                    <taxonomy-grid :items="tags" type="tag"></taxonomy-grid>
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
        name: 'tag-screen',

        components: {
            TaxonomyGrid,
            Navbar,
            vueHeadful
        },

        data() {
            return {
                tags: [],
            }
        },

        mounted() {
            this.fetchTags()
        },

        methods: {
            fetchTags() {
                this.request()
                    .get(Studio.path + '/api/tags')
                    .then(response => {
                        this.tags = response.data

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

    #blog-tags {
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

        #blog-tags-desc {
            font-size: 125%;
            line-height: 1.6;
            color: $blog-text-secondary;
            font-weight:300;
        }

        #blog-tags-list {
            margin-top:3rem!important;
        }
    }
</style>
