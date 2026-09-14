var mainApp = angular.module(
    'mainApp',
    ['ngRoute']
);


mainApp.config([
    '$routeProvider',
    '$httpProvider',

    function ($routeProvider, $httpProvider) {


        // =====================================================
        // CSRF TOKEN
        // =====================================================

        var csrfElement = document.querySelector(
            'meta[name="csrf-token"]'
        );


        if (csrfElement) {

            var csrfToken =
                csrfElement.getAttribute('content');


            $httpProvider.defaults.headers.common[
                'X-CSRF-TOKEN'
            ] = csrfToken;

        }


        // =====================================================
        // ANGULAR ROUTES
        // =====================================================

        $routeProvider


            // -------------------------------------------------
            // HOME
            // -------------------------------------------------

            .when('/', {

                templateUrl: '/templates/dashboard.html',
                controller: 'DashboardController'

            })


            // -------------------------------------------------
            // PRODUCTS
            // -------------------------------------------------

            .when('/products', {

                templateUrl: '/templates/products.html',

                controller: 'ProductController'

            })


            // -------------------------------------------------
            // COLORS
            // -------------------------------------------------

            .when('/colors', {

                templateUrl: '/templates/colors.html',

                controller: 'ColorController'

            })

            .when('/categories', { templateUrl: '/templates/catalog.html', controller: 'CatalogController' })

            .when('/brands', { templateUrl: '/templates/catalog.html', controller: 'CatalogController' })


            // -------------------------------------------------
            // UNKNOWN URL
            // -------------------------------------------------

            .otherwise({

                redirectTo: '/'

            });

    }

]);