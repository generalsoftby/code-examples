'use strict';

angular
    .module('app.contacts')
    .directive('dropDownCreater', dropDownCreater);

function dropDownCreater() {
    return {
        restrict: 'E',
        templateUrl: '/templates/contacts/dropDownCreater/contacts.dropDownCreater.html',
        scope: {
            model: '=',
            disable: '=',
            defaultOptions: '=',
            height: '@'
        },
        controller: function ($scope, $element, ContactsInitialFieldsFactory, $filter, $timeout, Helpers) {
            $scope.defaultOptions = $scope.defaultOptions ? $scope.defaultOptions : [];

            $scope.newOptionIsEdit = false;

            var push = function (option) {
                var maxPosition = Helpers.maxPropertyValue($scope.model.options, 'pos');

                option.pos = ++ maxPosition;
                $scope.model.options.push(option);
            };

            var swap = function (item, offset) {
                $scope.model.options = $filter('orderBy')($scope.model.options, 'pos');

                var index = _.findIndex($scope.model.options, function (val) {
                    return val.pos === item.pos;
                });

                if (index + offset < 0 || index + offset > $scope.model.options.length) {
                    return false;
                }

                var buffer = $scope.model.options[index + offset];

                $scope.model.options[index + offset] = item;
                $scope.model.options[index + offset].pos += offset;
                $scope.model.options[index] = buffer;
                $scope.model.options[index].pos -= offset;
            };

            $scope.rebuildScrollBar = function () {
                $scope.$broadcast('scrollbarRebuild:ddCreator');
            };

            if (!$scope.model) {
                $scope.model = angular.copy(ContactsInitialFieldsFactory.init('select'));
                $scope.$emit('dropDownModel', $scope.model);
            }

            $scope.checkEmptyOption = function (option) {
                if (!option.name.length) {
                    $scope.deleteItem(option);
                }
            };

            $scope.moveDown = function (item) {
                swap(item, 1);
            };

            $scope.moveUp = function (item) {
                swap(item, -1);
            };

            $scope.getLastOption = function () {
                return _.last($scope.model.options);
            };

            $scope.deleteItem = function (item) {
                var currentPos = item.pos;

                if ($scope.model.options.length <= 2) {
                    return;
                }

                _.remove($scope.model.options, function (val) {
                    return val.name === item.name && item.pos === val.pos;
                });

                angular.forEach($scope.model.options, function (value) {
                    if (currentPos < value.pos) {
                        value.pos --;
                    }
                });

                $scope.$broadcast('scrollbarRebuild:ddCreator');
            };

            $scope.setOption = function (newOptionName) {
                var editableOption = _.last($scope.model.options);

                if (!angular.isDefined(newOptionName) && !$scope.newOptionIsEdit) {
                    return '';
                }

                if (!angular.isDefined(newOptionName)) {
                    return editableOption.name;
                }

                if (!$scope.newOptionIsEdit) {
                    editableOption = {
                        name: newOptionName
                    };
                    push(editableOption);
                    $scope.newOptionIsEdit = true;
                    $scope.rebuildScrollBar();
                }

                if (newOptionName === '' && $scope.newOptionIsEdit) {
                    $scope.model.options.splice(-1, 1);
                    $scope.rebuildScrollBar();
                    $scope.newOptionIsEdit = false;
                }

                return editableOption.name = newOptionName;
            };

            $scope.resetInput = function () {
                if ($scope.newOptionIsEdit) {
                    $scope.newOptionIsEdit = false;
                }
            };

            $scope.goToLastEmptyInput = function (keyCode) {
                if (keyCode !== 9) {    //  checks if Tab is pressed
                    return
                }

                var needToFill = _.find($scope.model.options, function (value) {
                    return !value.name;
                });

                if (!needToFill) {
                    var newOptionInput = $element.find('#add-new-field');

                    $scope.$broadcast('scrollbarRedraw:toBottom');

                    $timeout(function () {
                        newOptionInput[0].focus();
                    }, 550);

                    return;
                }

                var needToFillInput = $element.find('#option' + (needToFill.pos - 1));

                $timeout(function () {
                    needToFillInput.attr('tabindex', 1);
                    needToFillInput[0].focus();
                    needToFillInput.attr('tabindex', -1);
                }, 100);
            };

            $scope.isSaved = function (item) {
                return !!_.find($scope.defaultOptions, function (value) {
                    return value.name === item.name || value === item;
                }) && !_.find($scope.model.options, function (value) {
                    return (value.name === item.name || value === item) && value !== item;
                });
            };

            $scope.getPlaceholder = function (index) {
                switch (index) {
                    case 0 :
                        return 'Name der ersten Option';
                    case 1 :
                        return 'Name der zweiten Option';
                    default:
                        return 'Name der nächsten Option (optional)';
                }
            };

            $scope.focusAddOptionInput = function () {
                var newOptionInput = $element.find('#add-new-field');
                $scope.$broadcast('scrollbarRedraw:toBottom');

                $timeout(function () {
                    newOptionInput[0].focus();
                },100);
            };
        }
    };
}
