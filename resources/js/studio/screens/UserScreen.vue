<template>
    <div>
        <vue-headful
            v-if="isReady"
            :title="user.name + ' — Surf Captain Blog'"
            :description="summary"
            :image="avatar"
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}" class="btn btn-sm btn-outline-secondary">
                Captain's Blog
            </router-link>
        </navbar>

        <div v-if="isReady">
            <div id="blog-user">
                <div id="blog-user-image">
                    <img :src="avatar" :alt="user.name" id="blog-user-img">
                </div>
                <div id="blog-user-desc">
                    <h1>{{ user.name }}</h1>
                    <p id="blog-user-summary">
                        {{ summary }}
                    </p>
                </div>
            </div>

            <div id="blog-user-posts">
                <div v-if="posts.length > 0">
                    <h4 class="blog-header">Featured</h4>

                    <featured-post-list :posts="posts.slice(0, featuredPostCount)"></featured-post-list>

                    <h4 v-if="posts.slice(featuredPostCount).length > 0" class="blog-header">All Posts</h4>

                    <post-list :posts="posts.slice(featuredPostCount)"></post-list>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import NProgress from 'nprogress'
    import vueHeadful from 'vue-headful';
    import PostList from '../components/PostList'
    import Navbar from "../components/Navbar";
    import FeaturedPostList from "../components/FeaturedPostList";

    export default {
        name: 'user-screen',

        components: {
            Navbar,
            PostList,
            FeaturedPostList,
            vueHeadful
        },

        data() {
            return {
                user: null,
                avatar: null,
                summary: null,
                featuredPostCount: 4,
                posts: [],
                isReady: false,
            }
        },

        created() {
            this.fetchData()
        },

        methods: {
            fetchData() {
                this.request()
                    .get(Studio.path + '/api/users/' + this.$route.params.identifier)
                    .then(response => {
                        this.user = response.data.user
                        this.avatar = response.data.avatar
                        this.summary = response.data.summary
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
        },
    }
</script>

<style lang="scss">

    @import "../../../sass/sc-settings";

    #blog-user {
        -webkit-box-align: center;
        align-items: center;
        @include blogContentWidth();
        margin: 3rem auto 2rem;

        @include breakpoint(smedium down) {
            margin: 2rem auto;
            font-size:2rem !important;
        }
    }

    #blog-user-image {
        vertical-align:top;
        display:inline-block;

        @include breakpoint(medium up) {
            margin-right:30px;
        }

        @include breakpoint(smedium down) {
            margin-right:20px;
        }

        #blog-user-img {
            border-radius: 50%;

            @include breakpoint(large up) {
                width:120px;
            }

            @include breakpoint(medium down) {
                width:100px;
            }
        }
    }

    #blog-user-desc {
        display:inline-block;

        h1 {
            color:$dark-blue-heading;
            font-weight:900;
            font-size:3rem;


            @include breakpoint(medium only) {
                font-size:2.5rem;
            }

            @include breakpoint(smedium down) {
                font-size:1.5rem;
            }
        }
    }

    #blog-user-summary {
        color: $blog-text-secondary;
    }

    #blog-user-posts {
        @include blogContentWidth;
    }

</style>
