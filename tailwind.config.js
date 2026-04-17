/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./resources/**/*.blade.php','./resources/**/*.js','./resources/**/*.vue'],
    theme: {
        extend: {
            colors: {
                win:          '#1D9E75',
                'win-dark':   '#166e52',
                loss:         '#E24B4A',
                'loss-dark':  '#a83433',
                neutral:      '#6B7E8F',
                surface:      '#0F1923',
                card:         '#1A2633',
                'card-hover': '#1f2e3f',
                border:       '#243447',
            },
            fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
        },
    },
    plugins: [],
}