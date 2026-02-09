/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './themes/aesir/**/*.php',
    './themes/aesir/**/*.js',
    './*.php',
    './template-parts/**/*.php',
    './assets/**/*.js'
  ],
  theme: {
    screens: {
      sm: "640px",
      md: "768px",
      lg: "1024px",
      xl: "1280px",
      "2xl": "1536px",
    },
    container: {
      center: true,
    },
    extend: {
      fontFamily: {
        'montserrat': ['Montserrat', 'Helvetica Neue', 'Helvetica', 'Arial', 'sans-serif'],
      },
      keyframes: {
        'slide-in': {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(0)' },
        },
        'slide-out': {
          '0%': { transform: 'translateX(0)' },
          '100%': { transform: 'translateX(-100%)' },
        },
      },
      animation: {
        'slide-in': 'slide-in 0.3s ease-out forwards',
        'slide-out': 'slide-out 0.3s ease-out forwards',
      },
    },
  },
  plugins: [],
}
