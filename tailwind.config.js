/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  safelist: [
    'pl-6', 'pl-9', 'pl-12', 'pl-15', 'pl-18'
  ],
  theme: {
    extend: {
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
