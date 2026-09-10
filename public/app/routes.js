var app = angular.module('mainApp', [
    'ngRoute',
    'angularUtils.directives.dirPagination'
]);

app.config(function ($routeProvider, $locationProvider) {

    $locationProvider.hashPrefix('');

    $routeProvider

        .when('/', {
            templateUrl: 'templates/home.html'
        })

        .when('/products', {
            templateUrl: 'templates/products.html'
        })

        .when('/colors', {
            templateUrl: 'templates/colors.html'
        })

        .otherwise({
            redirectTo: '/'
        });
});