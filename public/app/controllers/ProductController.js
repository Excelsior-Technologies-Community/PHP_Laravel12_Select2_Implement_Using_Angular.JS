mainApp.controller('ProductController', function ($scope, $http, $timeout, $window) {

    /*
    |--------------------------------------------------------------------------
    | Variables
    |--------------------------------------------------------------------------
    */

    $scope.products = [];

    $scope.colors = [];

    $scope.categories = [];

    $scope.brands = [];

    $scope.toast = '';

    $scope.visibleColumns = {
        image: true,
        sku: true,
        category: true,
        stock: true,
        status: true,
        price: true,
        colors: true,
        created: true
    };

    $scope.bulk = { status: '', price: '' };

    $scope.trash = [];

    $scope.showTrash = false;

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
        category_id: '',
        brand_id: '',
        status: '',
        date_from: '',
        date_to: '',
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
        description: '', price: '', sku: '', slug: '',
        category_id: '', brand_id: '', stock_quantity: 0,
        status: 'active', discount: 0, image: null, color_ids: []
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

    $scope.loadLookups = function () {
        $http.get('/categories').then(function (response) { $scope.categories = response.data; });
        $http.get('/brands').then(function (response) { $scope.brands = response.data; });
    };

    $scope.notify = function (message) {
        $scope.toast = message;
        $timeout(function () { $scope.toast = ''; }, 3000);
    };

    $scope.imageUrl = function (image) {
        if (!image) { return ''; }
        return image.indexOf('http://') === 0 || image.indexOf('https://') === 0
            ? image
            : '/storage/' + image;
    };

    /*
    |--------------------------------------------------------------------------
    | Select2
    |--------------------------------------------------------------------------
    */

    $scope.initializeSelect2 = function () {

        $('.select2').each(function () {

            var element = $(this);

            if (typeof element.select2 !== 'function') {
                console.error('Select2 is not loaded.');
                return;
            }

            if (element.hasClass('select2-hidden-accessible')) {
                element.select2('destroy');
            }

            var select2Options = {
                width: '100%',
                placeholder: 'Select colors',
                allowClear: true,
                closeOnSelect: false
            };

            if (element.attr('id') === 'productColors') {
                select2Options.dropdownParent = $('#productModal');
            }

            element.select2(select2Options);

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
            category_id: $scope.filters.category_id,
            brand_id: $scope.filters.brand_id,
            status: $scope.filters.status,
            date_from: $scope.filters.date_from,
            date_to: $scope.filters.date_to,
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
            category_id: '', brand_id: '', status: '', date_from: '', date_to: '',
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
            description: '', price: '', sku: '', slug: '', category_id: '', brand_id: '',
            stock_quantity: 0, status: 'active', discount: 0, image: null, color_ids: []
        };

        $('#productModal').modal('show');

        $timeout(function () {

            $scope.initializeSelect2();

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
                    description: product.description,
                    price: product.price,
                    sku: product.sku, slug: product.slug,
                    category_id: product.category_id || '', brand_id: product.brand_id || '',
                    stock_quantity: product.stock_quantity, status: product.status,
                    discount: product.discount, image: null,
                    color_ids: product.colors.map(function (color) {
                        return color.id;
                    })
                };

                $('#productModal').modal('show');

                $timeout(function () {

                    $scope.initializeSelect2();

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

        var data = new FormData();
        data.append('title', $scope.form.title);
        data.append('description', $scope.form.description || '');
        data.append('price', $scope.form.price);
        data.append('sku', $scope.form.sku || '');
        data.append('slug', $scope.form.slug || '');
        data.append('category_id', $scope.form.category_id || '');
        data.append('brand_id', $scope.form.brand_id || '');
        data.append('stock_quantity', $scope.form.stock_quantity || 0);
        data.append('status', $scope.form.status || 'active');
        data.append('discount', $scope.form.discount || 0);
        ($scope.form.color_ids || []).forEach(function (id) { data.append('color_ids[]', id); });
        if ($scope.form.image) { data.append('image', $scope.form.image); }

        if ($scope.editing) {

            data.append('_method', 'PUT');
            $http.post(
                '/products/' + $scope.form.id,
                data,
                { transformRequest: angular.identity, headers: { 'Content-Type': undefined } }
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
                data,
                { transformRequest: angular.identity, headers: { 'Content-Type': undefined } }
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

    $scope.bulkUpdate = function () {
        if (!$scope.selectedProductIds.length) { alert('Select at least one product.'); return; }
        if (!$scope.bulk.status && $scope.bulk.price === '') { alert('Choose status or enter price.'); return; }
        $http.post('/products-bulk-update', {
            ids: $scope.selectedProductIds, status: $scope.bulk.status || null,
            price: $scope.bulk.price === '' ? null : $scope.bulk.price
        }).then(function (response) { $scope.notify(response.data.message); $scope.loadProducts($scope.currentPage); });
    };

    $scope.importProducts = function (element) {
        if (!element.files.length) { return; }
        var data = new FormData(); data.append('file', element.files[0]);
        $http.post('/products-import', data, { transformRequest: angular.identity, headers: { 'Content-Type': undefined } })
            .then(function (response) { $scope.notify(response.data.message); $scope.loadProducts(1); });
        element.value = '';
    };

    $scope.printReport = function () { $window.print(); };

    $scope.toggleColumn = function (column) { $scope.visibleColumns[column] = !$scope.visibleColumns[column]; };

    $scope.showDetails = function (id) {
        $http.get('/products/' + id).then(function (response) {
            $scope.details = response.data; $('#productDetailsModal').modal('show');
        });
    };

    $scope.loadTrash = function () {
        $http.get('/products-trash').then(function (response) {
            $scope.trash = response.data;
            $scope.showTrash = true;
        });
    };

    $scope.restoreProduct = function (id) {
        $http.post('/products/' + id + '/restore').then(function (response) {
            $scope.notify(response.data.message);
            $scope.loadTrash();
            $scope.loadProducts($scope.currentPage);
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
    $scope.loadLookups();

    $scope.loadProducts(1);
});