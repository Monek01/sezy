/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                sezy: {
                    DEFAULT: '#0057d4',
                    light: '#4d90e8',
                    dark: '#003f9e',
                    50: '#eef6ff',
                    100: '#d6eaff',
                    500: '#0057d4',
                    600: '#004ab8',
                    700: '#003f9e',
                    900: '#001f5c',
                },
                'sezy-orange': {
                    DEFAULT: '#F5821F',
                    light: '#FFAD5C',
                    dark: '#d96a08',
                    50: '#fff7ed',
                    100: '#fde8cc',
                },
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.5s ease-out',
                'fade-in': 'fadeIn 0.4s ease-out',
                'slide-in-left': 'slideInLeft 0.5s ease-out',
                'bounce-subtle': 'bounceSub 2s infinite',
                'float': 'float 3s ease-in-out infinite',
            },
            keyframes: {
                fadeInUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                slideInLeft: { '0%': { opacity: '0', transform: 'translateX(-20px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                bounceSub: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-4px)' } },
                float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-8px)' } },
            },
            fontFamily: {
                sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui'],
            },
        },
    },
    plugins: [require('@tailwindcss/forms')],
};
