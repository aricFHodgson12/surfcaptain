<template>
    <div>
        <vue-headful
            v-if="isReady"
            :title="tag.name + ' — Surf Captain Blog'"
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}" class="btn btn-sm btn-outline-secondary">
                Captain's Blog
            </router-link>
        </navbar>

        <div v-if="isReady">
            <div id="tag-posts">
                <h1>{{ tag.name }}</h1>

                <div id="tag-posts-wrapper">
                    <h4 class="blog-header-secondary">All Posts</h4>

                    <post-list :posts="posts"></post-list>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NProgress from 'nprogress'
    import vueHeadful from 'vue-headful'
    import PostList from '../components/PostList'
    import Navbar from "../components/Navbar";

    export default {
        name: 'tag-posts-screen',

        components: {
            Navbar,
            PostList,
            vueHeadful
        },

        data() {
            return {
                posts: [],
                tag: null,
                isReady: false,
            }
        },

        mounted() {
            this.fetchData()
        },

        methods: {
            fetchData() {
                this.request()
                    .get(Studio.path + '/api/tags/' + this.$route.params.slug)
                    .then(response => {
                        this.tag = response.data.tag
                        this.posts = response.data.posts
                        this.isReady = true

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

</style>
