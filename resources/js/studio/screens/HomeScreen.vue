<template>
    <div id="blog-home">
        <vue-headful
            title="Surf Captain Blog"
            description="Blog posts about updates and surf forecasting articles."
        />

        <navbar>
        </navbar>

        <div id="blog-home-main">
            <h1>Captain's Blog</h1>
            <div v-if="posts.length > 0" id="blog-latest">
                <h4 class="blog-header">Featured</h4>

                <featured-post-list :posts="posts.slice(0, featuredPostCount)"></featured-post-list>

                <h4 v-if="posts.slice(featuredPostCount).length > 0" class="blog-header">All Posts</h4>

                <post-list :posts="posts.slice(featuredPostCount)"></post-list>
            </div>
        </div>
    </div>
</template>

<script>
    import NProgress from 'nprogress'
    import vueHeadful from 'vue-headful'
    import Navbar from "../components/Navbar";
    import PostList from '../components/PostList'
    import FeaturedPostList from "../components/FeaturedPostList";

    export default {
        name: 'home-screen',

        components: {
            FeaturedPostList,
            Navbar,
            PostList,
            vueHeadful
        },

        data() {
            return {
                featuredPostCount: 4,
                posts: [],
            }
        },

        mounted() {
            console.log('fetch posts');
            this.fetchPosts()
        },

        methods: {
            fetchPosts() {
                console.log('get em');
                this.request()
                    .get(Studio.path + '/api/posts')
                    .then(response => {
                        this.posts = response.data.posts;
                        NProgress.done()
                    })
                    .catch(error => {
                        // Add any error debugging...
                        this.$router.push({name: 'home'});

                        NProgress.done()
                    })
            }
        }
    }
</script>

<style lang="scss">

    #blog-home-main {

        h1 {
            
        }
    }

</style>
