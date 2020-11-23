<template>
    <div>
        <vue-headful
            v-if="isReady"
            :title="post.title + ' — Surf Captain Blog'"
            :description="post.summary"
            :image="post.featured_image"
            :url="meta.canonical_link"
        />

        <navbar>
            <router-link slot="extra" :to="{name: 'home'}">
                Captain's Blog
            </router-link>
        </navbar>

        <div v-if="isReady">
            <div id="post-article">
                <h1>{{ post.title }}</h1>

                <div class="media">
                    <router-link :to="{name: 'user', params: { identifier: publicIdentifier(post) }}">
                        <img :src="avatar"
                             class="mr-3 rounded-circle shadow-inner"
                             style="width: 50px"
                             :alt="user.name">
                    </router-link>

                    <div class="media-body">
                        <router-link :to="{name: 'user', params: { identifier: publicIdentifier(post) }}" class="text-decoration-none">
                            <p>{{ user.name }}</p>
                        </router-link>
                        <span>{{ moment(post.published_at).format('MMM D, Y') }} — {{ post.read_time }}</span>
                    </div>
                </div>

                <img v-if="post.featured_image"
                     :src="post.featured_image"
                     class="article-feature-image img-fluid"
                     :alt="post.featured_image_caption"
                     :title="post.featured_image_caption">

                <p v-if="post.featured_image && post.featured_image_caption"
                   class="featured-image-caption"
                   v-html="post.featured_image_caption">
                </p>

                <div class="post-content">
                    <div v-html="post.body"></div>
                </div>

                <div v-if="tags" class="post-article-tags">
                    <router-link
                        v-for="tag in tags"
                        :key="tag.id"
                        :to="{ name: 'tag-posts', params: { slug: tag.slug } }"
                        class="tag-badge">
                        {{ tag.name }}
                    </router-link>
                </div>
            </div>

            <div v-if="meta.canonical_link" class="post-content">
                <hr>
                <p class="post-content-canonical">
                    This post was originally published on <a :href="meta.canonical_link" target="_blank" rel="noopener">{{ parseURL(meta.canonical_link).hostname }}</a>
                </p>
            </div>

            <section id="post-article-related">
                <div v-if="related.length > 0">
                    <h4 class="blog-header-secondary">Related Posts</h4>

                    <post-list :posts="related"></post-list>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
    import hljs from 'highlight.js'
    import PostList from "../components/PostList";
    import NProgress from 'nprogress'
    import vueHeadful from 'vue-headful'
    import mediumZoom from 'medium-zoom'
    import Navbar from "../components/Navbar";

    export default {
        name: 'post-screen',

        components: {
            Navbar,
            PostList,
            vueHeadful
        },

        data() {
            return {
                user: null,
                post: null,
                tags: null,
                topic: null,
                username: null,
                avatar: null,
                meta: null,
                related: [],
                isReady: false,
                canvasPath: Studio.canvasPath,
            }
        },

        created() {
            this.fetchData()
        },

        updated() {
            document.querySelectorAll('.embedded_image img').forEach((image) => {
                mediumZoom(image)
            })
            document.querySelectorAll('pre').forEach((block) => {
                hljs.highlightBlock(block)
            })

            // Render any Tweets inside the editor
            let tweets = document.querySelectorAll('div.ql-tweet')
            console.log(tweets);
            for (let i = 0; i <tweets.length; i++) {
                while (tweets[i].firstChild) {
                    tweets[i].removeChild(tweets[i].firstChild)
                }

                twttr.widgets.createTweet(tweets[i].dataset.id, tweets[i])
            }
        },

        watch: {
            '$route.params.slug': function (slug) {
                this.isReady = false
                this.related = []
                this.fetchData()
            }
        },

        methods: {
            fetchData() {
                this.request()
                    .get(Studio.path + '/api/posts/' + this.$route.params.identifier + '/' + this.$route.params.slug)
                    .then(response => {
                        this.user = response.data.user
                        this.post = response.data.post
                        this.tags = response.data.post.tags
                        this.topic = response.data.post.topic
                        this.username = response.data.username
                        this.avatar = response.data.avatar
                        this.meta = response.data.meta
                        this.related = response.data.related
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

        computed: {
            postBelongsToAuthUser() {
                if (Studio.user) {
                    return this.user.id === Studio.user.id
                } else {
                    return false
                }
            }
        }
    }
</script>

<style lang="scss">
    @import '../../../sass/sc-settings';

    #post-article {
        @include blogContentWidth();

        h1 {
            color: $dark-blue-heading;
            font-weight:900;
            padding-top:3rem!important;
            margin-bottom:1.5rem!important;

            @include breakpoint(smedium down) {
                padding-top:2rem !important;
                font-size:2rem !important;
            }
        }

        .media {

            display: -webkit-box;
            display: flex;
            -webkit-box-align: start;
            align-items: flex-start;

            img {
                margin-right: 1rem!important;
                border-radius:50%;
            }
        }

        .media-body {
            p {
                margin-bottom: 0.5rem;
            }

            span {
                color: $gray-500;
            }
        }

        .article-feature-image {
            width:100%;
            padding-top: 1.5rem!important;
            max-width: 100%;
            height: auto;
        }

        .post-article-tags {
            margin-top:3rem;

            .tag-badge {
                text-decoration: none!important;
                text-transform: uppercase;
                padding: .5rem!important;
                margin: 0.25rem 0.5rem 0.25rem 0!important;
                display: inline-block;
                font-size: 75%;
                font-weight: 700;
                line-height: 1;
                text-align: center;
                white-space: nowrap;
                vertical-align: baseline;
                border-radius: .25rem;
                color: #1a202c;
                background-color: #f7fafc;
                -webkit-transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;

                &:focus, &:hover {
                    color: #1a202c;
                    background-color: #d2e3ee;
                }
            }

        }
    }

    #post-article-related {
        @include blogContentWidth();
        margin: 3rem auto 0;
    }

    .post-content::first-letter {
        font-size: 52px;
        line-height: 0;
    }

    .featured-image-caption {
        text-align: center;
        color:$gray-500;
        margin-top: .5em;
        font-size: 0.9rem;
    }

    .featured-image-caption a {
        text-decoration: underline;
    }

    .post-content {
        font-size: 1.1rem;
        line-height: 2;
        -webkit-box-align: center;
        -ms-flex-align: center;
        margin-top:1.5rem!important;
        position:relative;
        -webkit-box-align: center!important;
        align-items: center!important;

        .post-content-canonical {
            text-align: center;
            font-style:italic;
            padding-top:1rem!important;
            margin-bottom:3rem!important;
        }
    }

    .post-content p {
        margin: 2em 0 0 0;
        color: $dark-letter;
    }

    .post-content a {
        text-decoration: underline;
    }

    .post-content h1,
    .post-content h2,
    .post-content h3 {
        margin: 1.5em 0 0 0;
    }

    .post-content blockquote {
        margin-top: 2em;
        font-style: italic;
        font-size: 28px;
        color: $blog-text-muted;
        padding-left: 1.5em;
        line-height: 1.5;
    }

    div.embedded_image {
        margin-top: 2em;
    }

    div.embedded_image > img {
        width: 100%;
        height: auto;
        display: block;
    }

    div.embedded_image > p {
        text-align: center;
        color: $blog-text-muted;
        margin-top: .5em;
        font-size: 0.9rem;
    }

    div.embedded_image[data-layout="wide"] img {
        max-width: 1024px;
        margin: 0 auto 30px;
    }

    div.embedded_image[data-layout=wide] {
        width: 100vw;
        position: relative;
        left: 50%;
        margin-left: -50vw;
    }

    div.post-content hr {
        border: none;
        margin: 3em 0 4em 0;
        color: $gray-900;
        letter-spacing: 1em;
        text-align: center;
    }

    div.post-content hr:before {
        content: '...';
    }

    .post-content > p > code {
        background-color: $blog-text-muted;
    }

    pre.ql-syntax {
        margin-top: 2em;
        padding: 1em;
        border-radius: $blog-border-radius;
    }

    @media screen and (max-width: 1024px) {
        .post-content > .embedded_image[data-layout=wide] >>> img {
            max-width: 100%
        }
    }

    div.ql-tweet {
        display: flex;
        justify-content: center;
    }
</style>
