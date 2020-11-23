<template>
    <div class="cell">
        <div class="card">
            <a :href="link">
            <div class="card-section">
                <img :src="featuredImage" class="featured-image">
                <div class="home-latest-blog-label">BLOG POST</div>
                <h2>{{ title }}</h2>
                <p>{{ summary }}</p>
                <div class="home-latest-blog-link">Read More<img src="/images/icon/chevron_right_blue300.svg"></div>
            </div>
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        name: "home-blog.vue",
        props: ['title', 'link', 'summary', 'featuredImage']
    }
</script>

<style>

</style>
