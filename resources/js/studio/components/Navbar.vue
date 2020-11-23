<template>
    <nav id="blog-navbar" class="clearfix">
        <slot name="extra"></slot>

        <div id="navbar-links">
            <router-link :to="{name:'tags'}" class="text-muted text-decoration-none">
                Tags
            </router-link>
            <router-link :to="{name:'topics'}" class="text-muted text-decoration-none ml-3">
                Topics
            </router-link>


            <div v-if="user && user.role_id === 1" id="navbar-user-links">
                <ul class="dropdown menu" data-dropdown-menu>
                    <!--
                    <li v-if="showPostLinks">
                        <a href="#" role="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" viewBox="0 0 24 24" class="icon-cog primary">
                                <path d="M6.8 3.45c.87-.52 1.82-.92 2.83-1.17a2.5 2.5 0 0 0 4.74 0c1.01.25 1.96.65 2.82 1.17a2.5 2.5 0 0 0 3.36 3.36c.52.86.92 1.8 1.17 2.82a2.5 2.5 0 0 0 0 4.74c-.25 1.01-.65 1.96-1.17 2.82a2.5 2.5 0 0 0-3.36 3.36c-.86.52-1.8.92-2.82 1.17a2.5 2.5 0 0 0-4.74 0c-1.01-.25-1.96-.65-2.82-1.17a2.5 2.5 0 0 0-3.36-3.36 9.94 9.94 0 0 1-1.17-2.82 2.5 2.5 0 0 0 0-4.74c.25-1.01.65-1.96 1.17-2.82a2.5 2.5 0 0 0 3.36-3.36zM12 16a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
                                <circle cx="12" cy="12" r="2"/>
                            </svg>
                        </a>
                        <ul class="menu">
                            <li>
                                <a :href="'/' + canvasPath + '/posts/' + postId + '/edit'" class="dropdown-item">Edit post</a>
                            </li>
                            <li>
                                <a :href="'/' + canvasPath + '/stats/' + postId" class="dropdown-item">View stats</a>
                            </li>
                        </ul>
                    </li>
                    -->
                    <li>
                        <a href="#">{{ user.name }}</a>
                        <ul class="menu">
                            <li><a :href="'/' + canvasPath + '/posts'" class="dropdown-item">Posts</a></li>
                            <li><a :href="'/' + canvasPath + '/tags'" class="dropdown-item">Tags</a></li>
                            <li><a :href="'/' + canvasPath + '/topics'" class="dropdown-item">Topics</a></li>
                            <li><a :href="'/' + canvasPath + '/stats'" class="dropdown-item">Stats</a></li>
                            <li><a :href="'/' + canvasPath + '/settings'" class="dropdown-item">Settings</a></li>
                            <li><a style="cursor: pointer" @click.prevent="sessionLogout">Sign out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    export default {
        name: 'navbar',
        props: ['showPostLinks','postId'],
        data() {
            return {
                user: Studio.user,
                avatar: Studio.avatar,
                canvasPath: Studio.canvasPath
            }
        },

        methods: {
            sessionLogout() {
                this.logout()
            },
        }
    }
</script>

<style lang="scss">

    @import '../../../sass/_sc-settings.scss';

    #blog-navbar {
        @include maxContentWidth();

        @include breakpoint(xxlarge) {
            max-width:$max-width-content-xlarge;
        }

        a {
            font-weight:700;
        }
    }

    #navbar-links {
        float:right;

        a:first-child {
            margin-right:20px;
        }
    }

    #navbar-user-links {
        display:none;
    }
</style>
