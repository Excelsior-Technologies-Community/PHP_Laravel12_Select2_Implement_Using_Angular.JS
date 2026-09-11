mainApp.controller('ColorController', function ($scope, $http) {

    $scope.colors = [];

    $scope.search = '';

    $scope.form = {
        name: ''
    };

    /*
    |--------------------------------------------------------------------------
    | Load Colors
    |--------------------------------------------------------------------------
    */

    $scope.loadColors = function () {

        $http.get('/colors', {
            params: {
                search: $scope.search
            }
        })
        .then(function (response) {

            $scope.colors = response.data;

        })
        .catch(function (error) {

            console.error(
                'Unable to load colors',
                error
            );

        });
    };

    /*
    |--------------------------------------------------------------------------
    | Search Colors
    |--------------------------------------------------------------------------
    */

    $scope.searchColors = function () {

        $scope.loadColors();
    };

    /*
    |--------------------------------------------------------------------------
    | Add Color
    |--------------------------------------------------------------------------
    */

    $scope.addColor = function () {

        if (!$scope.form.name) {

            alert('Color name is required.');

            return;
        }

        $http.post(
            '/colors',
            $scope.form
        )
        .then(function (response) {

            alert(
                response.data.message
            );

            $scope.form.name = '';

            $scope.loadColors();

        })
        .catch(function (error) {

            console.error(error);

            if (
                error.data &&
                error.data.errors
            ) {

                alert(
                    'Color already exists or is invalid.'
                );

            } else {

                alert(
                    'Unable to create color.'
                );
            }

        });
    };

    /*
    |--------------------------------------------------------------------------
    | Delete Color
    |--------------------------------------------------------------------------
    */

    $scope.deleteColor = function (id) {

        if (!confirm(
            'Are you sure you want to delete this color?'
        )) {
            return;
        }

        $http.delete(
            '/colors/' + id
        )
        .then(function (response) {

            alert(
                response.data.message
            );

            $scope.loadColors();

        })
        .catch(function (error) {

            console.error(error);

            alert(
                'Unable to delete color.'
            );

        });
    };

    $scope.loadColors();
});