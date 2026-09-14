mainApp.controller('CatalogController', function ($scope, $http, $window) {
    $scope.isBrand = $window.location.hash.indexOf('brands') !== -1;
    $scope.label = $scope.isBrand ? 'Brand' : 'Category';
    $scope.items = [];
    $scope.form = { name: '' };
    $scope.load = function () {
        $http.get($scope.isBrand ? '/brands' : '/categories').then(function (response) { $scope.items = response.data; });
    };
    $scope.add = function () {
        if (!$scope.form.name) { return; }
        $http.post($scope.isBrand ? '/brands' : '/categories', $scope.form).then(function () { $scope.form.name = ''; $scope.load(); });
    };
    $scope.remove = function (id) {
        if (confirm('Delete this ' + $scope.label.toLowerCase() + '?')) {
            $http.delete(($scope.isBrand ? '/brands/' : '/categories/') + id).then($scope.load);
        }
    };
    $scope.load();
});