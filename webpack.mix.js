let mix = require('laravel-mix');

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

// mix.js('resources/assets/js/app.js', 'public/js')
//    .sass('resources/assets/sass/app.scss', 'public/css');

mix.postCss('resources/css/app.css', 'public/css', [
      require('postcss-import'),
      require('tailwindcss'),
   ])
   .webpackConfig(require('./webpack.config'))
   .js('resources/assets/js/app.js', 'public/js');
mix.version();
