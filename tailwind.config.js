/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            screens: {
                '3xl': '1920px',
                '4xl': '2560px',
            },
        },
    },
    plugins: [],
};
