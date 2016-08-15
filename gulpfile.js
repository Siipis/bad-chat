var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.copy('node_modules/jquery/dist/jquery.min.js', 'public/js/jquery.js')
        .copy('node_modules/bootstrap/dist/js/bootstrap.min.js', 'public/js/bootstrap.js')
        .copy('node_modules/angular/angular.min.js', 'public/js/angular.js')
        .copy('node_modules/angular-sanitize/angular-sanitize.min.js', 'public/js/angular-sanitize.js')
        .copy('node_modules/js-cookie/src/js.cookie.js', 'resources/assets/js/chat/cookies.js')
        .copy('node_modules/font-awesome/css/font-awesome.min.css', 'public/css/font-awesome.css')
        .copy('node_modules/bootstrap/dist/fonts', 'public/fonts')
        .copy('node_modules/font-awesome/fonts', 'public/fonts');

    /*
    mix.scripts([
        'resources/assets/js/alert.js',
        'resources/assets/js/modal.js',
        'resources/assets/js/collapse-list.js'
    ], 'public/js/account.js');
    */

    mix.scripts([
        'resources/assets/js/alert.js',
        'resources/assets/js/chat/dependencies',
        'resources/assets/js/chat/app.js',
        'resources/assets/js/chat/services',
        'resources/assets/js/chat/controllers'
    ], 'public/js/chat.js');
});
