app.controller('ProductController', function ($scope, $http, $timeout) {

    $scope.data = [];
    $scope.colors = [];
    $scope.form = {};
    $scope.totalItems = 0;

    // ================= LOAD PRODUCTS =================
    function loadProducts(page = 1) {
        $http.get('/products?page=' + page).then(function (res) {
            $scope.data = res.data.data;
            $scope.totalItems = res.data.total;
        });
    }
    loadProducts();

    // ================= LOAD COLORS =================
    function loadColors(callback) {
        $http.get('/colors').then(function (res) {
            $scope.colors = res.data;
            if (callback) callback();
        });
    }

    // ================= PAGINATION =================
    $scope.pageChanged = function (newPage) {
        loadProducts(newPage);
    };

    // ================= CREATE MODAL =================
    $('#create-product').on('shown.bs.modal', function () {

        loadColors(function () {

            $timeout(function () {

                if ($('#colorSelectCreate').hasClass('select2-hidden-accessible')) {
                    $('#colorSelectCreate').select2('destroy');
                }

                $('#colorSelectCreate').select2({
                    dropdownParent: $('#create-product'),
                    width: '100%',
                    placeholder: 'Select Colors',
                    data: $scope.colors.map(function (c) {
                        return {
                            id: c.id,
                            text: c.name
                        };
                    })
                });

            }, 0);
        });
    });

    // ================= SAVE CREATE =================
    $scope.saveAdd = function () {
        $scope.form.color_ids = $('#colorSelectCreate').val();

        $http.post('/products', $scope.form).then(function () {
            loadProducts(1); // 🔥 reload index
            $('#create-product').modal('hide');
            $scope.form = {};
            $('#colorSelectCreate').val(null).trigger('change');
        });
    };

    // ================= EDIT =================
    $scope.edit = function (id) {

        $http.get('/products/' + id + '/edit').then(function (res) {

            $scope.form = res.data;
            $('#edit-product').modal('show');

            loadColors(function () {

                $timeout(function () {

                    if ($('#colorSelectEdit').hasClass('select2-hidden-accessible')) {
                        $('#colorSelectEdit').select2('destroy');
                    }

                    $('#colorSelectEdit').select2({
                        dropdownParent: $('#edit-product'),
                        width: '100%',
                        data: $scope.colors.map(function (c) {
                            return {
                                id: c.id,
                                text: c.name
                            };
                        })
                    });

                    $('#colorSelectEdit')
                        .val(res.data.colors.map(c => c.id))
                        .trigger('change');

                }, 0);
            });
        });
    };

    // ================= SAVE EDIT =================
    $scope.saveEdit = function () {
        $scope.form.color_ids = $('#colorSelectEdit').val();

        $http.put('/products/' + $scope.form.id, $scope.form).then(function () {
            loadProducts(1);
            $('#edit-product').modal('hide');
        });
    };

    // ================= DELETE =================
    $scope.remove = function (item, index) {
        if (confirm('Delete?')) {
            $http.delete('/products/' + item.id).then(function () {
                $scope.data.splice(index, 1);
            });
        }
    };

});
