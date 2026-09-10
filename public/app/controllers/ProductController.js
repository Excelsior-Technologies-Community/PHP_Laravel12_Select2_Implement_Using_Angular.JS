app.controller('ProductController', function (
    $scope,
    $http,
    $timeout
) {

    /*
    |--------------------------------------------------------------------------
    | Main Data
    |--------------------------------------------------------------------------
    */

    $scope.data = [];

    $scope.colors = [];

    $scope.form = {};

    $scope.totalItems = 0;

    $scope.currentPage = 1;

    $scope.lastPage = 1;


    /*
    |--------------------------------------------------------------------------
    | Search & Filters
    |--------------------------------------------------------------------------
    */

    $scope.filters = {

        search: '',

        min_price: '',

        max_price: '',

        color_ids: []

    };


    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

    $scope.analytics = {

        total_products: 0,

        total_colors: 0,

        products_without_colors: 0,

        average_price: 0,

        highest_price: 0,

        lowest_price: 0,

        most_used_color: null,

        color_statistics: []

    };


    /*
    |--------------------------------------------------------------------------
    | Load Products
    |--------------------------------------------------------------------------
    */

    function loadProducts(page) {

        page = page || 1;

        var params = {

            page: page

        };


        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        if ($scope.filters.search) {

            params.search =
                $scope.filters.search;

        }


        /*
        |--------------------------------------------------------------------------
        | Minimum Price
        |--------------------------------------------------------------------------
        */

        if (
            $scope.filters.min_price !== ''
        ) {

            params.min_price =
                $scope.filters.min_price;

        }


        /*
        |--------------------------------------------------------------------------
        | Maximum Price
        |--------------------------------------------------------------------------
        */

        if (
            $scope.filters.max_price !== ''
        ) {

            params.max_price =
                $scope.filters.max_price;

        }


        /*
        |--------------------------------------------------------------------------
        | Multiple Color IDs
        |--------------------------------------------------------------------------
        */

        if (
            $scope.filters.color_ids &&
            $scope.filters.color_ids.length > 0
        ) {

            params.color_ids =
                $scope.filters.color_ids.join(',');

        }


        /*
        |--------------------------------------------------------------------------
        | Debug Request Parameters
        |--------------------------------------------------------------------------
        */

        console.log(
            'Product Filter Parameters:',
            params
        );


        /*
        |--------------------------------------------------------------------------
        | Request Products
        |--------------------------------------------------------------------------
        */

        $http.get(
            '/products',
            {
                params: params
            }
        ).then(function (res) {

            $scope.data =
                res.data.data;

            $scope.totalItems =
                res.data.total;

            $scope.currentPage =
                res.data.current_page;

            $scope.lastPage =
                res.data.last_page;


        }, function (error) {

            console.error(
                'Product loading failed:',
                error
            );

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Initialize Filter Select2
    |--------------------------------------------------------------------------
    */

    function initializeFilterColorSelect() {

        var filterColorSelect =
            $('#filterColorSelect');


        /*
        |--------------------------------------------------------------------------
        | Check Element
        |--------------------------------------------------------------------------
        */

        if (!filterColorSelect.length) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Destroy Existing Select2
        |--------------------------------------------------------------------------
        */

        if (
            filterColorSelect.hasClass(
                'select2-hidden-accessible'
            )
        ) {

            filterColorSelect.select2(
                'destroy'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Remove Existing Options
        |--------------------------------------------------------------------------
        */

        filterColorSelect.empty();


        /*
        |--------------------------------------------------------------------------
        | Build Select2 Data
        |--------------------------------------------------------------------------
        */

        var colorData =
            $scope.colors.map(
                function (color) {

                    return {

                        id: String(color.id),

                        text: color.name

                    };

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Initialize Select2
        |--------------------------------------------------------------------------
        */

        filterColorSelect.select2({

            width: '100%',

            placeholder:
                'Filter by one or more colors',

            allowClear: true,

            closeOnSelect: false,

            data: colorData

        });


        /*
        |--------------------------------------------------------------------------
        | Remove Previous Event
        |--------------------------------------------------------------------------
        */

        filterColorSelect.off(
            'change.productFilter'
        );


        /*
        |--------------------------------------------------------------------------
        | Handle Color Selection
        |--------------------------------------------------------------------------
        */

        filterColorSelect.on(
            'change.productFilter',
            function () {

                var selectedColors =
                    filterColorSelect.val() || [];


                /*
                |--------------------------------------------------------------------------
                | Debug Selected Colors
                |--------------------------------------------------------------------------
                */

                console.log(
                    'Selected Color IDs:',
                    selectedColors
                );


                /*
                |--------------------------------------------------------------------------
                | AngularJS Digest
                |--------------------------------------------------------------------------
                */

                $scope.$applyAsync(
                    function () {

                        $scope.filters.color_ids =
                            selectedColors;

                        loadProducts(1);

                    }
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Load Colors
    |--------------------------------------------------------------------------
    */

    function loadColors(callback) {

        $http.get(
            '/colors'
        ).then(function (res) {

            $scope.colors =
                res.data;


            /*
            |--------------------------------------------------------------------------
            | Initialize Filter Select2
            |--------------------------------------------------------------------------
            */

            $timeout(
                function () {

                    initializeFilterColorSelect();

                },
                100
            );


            /*
            |--------------------------------------------------------------------------
            | Callback
            |--------------------------------------------------------------------------
            */

            if (callback) {

                callback();

            }


        }, function (error) {

            console.error(
                'Color loading failed:',
                error
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Initial Loading
    |--------------------------------------------------------------------------
    */

    loadColors();

    loadProducts(1);


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $scope.pageChanged =
        function (newPage) {

            loadProducts(
                newPage
            );

        };

$scope.getPages = function () {

    var pages = [];

    var startPage =
        (($scope.currentPage - 1) * 3) + 1;

    var endPage =
        startPage + 2;

    for (
        var i = startPage;
        i <= endPage;
        i++
    ) {

        if (i <= $scope.lastPage) {
            pages.push(i);
        }

    }

    return pages;
};


    /*
    |--------------------------------------------------------------------------
    | Search Products
    |--------------------------------------------------------------------------
    */

    $scope.searchProducts =
        function () {

            loadProducts(1);

        };


    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    $scope.resetFilters =
        function () {

            $scope.filters = {

                search: '',

                min_price: '',

                max_price: '',

                color_ids: []

            };


            /*
            |--------------------------------------------------------------------------
            | Clear Select2 Without Triggering Filter
            |--------------------------------------------------------------------------
            */

            $('#filterColorSelect')
                .val(null)
                .trigger('change.select2');


            /*
            |--------------------------------------------------------------------------
            | Reload Products
            |--------------------------------------------------------------------------
            */

            loadProducts(1);

        };


    /*
    |--------------------------------------------------------------------------
    | Create Product Modal
    |--------------------------------------------------------------------------
    */

    $('#create-product').on(
        'shown.bs.modal',
        function () {

            loadColors(
                function () {

                    $timeout(
                        function () {

                            var select =
                                $('#colorSelectCreate');


                            /*
                            |--------------------------------------------------------------------------
                            | Destroy Existing Select2
                            |--------------------------------------------------------------------------
                            */

                            if (
                                select.hasClass(
                                    'select2-hidden-accessible'
                                )
                            ) {

                                select.select2(
                                    'destroy'
                                );

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Initialize Create Select2
                            |--------------------------------------------------------------------------
                            */

                            select.select2({

                                dropdownParent:
                                    $('#create-product'),

                                width:
                                    '100%',

                                placeholder:
                                    'Select colors',

                                allowClear:
                                    true,

                                closeOnSelect:
                                    false,

                                data:
                                    $scope.colors.map(
                                        function (color) {

                                            return {

                                                id:
                                                    String(
                                                        color.id
                                                    ),

                                                text:
                                                    color.name

                                            };

                                        }
                                    )

                            });

                        },
                        100
                    );

                }
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Save Product
    |--------------------------------------------------------------------------
    */

    $scope.saveAdd =
        function () {

            $scope.form.color_ids =
                $('#colorSelectCreate').val() || [];


            $http.post(
                '/products',
                $scope.form
            ).then(function () {


                /*
                |--------------------------------------------------------------------------
                | Reload Products
                |--------------------------------------------------------------------------
                */

                loadProducts(1);


                /*
                |--------------------------------------------------------------------------
                | Reload Analytics
                |--------------------------------------------------------------------------
                */

                $scope.loadAnalytics();


                /*
                |--------------------------------------------------------------------------
                | Close Modal
                |--------------------------------------------------------------------------
                */

                $('#create-product')
                    .modal('hide');


                /*
                |--------------------------------------------------------------------------
                | Reset Form
                |--------------------------------------------------------------------------
                */

                $scope.form = {};


                /*
                |--------------------------------------------------------------------------
                | Clear Select2
                |--------------------------------------------------------------------------
                */

                $('#colorSelectCreate')
                    .val(null)
                    .trigger('change');


            }, function (error) {

                console.error(
                    'Product creation failed:',
                    error
                );

                alert(
                    'Please check the product data.'
                );

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Edit Product
    |--------------------------------------------------------------------------
    */

    $scope.edit =
        function (id) {

            $http.get(
                '/products/' +
                id +
                '/edit'
            ).then(function (res) {

                $scope.form =
                    res.data;


                /*
                |--------------------------------------------------------------------------
                | Open Edit Modal
                |--------------------------------------------------------------------------
                */

                $('#edit-product')
                    .modal('show');


                /*
                |--------------------------------------------------------------------------
                | Load Colors
                |--------------------------------------------------------------------------
                */

                loadColors(
                    function () {

                        $timeout(
                            function () {

                                var select =
                                    $('#colorSelectEdit');


                                /*
                                |--------------------------------------------------------------------------
                                | Destroy Existing Select2
                                |--------------------------------------------------------------------------
                                */

                                if (
                                    select.hasClass(
                                        'select2-hidden-accessible'
                                    )
                                ) {

                                    select.select2(
                                        'destroy'
                                    );

                                }


                                /*
                                |--------------------------------------------------------------------------
                                | Initialize Edit Select2
                                |--------------------------------------------------------------------------
                                */

                                select.select2({

                                    dropdownParent:
                                        $('#edit-product'),

                                    width:
                                        '100%',

                                    placeholder:
                                        'Select colors',

                                    allowClear:
                                        true,

                                    closeOnSelect:
                                        false,

                                    data:
                                        $scope.colors.map(
                                            function (color) {

                                                return {

                                                    id:
                                                        String(
                                                            color.id
                                                        ),

                                                    text:
                                                        color.name

                                                };

                                            }
                                        )

                                });


                                /*
                                |--------------------------------------------------------------------------
                                | Existing Selected Colors
                                |--------------------------------------------------------------------------
                                */

                                var selectedColors =
                                    res.data.colors.map(
                                        function (color) {

                                            return String(
                                                color.id
                                            );

                                        }
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | Set Selected Colors
                                |--------------------------------------------------------------------------
                                */

                                select
                                    .val(
                                        selectedColors
                                    )
                                    .trigger(
                                        'change'
                                    );

                            },
                            100
                        );

                    }
                );

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Update Product
    |--------------------------------------------------------------------------
    */

    $scope.saveEdit =
        function () {

            $scope.form.color_ids =
                $('#colorSelectEdit').val() || [];


            $http.put(
                '/products/' +
                $scope.form.id,
                $scope.form
            ).then(function () {


                /*
                |--------------------------------------------------------------------------
                | Reload Products
                |--------------------------------------------------------------------------
                */

                loadProducts(1);


                /*
                |--------------------------------------------------------------------------
                | Reload Analytics
                |--------------------------------------------------------------------------
                */

                $scope.loadAnalytics();


                /*
                |--------------------------------------------------------------------------
                | Close Modal
                |--------------------------------------------------------------------------
                */

                $('#edit-product')
                    .modal('hide');


            }, function (error) {

                console.error(
                    'Product update failed:',
                    error
                );

                alert(
                    'Please check the product data.'
                );

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Delete Product
    |--------------------------------------------------------------------------
    */

    $scope.remove =
        function (item, index) {

            if (
                !confirm(
                    'Are you sure you want to delete this product?'
                )
            ) {

                return;

            }


            $http.delete(
                '/products/' +
                item.id
            ).then(function () {


                /*
                |--------------------------------------------------------------------------
                | Remove From Current Page
                |--------------------------------------------------------------------------
                */

                $scope.data.splice(
                    index,
                    1
                );


                /*
                |--------------------------------------------------------------------------
                | Update Total
                |--------------------------------------------------------------------------
                */

                $scope.totalItems--;


                /*
                |--------------------------------------------------------------------------
                | Reload Analytics
                |--------------------------------------------------------------------------
                */

                $scope.loadAnalytics();


            }, function (error) {

                console.error(
                    'Product deletion failed:',
                    error
                );

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    */

    $scope.loadAnalytics =
        function () {

            $http.get(
                '/products-analytics'
            ).then(function (res) {

                $scope.analytics =
                    res.data;

            }, function (error) {

                console.error(
                    'Analytics loading failed:',
                    error
                );

            });

        };


    /*
    |--------------------------------------------------------------------------
    | Initial Analytics
    |--------------------------------------------------------------------------
    */

    $scope.loadAnalytics();

});