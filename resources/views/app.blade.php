<!DOCTYPE html>
<html lang="en" ng-app="mainApp">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>
        Laravel + AngularJS + Select2
    </title>


    <!-- Bootstrap -->

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">


    <!-- Select2 -->

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">


    <!-- jQuery -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <!-- Bootstrap JS -->

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <!-- Select2 JS -->

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <!-- AngularJS -->

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js"></script>


    <!-- Pagination -->

    <script src="{{ asset('app/packages/dirPagination.js') }}"></script>


    <!-- Angular Routes -->

    <script src="{{ asset('app/routes.js') }}"></script>


    <!-- Angular Controllers -->

    <script src="{{ asset('app/controllers/ProductController.js') }}"></script>

    <script src="{{ asset('app/controllers/ColorController.js') }}"></script>


    <style>

        body {
            background: #f5f5f5;
        }

        .container {
            margin-bottom: 50px;
        }

        .panel {
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .select2-container {
            width: 100% !important;
        }

        .label {
            display: inline-block;
            margin-bottom: 3px;
        }

        h1 {
            margin-bottom: 25px;
        }

    </style>

</head>


<body>


<nav class="navbar navbar-default">

    <div class="container">

        <div class="navbar-header">

            <a class="navbar-brand">

                Laravel + AngularJS + Select2

            </a>

        </div>


        <ul class="nav navbar-nav">

            <li>
                <a href="#/">
                    Home
                </a>
            </li>

            <li>
                <a href="#/products">
                    Products
                </a>
            </li>

            <li>
                <a href="#/colors">
                    Colors
                </a>
            </li>

        </ul>

    </div>

</nav>


<div class="container">

    <ng-view></ng-view>

</div>


</body>

</html>