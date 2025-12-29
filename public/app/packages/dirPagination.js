(function () {
    'use strict';

    var module = angular.module('angularUtils.directives.dirPagination', []);

    module.directive('dirPaginate', ['$compile', '$parse', 'paginationService',
        function ($compile, $parse, paginationService) {

            return {
                terminal: true,
                priority: 100,
                compile: function (element, attrs) {

                    var expression = attrs.dirPaginate;
                    var match = expression.match(/^\s*(.*)\s+in\s+(.*)\s*$/);

                    var lhs = match[1];
                    var rhs = match[2];

                    return function (scope, element, attrs) {

                        var paginationId = attrs.paginationId || '__default';
                        paginationService.registerInstance(paginationId);

                        scope.$watchCollection(rhs, function (collection) {
                            if (collection) {
                                paginationService.setCollectionLength(paginationId, collection.length);
                            }
                        });

                        var repeatExpression = lhs + ' in ' + rhs + ' | itemsPerPage: paginationService.getItemsPerPage(\'' + paginationId + '\')';
                        element.attr('ng-repeat', repeatExpression);

                        element.removeAttr('dir-paginate');
                        $compile(element)(scope);
                    };
                }
            };
        }
    ]);

    module.directive('dirPaginationControls', ['paginationService',
        function (paginationService) {

            return {
                restrict: 'AE',
                templateUrl: function (elem, attrs) {
                    return attrs.templateUrl || 'templates/dirPagination.html';
                },
                link: function (scope, element, attrs) {

                    var paginationId = attrs.paginationId || '__default';

                    scope.pagination = paginationService.getInstance(paginationId);
                }
            };
        }
    ]);

    module.factory('paginationService', function () {

        var instances = {};

        return {
            registerInstance: function (id) {
                instances[id] = instances[id] || {
                    currentPage: 1,
                    itemsPerPage: 5
                };
            },
            getInstance: function (id) {
                return instances[id];
            },
            setCollectionLength: function (id, length) {
                instances[id].collectionLength = length;
            },
            getItemsPerPage: function (id) {
                return instances[id].itemsPerPage;
            }
        };
    });

})();
