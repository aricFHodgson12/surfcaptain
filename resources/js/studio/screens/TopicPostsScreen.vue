<template>
    <div>
        <vue-headful
            v-if="isReady"
            :title="topic.name + ' — Surf Captain Blog'"
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}" class="btn btn-sm btn-outline-secondary">
                Captain's Blog
            </router-link>
        </navbar>

        <div v-if="isReady">
            <div id="topic-posts">
                <h1>{{ topic.name }}</h1>

                <div id="topic-posts-wrapper">
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
        name: 'topic-posts-screen',

        components: {
            Navbar,
            PostList,
            vueHeadful
        },

        data() {
            return {
                posts: [],
                topic: null,
                isReady: false,
            }
        },

        mounted() {
            this.fetchData()
        },

        methods: {
            fetchData() {
                this.request()
                    .get(Studio.path + '/api/topics/' + this.$route.params.slug)
                    .then(response => {
                        this.topic = response.data.topic
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
