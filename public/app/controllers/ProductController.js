mainApp.controller('ProductController', function ($scope, $http, $timeout) {

    /*
    |--------------------------------------------------------------------------
    | Variables
    |--------------------------------------------------------------------------
    */

    $scope.products = [];

    $scope.colors = [];

    $scope.selectedProductIds = [];

    $scope.selectAll = false;

    $scope.loading = false;

    $scope.saving = false;

    $scope.editing = false;

    $scope.currentPage = 1;

    $scope.totalPages = 1;

    $scope.totalProducts = 0;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    $scope.filters = {
        search: '',
        min_price: '',
        max_price: '',
        color_ids: [],
        sort: 'id',
        direction: 'asc'
    };

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    $scope.form = {
        id: null,
        title: '',
        price: '',
        color_ids: []
    };

    /*
    |--------------------------------------------------------------------------
    | Load Colors
    |--------------------------------------------------------------------------
    */

    $scope.loadColors = function () {

        $http.get('/colors')
            .then(function (response) {

                $scope.colors = response.data;

                $timeout(function () {
                    $scope.initializeSelect2();
                }, 100);

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
    | Select2
    |--------------------------------------------------------------------------
    */

    $scope.initializeSelect2 = function () {

        $('.select2').each(function () {

            var element = $(this);

            if (element.hasClass('select2-hidden-accessible')) {
                element.select2('destroy');
            }

            element.select2({
                width: '100%',
                placeholder: 'Select colors',
                allowClear: true
            });

            element.off('change.angular');

            element.on(
                'change.angular',
                function () {

                    var value = element.val() || [];

                    $scope.$applyAsync(function () {

                        if (element.attr('id') === 'filterColors') {

                            $scope.filters.color_ids = value.map(Number);

                        }

                        if (element.attr('id') === 'productColors') {

                            $scope.form.color_ids = value.map(Number);

                        }

                    });

                }
            );
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Load Products
    |--------------------------------------------------------------------------
    */

    $scope.loadProducts = function (page) {

        $scope.loading = true;

        $scope.currentPage = page || 1;

        var params = {
            page: $scope.currentPage,
            search: $scope.filters.search,
            min_price: $scope.filters.min_price,
            max_price: $scope.filters.max_price,
            color_ids: $scope.filters.color_ids,
            sort: $scope.filters.sort,
            direction: $scope.filters.direction
        };

        $http.get('/products', {
            params: params
        })
        .then(function (response) {

            $scope.products = response.data.data;

            $scope.totalPages =
                response.data.last_page || 1;

            $scope.currentPage =
                response.data.current_page || 1;

            $scope.totalProducts =
                response.data.total || 0;

            $scope.selectedProductIds = [];

            $scope.selectAll = false;

        })
        .catch(function (error) {

            console.error(
                'Unable to load products',
                error
            );

        })
        .finally(function () {

            $scope.loading = false;

        });
    };

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    $scope.searchProducts = function () {

        $scope.loadProducts(1);
    };

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    $scope.applyFilters = function () {

        $scope.loadProducts(1);
    };

    /*
    |--------------------------------------------------------------------------
    | Clear Filters
    |--------------------------------------------------------------------------
    */

    $scope.clearFilters = function () {

        $scope.filters = {
            search: '',
            min_price: '',
            max_price: '',
            color_ids: [],
            sort: 'id',
            direction: 'asc'
        };

        $('#filterColors')
            .val(null)
            .trigger('change');

        $scope.loadProducts(1);
    };

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    $scope.changeSorting = function () {

        $scope.loadProducts(1);
    };

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $scope.pageNumbers = function () {

        var pages = [];

        for (
            var i = 1;
            i <= $scope.totalPages;
            i++
        ) {
            pages.push(i);
        }

        return pages;
    };

    $scope.goToPage = function (page) {

        if (
            page >= 1 &&
            page <= $scope.totalPages
        ) {
            $scope.loadProducts(page);
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Open Add Modal
    |--------------------------------------------------------------------------
    */

    $scope.openAddModal = function () {

        $scope.editing = false;

        $scope.form = {
            id: null,
            title: '',
            price: '',
            color_ids: []
        };

        $('#productModal').modal('show');

        $timeout(function () {

            $('#productColors')
                .val([])
                .trigger('change');

        }, 200);
    };

    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */

    $scope.editProduct = function (id) {

        $http.get('/products/' + id + '/edit')
            .then(function (response) {

                var product = response.data;

                $scope.editing = true;

                $scope.form = {
                    id: product.id,
                    title: product.title,
                    price: product.price,
                    color_ids: product.colors.map(function (color) {
                        return color.id;
                    })
                };

                $('#productModal').modal('show');

                $timeout(function () {

                    $('#productColors')
                        .val($scope.form.color_ids)
                        .trigger('change');

                }, 200);

            })
            .catch(function (error) {

                console.error(
                    'Unable to load product',
                    error
                );

            });
    };

    /*
    |--------------------------------------------------------------------------
    | Save Product
    |--------------------------------------------------------------------------
    */

    $scope.saveProduct = function () {

        if (!$scope.form.title) {

            alert('Product title is required.');

            return;
        }

        if (
            $scope.form.price === '' ||
            $scope.form.price === null
        ) {

            alert('Product price is required.');

            return;
        }

        $scope.saving = true;

        var data = {
            title: $scope.form.title,
            price: $scope.form.price,
            color_ids: $scope.form.color_ids || []
        };

        if ($scope.editing) {

            $http.put(
                '/products/' + $scope.form.id,
                data
            )
            .then(function () {

                alert(
                    'Product updated successfully.'
                );

                $('#productModal').modal('hide');

                $scope.loadProducts(
                    $scope.currentPage
                );

            })
            .catch(function (error) {

                console.error(error);

                alert(
                    'Unable to update product.'
                );

            })
            .finally(function () {

                $scope.saving = false;

            });

        } else {

            $http.post(
                '/products',
                data
            )
            .then(function () {

                alert(
                    'Product created successfully.'
                );

                $('#productModal').modal('hide');

                $scope.loadProducts(1);

            })
            .catch(function (error) {

                console.error(error);

                alert(
                    'Unable to create product.'
                );

            })
            .finally(function () {

                $scope.saving = false;

            });
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

    $scope.deleteProduct = function (id) {

        if (!confirm(
            'Are you sure you want to delete this product?'
        )) {
            return;
        }

        $http.delete(
            '/products/' + id
        )
        .then(function () {

            alert(
                'Product deleted successfully.'
            );

            $scope.loadProducts(
                $scope.currentPage
            );

        })
        .catch(function (error) {

            console.error(error);

            alert(
                'Unable to delete product.'
            );

        });
    };

    /*
    |--------------------------------------------------------------------------
    | Duplicate Product
    |--------------------------------------------------------------------------
    */

    $scope.duplicateProduct = function (id) {

        if (!confirm(
            'Duplicate this product?'
        )) {
            return;
        }

        $http.post(
            '/products/' + id + '/duplicate'
        )
        .then(function (response) {

            alert(
                response.data.message
            );

            $scope.loadProducts(
                $scope.currentPage
            );

        })
        .catch(function (error) {

            console.error(error);

            alert(
                'Unable to duplicate product.'
            );

        });
    };

    /*
    |--------------------------------------------------------------------------
    | Select Product
    |--------------------------------------------------------------------------
    */

    $scope.toggleProductSelection = function (id) {

        var index =
            $scope.selectedProductIds.indexOf(id);

        if (index === -1) {

            $scope.selectedProductIds.push(id);

        } else {

            $scope.selectedProductIds.splice(
                index,
                1
            );
        }

        $scope.updateSelectAllState();
    };

    /*
    |--------------------------------------------------------------------------
    | Select All
    |--------------------------------------------------------------------------
    */

    $scope.toggleSelectAll = function () {

        if ($scope.selectAll) {

            $scope.selectedProductIds =
                $scope.products.map(function (product) {
                    return product.id;
                });

        } else {

            $scope.selectedProductIds = [];
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Update Select All
    |--------------------------------------------------------------------------
    */

    $scope.updateSelectAllState = function () {

        if (!$scope.products.length) {

            $scope.selectAll = false;

            return;
        }

        $scope.selectAll =
            $scope.selectedProductIds.length ===
            $scope.products.length;
    };

    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    $scope.bulkDelete = function () {

        if (
            !$scope.selectedProductIds.length
        ) {

            alert(
                'Please select at least one product.'
            );

            return;
        }

        if (!confirm(
            'Delete ' +
            $scope.selectedProductIds.length +
            ' selected product(s)?'
        )) {
            return;
        }

        $http.post(
            '/products-bulk-delete',
            {
                ids: $scope.selectedProductIds
            }
        )
        .then(function (response) {

            alert(
                response.data.message
            );

            $scope.loadProducts(
                $scope.currentPage
            );

        })
        .catch(function (error) {

            console.error(error);

            alert(
                'Unable to delete selected products.'
            );

        });
    };

    /*
    |--------------------------------------------------------------------------
    | CSV Export
    |--------------------------------------------------------------------------
    */

    $scope.exportProducts = function () {

        var params = $.param({
            search: $scope.filters.search,
            min_price: $scope.filters.min_price,
            max_price: $scope.filters.max_price,
            color_ids: $scope.filters.color_ids,
            sort: $scope.filters.sort,
            direction: $scope.filters.direction
        });

        window.location.href =
            '/products-export?' + params;
    };

    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */

    $scope.loadColors();

    $scope.loadProducts(1);
});