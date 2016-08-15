/**
 * Angular JS
 *
 * @type {IModule}
 */

var app = angular.module('chat', ['ngSanitize']);

app.config(['$httpProvider', function ($httpProvider) {
    $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}]);