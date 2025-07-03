import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/js/bootstrap.js",
                "resources/scss/app.scss",
                "resources/css/app.css",
                "resources/js/app.js",
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
        },
    },
    // server: {
    //     allowedHosts: true,
    // },

    //  server: {
    //      host: '10.0.0.6',           // <--mansi aqui pon tu ip
    //      port: 5173,
    //      strictPort: true,
    //      allowedHosts: 'all',
    //  },

    // server: {
    //     host: '10.0.0.7',
    //     port: 5173,
    //     strictPort: true,
    //     allowedHosts: 'all',
    // },
});
