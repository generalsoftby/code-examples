'use strict';

angular
    .module('app.contacts')
    .controller('ContactsTypesCtrl', ContactsTypesCtrl);

function ContactsTypesCtrl($scope, $window, ContactsTable, ContextFactory, $log, GocMultiSelectTableFactory,
    GocMsgBusFactory, ContactsRestangular, ContactsViewConfigFactory) {

    $log.info('ContactsTypesCtrl');
    ContextFactory.getCurrentContext();

    $scope.refreshedAll = true;
    GocMsgBusFactory.onMsg('refresh_filters', function() {
        $scope.refreshedAll = false;
        setTimeout(function() {
            $scope.refreshedAll = true;
        }, 50);
    });

    this.table = new ContactsTable($scope, GocMultiSelectTableFactory);

    $scope.configList = [];

    $scope.selectView = function(item, didUserClick) {
        if ($scope.selectedView && item && !item.active && $scope.selectedView.id !== item.id) {
            ContactsViewConfigFactory.setActive(item.id, item, true);
        }

        if (didUserClick) {

            $window.localStorage['userChoseView'] = true;

            if (!item.type) {
                $window.localStorage['viewId'] = 'default';
            } else {
                $window.localStorage['viewId'] = item.id;
            }
        }

        $scope.selectedView = item;
        GocMsgBusFactory.emitMsg('dynamic-table-update', $scope.selectedView.settings);
    };

    $scope.initTable = function() {
        ContactsViewConfigFactory.list(function(results){
            $scope.configList = results;
            var keys = _.keys($scope.configList);
            var active = keys[0];
            var view = {};

            if($window.localStorage['viewId']) {
                active = $window.localStorage['viewId'];
            }

            angular.forEach(keys, function(item){
                if (active === item) {
                    view = $scope.configList[item];
                }
            });

            $scope.selectView(view);
        });
    };

    $scope.showStatistics = false;
}
