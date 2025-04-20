/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  safelist: [
    {pattern: /^category_/}, 'pl-3', 'pl-12', 'pl-16', 'pl-20', 'pl-24', 'w-48'
  ],
  theme: {
    extend: {
      backgroundImage: {
        'white-granular': "url('/images/background/white.jpg')",
        'grey-granular': "url('/images/background/grey.jpg')",
        'black-granular': "url('/images/background/drk.jpg')",
        'pitch-black': "url('/images/background/pitch.jpg')",
      },
      backgroundSize: {
        '50': '50px'
      },
      colors: {
        'amber-dim': '#e19e2f',
        'orange-yellow': '#F9DDAA',
        'green-dark': '#11333c',
        'dark-charcoal': '#2B2B2B',
        'white-soft': '#fdfaf7',
        'grey-dark': '#2a2a29',

      },
      fontFamily: {
        roboto: ['Roboto', 'sans-serif'],
      },
    },
    fontWeight: {
      upnormal: '450'
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
