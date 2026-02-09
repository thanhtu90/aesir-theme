module.exports = {
  syntax: 'postcss-scss',
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
    // Enable minification only for production builds
    ...(process.env.NODE_ENV === 'production'
      ? { cssnano: { preset: 'default' } }
      : {})
  }
};