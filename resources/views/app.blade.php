<!DOCTYPE html>
<html lang="en" ng-app="mainApp">
<head>
    <meta charset="UTF-8">
    <title>Laravel + AngularJS + Select2</title>

    <!-- ================= BOOTSTRAP CSS ================= -->
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- ================= SELECT2 CSS ================= -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <!-- ================= jQuery (MUST BE FIRST) ================= -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ================= BOOTSTRAP JS ================= -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <!-- ================= SELECT2 JS (AFTER jQuery) ================= -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- ================= ANGULAR ================= -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js"></script>

    <!-- ================= DIR PAGINATION ================= -->
    <script src="{{ asset('app/packages/dirPagination.js') }}"></script>

    <!-- ================= ANGULAR ROUTES ================= -->
    <script src="{{ asset('app/routes.js') }}"></script>

    <!-- ================= CONTROLLERS ================= -->
    <script src="{{ asset('app/controllers/ProductController.js') }}"></script>
    <script src="{{ asset('app/controllers/ColorController.js') }}"></script>
</head>

<body>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand">Laravel + AngularJS</a>
        </div>
        <ul class="nav navbar-nav">
            <li><a href="#/">Home</a></li>
            <li><a href="#/products">Products</a></li>
            <li><a href="#/colors">Colors</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <ng-view></ng-view>
</div>

</body>
</html>
