// Main Angular Module
var app = angular.module('mainApp', [
    'ngRoute',
    'angularUtils.directives.dirPagination'
]);

// Angular Routes Configuration
app.config(function ($routeProvider, $locationProvider) {

    // Remove default ! from URL
    $locationProvider.hashPrefix('');

    $routeProvider
        // Home Route
        .when('/', {
            templateUrl: 'templates/home.html'
        })
        // Products Route
        .when('/products', {
            templateUrl: 'templates/products.html'
        })
        // Colors Route
        .when('/colors', {
            templateUrl: 'templates/colors.html'
        })
        // Default Route
        .otherwise({
            redirectTo: '/'
        });
});
