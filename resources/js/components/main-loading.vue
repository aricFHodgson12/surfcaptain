<template>
    <div class="vld-parent">
        <loading :active.sync="loading"
                 :can-cancel="true"
                 :on-cancel="onCancel"
                 :is-full-page="fullPage"
                 color="#002D45"
        ></loading>
    </div>
</template>

<script>
    //import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        name: "main-loading.vue",
        props: ['loading'],
        data() {
            return {
                fullPage: true
            }
        },
        components: {
            'loading': require('vue-loading-overlay')
        },
        methods: {
            onCancel() {
                console.log('User cancelled the loader.')
            }
        }
    }
</script>

<style lang="scss">
    .vld-overlay {
        bottom: 0;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        align-items: center;
        display: none;
        justify-content: center;
        overflow: hidden;
        z-index: 9999;
    }

    .vld-overlay.is-active {
        display: flex;
    }

    .vld-overlay.is-full-page {
        z-index: 9999;
        position: fixed;
    }

    .vld-overlay .vld-background {
        bottom: 0;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        background: #fff;
        opacity: 0.5;
    }

    .vld-overlay .vld-icon, .vld-parent {
        position: relative;
    }
</style>
