app.controller('ColorController', function ($scope, $http) {

    $scope.data = [];

    $scope.form = {};

    /*
    |--------------------------------------------------------------------------
    | Load colors
    |--------------------------------------------------------------------------
    */

    function load() {

        $http.get('/colors')
            .then(function (res) {

                $scope.data = res.data;

            });
    }

    load();

    /*
    |--------------------------------------------------------------------------
    | Add Color
    |--------------------------------------------------------------------------
    */

    $scope.saveAdd = function () {

        $http.post(
            '/colors',
            $scope.form
        ).then(function (res) {

            $scope.data.push(res.data.color);

            $('#create-color').modal('hide');

            $scope.form = {};

            alert(
                'Color created successfully.'
            );

        }, function (error) {

            if (
                error.data &&
                error.data.errors &&
                error.data.errors.name
            ) {
                alert(
                    error.data.errors.name[0]
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

    $scope.remove = function (c, index) {

        if (!confirm(
            'Delete this color? All product relationships will also be removed.'
        )) {
            return;
        }

        $http.delete(
            '/colors/' + c.id
        ).then(function () {

            $scope.data.splice(index, 1);

        });

    };

});