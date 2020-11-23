<template>
    <aside class="off-canvas-install">
        <div class="install-content">
            <h1>Install App</h1>
            <h2>Access Surf Captain Directly from your Home Screen</h2>
            <p>For a better Surf Captain Experience, install the app on your mobile device.</p>
            <div class="install-header">Benefits:</div>
            <ul>
                <li>Faster / Easier access</li>
                <li>Auto updated Forecasts/Weather</li>
                <li>Surf Captain icon on your home screen</li>
                <li>Larger display than in the browser</li>
            </ul>
            <div class="install-header">To Install</div>
            <p>Open surfcaptain.com on your mobile device. For <b>Apple devices</b>, tap the browser 'Share' button and select 'Add to Home Screen'. For <b>Android devices</b>, tap the browser 'Menu' button and select 'Add to Home Screen'.</p>
            <p>Once these steps are complete, you'll see an icon like the one below on your device's home screen.</p>

            <img src="/images/manifest/icon-192-blue.png" />
            <div class="right-col hide-for-large">
                <button id="close-install" class="close-profile" @click="$emit('close')">
                    <span><img src="/images/icon/chevron_left_white.svg"></span>
                </button>
            </div>
        </div>
    </aside>
</template>

<script>
    export default {
        name: "main-install-app.vue"
    }
</script>

<style lang="scss">

    @import '../../sass/sc-settings.scss';

    .off-canvas-install {
        padding-bottom: env(safe-area-inset-bottom);
    }

    .install-content {
        position:relative;
        margin: 0 48px 48px 0;
        padding:0 12px;

        @include breakpoint(medium down) {
            padding:0 12px 148px;
        }

        h2 {
            font-size:18px;
            color:$blue-100;
        }

        .install-header {
            font-size:16px;
            font-weight:bold;
            margin-bottom:4px;
        }

        ul {
            list-style-type:disc;
            margin-left:24px;
        }

        li {
            border-top: none;
            padding-bottom:12px;
            color: #fff;
            font-size: 14px;
        }

        img {
            width: 120px;
            border-radius: 18px;
        }
    }
</style>
