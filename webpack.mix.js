const mix = require('laravel-mix');
const url = require('url');

require('laravel-mix-workbox');

mix.options({
    hmrOptions: {
        host: url.parse(process.env.APP_URL).hostname,
        port: 8080
    },
    extractVueStyles: 'public/css/vue-components.css'
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/maps.scss', 'public/css')
    .sass('resources/sass/about.scss', 'public/css')
    .sass('resources/sass/faq.scss', 'public/css')
    .sass('resources/sass/privacy.scss', 'public/css')
    //.sass('resources/sass/home.scss', 'public/css')
    //.sass('resources/sass/forecast.scss', 'public/css')
    //.js('resources/js/studio/app.js', 'public/js/studio.js')
    //.sass('resources/sass/studio/app.scss', 'public/css/studio.css')
    .extract()
    .webpackConfig({
        output: {
            publicPath: "",
        }
    })
    .generateSW({

        swDest: 'sw.js',

        // Define runtime caching rules.
        runtimeCaching: [
            {
                // Match google fonts
                urlPattern: new RegExp('https://fonts.(?:googleapis|gstatic).com/(.*)'),

                // Apply a cache-first strategy.
                handler: 'CacheFirst',

                options: {
                    // Use a custom cache name.
                    cacheName: 'googleapis',

                    // Only cache 10 images
                    expiration: {
                        maxEntries: 10
                    },
                }
            },
            {
                // Match any request that ends with .png, .jpg, .jpeg or .svg.
                urlPattern: /\.(?:png|jpg|jpeg|svg)$/,

                // Apply a cache-first strategy.
                handler: 'CacheFirst',

                options: {
                    // Use a custom cache name.
                    cacheName: 'image-cache',

                    // Only cache 10 images, for 1 week
                    expiration: {
                        maxEntries: 100,
                        maxAgeSeconds: 24 * 3600 * 7, //7 days
                    },
                }
            }
        ],

        skipWaiting: true
    });

if (mix.inProduction()) {
    mix.version();
}


