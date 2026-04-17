const { defineConfig } = require('vite')
const laravel = require('laravel-vite-plugin').default
const vue = require('@vitejs/plugin-vue2').default
const path = require('path')

module.exports = defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: { '@': path.resolve(__dirname, 'resources/js') },
    },
    server: {
        port: 5173,
        host: '127.0.0.1',
    },
})