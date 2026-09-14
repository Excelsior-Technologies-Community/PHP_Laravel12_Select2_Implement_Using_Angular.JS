mainApp.controller('DashboardController', function ($scope, $http) {
    $scope.metrics = {};
    $scope.loading = true;

    $http.get('/products-analytics').then(function (response) {
        $scope.metrics = response.data;
    }).finally(function () {
        $scope.loading = false;
    });
});