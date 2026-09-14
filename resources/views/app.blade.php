<!DOCTYPE html>
<html lang="en" ng-app="mainApp">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>Laravel + AngularJS + Select2</title>


    <!-- ===================================================== -->
    <!-- BOOTSTRAP CSS -->
    <!-- ===================================================== -->

    <link
        rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">


    <!-- ===================================================== -->
    <!-- SELECT2 CSS -->
    <!-- ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">


    <!-- ===================================================== -->
    <!-- JQUERY -->
    <!-- ===================================================== -->

    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js">
    </script>


    <!-- ===================================================== -->
    <!-- BOOTSTRAP JS -->
    <!-- ===================================================== -->

    <script
        src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js">
    </script>


    <!-- ===================================================== -->
    <!-- SELECT2 JS -->
    <!-- ===================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js">
    </script>


    <!-- ===================================================== -->
    <!-- ANGULARJS -->
    <!-- ===================================================== -->

    <script
        src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js">
    </script>


    <!-- ===================================================== -->
    <!-- ANGULAR ROUTE -->
    <!-- ===================================================== -->

    <script
        src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js">
    </script>


    <!-- ===================================================== -->
    <!-- PAGINATION -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('app/packages/dirPagination.js') }}">
    </script>


    <!-- ===================================================== -->
    <!-- ANGULAR ROUTES -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('app/routes.js') }}">
    </script>


    <!-- ===================================================== -->
    <!-- PRODUCT CONTROLLER -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('app/controllers/ProductController.js') }}">
    </script>


    <!-- ===================================================== -->
    <!-- COLOR CONTROLLER -->
    <!-- ===================================================== -->

    <script
        src="{{ asset('app/controllers/ColorController.js') }}">
    </script>

    <script src="{{ asset('app/controllers/DashboardController.js') }}"></script>
    <script src="{{ asset('app/controllers/CatalogController.js') }}"></script>


    <!-- ===================================================== -->
    <!-- CUSTOM CSS -->
    <!-- ===================================================== -->

    <style>

        body {
            background: #f5f7fa;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
        }

        .container {
            margin-bottom: 50px;
        }

        .navbar {
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .navbar-brand {
            font-weight: 600;
            cursor: pointer;
        }

        .navbar-nav > li > a {
            cursor: pointer;
        }

        .panel {
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: visible;
        }

        .panel-heading {
            padding: 18px 20px;
        }

        .panel-body {
            padding: 20px;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-selection {
            min-height: 34px !important;
        }

        .filter-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }

        .filter-box h4 {
            margin-top: 0;
            margin-bottom: 20px;
        }

        .filter-buttons {
            text-align: left;
        }

        .filter-buttons button {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .bulk-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
        }

        .color-label {
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .table > thead > tr > th {
            vertical-align: middle;
        }

        .table > tbody > tr > td {
            vertical-align: middle;
        }

        .pagination {
            margin: 10px 0;
        }

        .pagination > li > a {
            cursor: pointer;
        }

        .jumbotron {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            min-height: 170px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .feature-card h3 {
            margin-top: 0;
        }

        .modal {
            z-index: 1050;
        }

        .modal-backdrop {
            z-index: 1040;
        }

        .btn {
            border-radius: 5px;
        }

        .table-responsive {
            border-radius: 6px;
        }

        .filter-row, .form-row { margin-top: 15px; }

        .column-tools { padding: 12px 0; color: #555; }

        .bulk-control { display: inline-block; width: 140px; margin-left: 8px; height: 30px; }

        .product-thumb { width: 42px; height: 42px; object-fit: cover; border-radius: 4px; }

        .details-image { max-width: 100%; max-height: 220px; display: block; margin: 0 auto 15px; }

        .actions { min-width: 190px; }

        .spinning { animation: spin 1s linear infinite; }

        .dashboard-header { margin: 20px 0; }

        .metric-card, .dashboard-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }

        .metric-card small { color: #6b7280; display: block; letter-spacing: 1px; }

        .metric-card strong, .dashboard-number { display: block; font-size: 28px; margin-top: 8px; color: #1f2937; }

        .metric-card.warning strong { color: #c2410c; }

        .dashboard-card h4 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        .stat-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f1f1; }

        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        @media (max-width: 767px) {
            .navbar-nav { margin: 0; }
            .navbar-nav > li { display: inline-block; }
            .panel-heading .pull-right { float: none !important; margin-top: 15px; }
            .bulk-control { margin: 8px 0 0; width: 100%; }
            .filter-buttons { margin-top: 10px; }
            .product-table { min-width: 1000px; }
        }

    </style>

</head>


<body>


<!-- ===================================================== -->
<!-- NAVBAR -->
<!-- ===================================================== -->

<nav class="navbar navbar-default">

    <div class="container">


        <!-- BRAND -->

        <div class="navbar-header">

            <a class="navbar-brand" href="#!/">

                Laravel + AngularJS + Select2

            </a>

        </div>


        <!-- MENU -->

        <ul class="nav navbar-nav">


            <!-- HOME -->

            <li>

                <a href="#!/">

                    🏠 Home

                </a>

            </li>


            <!-- PRODUCTS -->

            <li>

                <a href="#!/products">

                    📦 Products

                </a>

            </li>


            <!-- COLORS -->

            <li>

                <a href="#!/colors">

                    🎨 Colors

                </a>

            </li>

            <li><a href="#!/categories">Categories</a></li>
            <li><a href="#!/brands">Brands</a></li>


        </ul>

    </div>

</nav>


<!-- ===================================================== -->
<!-- ANGULAR VIEW -->
<!-- ===================================================== -->

<div class="container">

    <ng-view></ng-view>

</div>


<!-- ===================================================== -->
<!-- ANGULAR NAVIGATION -->
<!-- ===================================================== -->

<script>

angular.module('mainApp')

    .run([
        '$rootScope',
        '$location',

        function ($rootScope, $location) {


            /*
            |--------------------------------------------------------------------------
            | Navigate
            |--------------------------------------------------------------------------
            */

            $rootScope.navigate = function (path) {

                console.log(
                    'Angular navigation:',
                    path
                );

                $location.path(path);

            };


            /*
            |--------------------------------------------------------------------------
            | Home
            |--------------------------------------------------------------------------
            */

            $rootScope.goHome = function () {

                $location.path('/');

            };

        }

    ]);

</script>


</body>

</html>